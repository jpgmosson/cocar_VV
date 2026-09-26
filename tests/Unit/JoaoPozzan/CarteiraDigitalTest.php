<?php

/*
|--------------------------------------------------------------------------
| Casos de teste — Carteira Digital (área 5 do Plano de Teste do CoCar)
|--------------------------------------------------------------------------
| Responsável: João Pozzan
|
| Testes de integração caixa-branca, automatizados com Pest, rodando no banco de
| testes PostgreSQL/PostGIS (DB_DATABASE=test, definido no phpunit.xml).
|
| Cada caso imprime no terminal o passo a passo, a tabela "Resultado esperado ×
| Resultado obtido" e o status OK/NOk. No fim sai um resumo com todos os casos.
| As capturas de tela dessa saída servem de evidência. A mesma saída, sem cores,
| também é gravada em storage/logs/evidencias-carteira-digital.log.
|
| Como rodar (com o banco do Docker no ar, por exemplo com o `composer dev` aberto):
|
|     php artisan test tests/Unit/JoaoPozzan/CarteiraDigitalTest.php
|
| O CT05 e a entrada '0.00' do CT02 falham com o código atual. São defeitos reais
| do sistema (estorno em duplicidade e depósito de R$ 0,00 aceito), não do teste.
*/

use App\Enums\StatusCarona;
use App\Enums\StatusPedidoCarona;
use App\Enums\StatusTrajeto;
use App\Enums\StatusTransacao;
use App\Enums\TipoTransacao;
use App\Enums\TipoUsuario;
use App\Models\Carona;
use App\Models\Carteira;
use App\Models\PedidoCarona;
use App\Models\Trajeto;
use App\Models\Transacao;
use App\Models\User;
use App\Services\PagamentoService;
use App\Services\TrajetoService;
use App\ValueObjects\Point;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Testing\TestResponse;
use Symfony\Component\Console\Terminal;
use Tests\TestCase;

uses(TestCase::class);

beforeAll(function () {
    // Com a configuração em cache, o Laravel ignora o DB_DATABASE=test do phpunit.xml
    // e o RefreshDatabase apagaria o banco de desenvolvimento. Melhor parar aqui.
    if (is_file(dirname(__DIR__, 3).'/bootstrap/cache/config.php')) {
        throw new RuntimeException(
            'Existe configuração em cache (bootstrap/cache/config.php). Rode "php artisan config:clear" antes dos testes.'
        );
    }

    EvidenciaCarteiraDigital::iniciarLog();
});

afterAll(fn () => EvidenciaCarteiraDigital::imprimirResumo());

/*
|--------------------------------------------------------------------------
| CT01 — Depósito válido atualiza o saldo e gera transação
|--------------------------------------------------------------------------
*/
test('CT01 · depósito válido atualiza o saldo e gera transação', function () {
    $ev = EvidenciaCarteiraDigital::caso('CT01');
    $this->withoutVite();

    $usuario = CenarioCarteiraDigital::usuario('50.00');
    $ev->passo("Usuário PADRAO #{$usuario->id} criado com carteira de saldo 50.00");

    $deposito = $this->actingAs($usuario)
        ->from(route('usuario.carteira'))
        ->patch(route('carteira.depositar'), ['valor' => '25.50']);
    $erros = CenarioCarteiraDigital::errosDaSessao();
    $ev->passo('PATCH /carteira com valor = 25.50 → HTTP '.CenarioCarteiraDigital::resposta($deposito));

    $pagina = $this->actingAs($usuario)->get(route('usuario.carteira'));
    $saldoNaPagina = CenarioCarteiraDigital::nomeDaView($pagina) === 'usuario.carteira'
        ? CenarioCarteiraDigital::dinheiro($pagina->viewData('carteira')?->saldo)
        : '—';
    $ev->passo('GET /carteira → HTTP '.$pagina->status().' | view '.CenarioCarteiraDigital::nomeDaView($pagina)." | saldo exibido {$saldoNaPagina}");

    $transacoes = Transacao::where('user_id', $usuario->id)->get();
    $gravada = $transacoes->first();

    $esperado = [
        'Resposta do PATCH' => '302 → /carteira',
        'Erros de validação' => 'nenhum',
        'Saldo da carteira' => '75.50',
        'Transações do usuário' => '1',
        'Transação gravada (tipo | status | valor)' => 'deposito | sucesso | 25.50',
        'Vínculo com pedido/trajeto' => 'nenhum',
        'GET /carteira (HTTP | view | saldo)' => '200 | usuario.carteira | 75.50',
    ];

    $obtido = [
        'Resposta do PATCH' => CenarioCarteiraDigital::resposta($deposito),
        'Erros de validação' => $erros ? implode('; ', array_merge(...array_values($erros))) : 'nenhum',
        'Saldo da carteira' => CenarioCarteiraDigital::saldo($usuario),
        'Transações do usuário' => (string) $transacoes->count(),
        'Transação gravada (tipo | status | valor)' => $gravada ? CenarioCarteiraDigital::descrever($gravada) : '—',
        'Vínculo com pedido/trajeto' => $gravada && ($gravada->pedido_carona_id || $gravada->trajeto_id) ? 'preenchido' : 'nenhum',
        'GET /carteira (HTTP | view | saldo)' => $pagina->status().' | '.CenarioCarteiraDigital::nomeDaView($pagina).' | '.$saldoNaPagina,
    ];

    $ev->concluir(
        $esperado,
        $obtido,
        "Saldo passou de 50.00 para {$obtido['Saldo da carteira']}; {$obtido['Transações do usuário']} transação gravada "
        ."({$obtido['Transação gravada (tipo | status | valor)']}); a página da carteira exibe {$saldoNaPagina}."
    );

    expect($obtido)->toBe($esperado);
});

/*
|--------------------------------------------------------------------------
| CT02 — Depósito com valor inválido é rejeitado
|--------------------------------------------------------------------------
| Cada entrada deixa falsa uma única regra de "required|min:0|decimal:0,2".
| O caso com todas as regras verdadeiras é o CT01.
*/
test('CT02 · depósito com valor inválido', function (?string $valor, string $regra, string $defeito) {
    $entrada = $valor === null ? 'sem o campo valor' : "valor = '{$valor}'";
    $envio = $valor === null ? 'sem o campo valor' : "com valor = '{$valor}'";
    $ev = EvidenciaCarteiraDigital::caso('CT02', $entrada);

    $usuario = CenarioCarteiraDigital::usuario('50.00');
    $ev->passo("Usuário PADRAO #{$usuario->id} criado com carteira de saldo 50.00");

    $resposta = $this->actingAs($usuario)
        ->from(route('usuario.carteira'))
        ->patch(route('carteira.depositar'), $valor === null ? [] : ['valor' => $valor]);
    $erro = CenarioCarteiraDigital::errosDaSessao()['valor'][0] ?? 'nenhum';
    $ev->passo("PATCH /carteira {$envio} (regra exercitada: {$regra}) → HTTP ".CenarioCarteiraDigital::resposta($resposta));

    $transacoes = Transacao::where('user_id', $usuario->id)->get();
    $ev->passo('Consulta da carteira e das transações do usuário após a tentativa');

    $esperado = [
        'Resposta do PATCH' => '302 → /carteira',
        'Erro no campo valor' => 'Valor inválido, verifique e tente novamente',
        'Saldo da carteira' => '50.00',
        'Transações criadas' => '0',
    ];

    $obtido = [
        'Resposta do PATCH' => CenarioCarteiraDigital::resposta($resposta),
        'Erro no campo valor' => $erro,
        'Saldo da carteira' => CenarioCarteiraDigital::saldo($usuario),
        'Transações criadas' => (string) $transacoes->count(),
    ];

    $resumo = $transacoes->isEmpty()
        ? "rejeitada (saldo {$obtido['Saldo da carteira']})"
        : "ACEITA: gravou {$transacoes->count()} depósito de "
        .$transacoes->map(fn ($t) => CenarioCarteiraDigital::dinheiro($t->valor))->implode(', ')
        ." (saldo {$obtido['Saldo da carteira']})";

    $ev->concluir($esperado, $obtido, $resumo, $defeito);

    expect($obtido)->toBe($esperado);
})->with([
    'sem o campo valor' => [null, 'required', 'A validação aceitou o depósito sem o campo valor.'],
    "valor '-10.00'" => ['-10.00', 'min:0', 'A validação aceitou um valor negativo.'],
    "valor '10.555'" => ['10.555', 'decimal:0,2', 'A validação aceitou um valor com três casas decimais.'],
    "valor 'abc'" => ['abc', 'decimal:0,2', 'A validação aceitou um valor não numérico.'],
    "valor '0.00'" => ['0.00', 'min:0 (valor-limite)',
        'A regra min:0 aceita zero (o formulário usa min="0.01"), então o PagamentoService grava um depósito de R$ 0,00 no extrato.'],
]);

/*
|--------------------------------------------------------------------------
| CT03 — Retenção aprovada com saldo exatamente igual ao valor
|--------------------------------------------------------------------------
| Ramo falso de bccomp(saldo, valor, 2) === -1, no valor-limite (saldo == valor).
*/
test('CT03 · retenção com saldo igual ao valor (limite)', function () {
    $ev = EvidenciaCarteiraDigital::caso('CT03');

    $passageiro = CenarioCarteiraDigital::usuario('30.00');
    $pedido = CenarioCarteiraDigital::pedido($passageiro);
    $ev->passo("Passageiro #{$passageiro->id} criado com saldo 30.00 e pedido de carona #{$pedido->id}");

    $excecao = null;
    try {
        app(PagamentoService::class)->reterValor($passageiro->id, '30.00', $pedido->id);
    } catch (Throwable $e) {
        $excecao = $e;
    }
    $ev->passo('reterValor(passageiro, "30.00", pedido) executado → '.($excecao ? 'lançou '.$excecao::class : 'sem exceção'));

    $retencoes = Transacao::where('pedido_carona_id', $pedido->id)->where('tipo', TipoTransacao::RETENCAO)->get();

    $esperado = [
        'Exceção' => 'nenhuma',
        'Saldo da carteira' => '0.00',
        'Retenções com status sucesso' => '1 (30.00)',
        'Retenções com status falhou' => '0',
    ];

    $obtido = [
        'Exceção' => $excecao ? $excecao::class.': '.$excecao->getMessage() : 'nenhuma',
        'Saldo da carteira' => CenarioCarteiraDigital::saldo($passageiro),
        'Retenções com status sucesso' => CenarioCarteiraDigital::listar($retencoes->where('status', StatusTransacao::SUCESSO)),
        'Retenções com status falhou' => CenarioCarteiraDigital::listar($retencoes->where('status', StatusTransacao::FALHOU)),
    ];

    $ev->concluir(
        $esperado,
        $obtido,
        CenarioCarteiraDigital::excecao($excecao)."; saldo {$obtido['Saldo da carteira']}; retenção sucesso "
        ."{$obtido['Retenções com status sucesso']}, falhou {$obtido['Retenções com status falhou']}."
    );

    expect($obtido)->toBe($esperado);
});

/*
|--------------------------------------------------------------------------
| CT04 — Retenção recusada por saldo insuficiente
|--------------------------------------------------------------------------
| Ramo verdadeiro de bccomp(saldo, valor, 2) === -1 + tratamento de exceção.
*/
test('CT04 · retenção recusada por saldo insuficiente', function () {
    $ev = EvidenciaCarteiraDigital::caso('CT04');

    $passageiro = CenarioCarteiraDigital::usuario('29.99');
    $pedido = CenarioCarteiraDigital::pedido($passageiro);
    $ev->passo("Passageiro #{$passageiro->id} criado com saldo 29.99 e pedido de carona #{$pedido->id}");

    $excecao = null;
    try {
        app(PagamentoService::class)->reterValor($passageiro->id, '30.00', $pedido->id);
    } catch (Throwable $e) {
        $excecao = $e;
    }
    $ev->passo('reterValor(passageiro, "30.00", pedido) executado → '.($excecao ? 'lançou '.$excecao::class : 'sem exceção'));

    $retencoes = Transacao::where('pedido_carona_id', $pedido->id)->where('tipo', TipoTransacao::RETENCAO)->get();

    $esperado = [
        'Exceção' => 'Exception: Saldo insuficiente para garantir a vaga da carona.',
        'Saldo da carteira' => '29.99',
        'Retenções com status falhou' => '1 (30.00)',
        'Retenções com status sucesso' => '0',
    ];

    $obtido = [
        'Exceção' => $excecao ? $excecao::class.': '.$excecao->getMessage() : 'nenhuma',
        'Saldo da carteira' => CenarioCarteiraDigital::saldo($passageiro),
        'Retenções com status falhou' => CenarioCarteiraDigital::listar($retencoes->where('status', StatusTransacao::FALHOU)),
        'Retenções com status sucesso' => CenarioCarteiraDigital::listar($retencoes->where('status', StatusTransacao::SUCESSO)),
    ];

    $ev->concluir(
        $esperado,
        $obtido,
        CenarioCarteiraDigital::excecao($excecao)."; saldo {$obtido['Saldo da carteira']}; retenção falhou "
        ."{$obtido['Retenções com status falhou']}, sucesso {$obtido['Retenções com status sucesso']}."
    );

    $ev->nota('Achado (não altera o status): no fluxo real (PedidoCaronaService::novoPedido) o reterValor roda dentro de '
        .'um DB::transaction, e o rollback apaga esse registro "falhou". Além disso, por ser Exception e não '
        .'ExibivelException, o usuário receberia erro 500 em vez da mensagem.');

    expect($obtido)->toBe($esperado);
});

/*
|--------------------------------------------------------------------------
| CT05 — Cancelar o mesmo pedido duas vezes não pode estornar duas vezes
|--------------------------------------------------------------------------
| Fluxo de dados (def-uso): a retenção é definida uma vez e só pode ser
| consumida por um único estorno.
*/
test('CT05 · cancelar o pedido duas vezes não estorna duas vezes', function () {
    $ev = EvidenciaCarteiraDigital::caso('CT05');

    $passageiro = CenarioCarteiraDigital::usuario('100.00');
    $pedido = CenarioCarteiraDigital::pedido($passageiro);
    $ev->passo("Passageiro PADRAO #{$passageiro->id} criado com saldo 100.00 e pedido #{$pedido->id} em procurando_motorista");

    app(PagamentoService::class)->reterValor($passageiro->id, '30.00', $pedido->id);
    $ev->passo('reterValor(passageiro, "30.00", pedido) → saldo '.CenarioCarteiraDigital::saldo($passageiro));

    $estornos = fn () => Transacao::where('pedido_carona_id', $pedido->id)->where('tipo', TipoTransacao::ESTORNO)->get();

    $primeiro = $this->actingAs($passageiro)->delete(route('pedido-carona.destroy', $pedido));
    $statusDoPedido = PedidoCarona::find($pedido->id)?->status?->value ?? '—';
    $saldoAposPrimeiro = CenarioCarteiraDigital::saldo($passageiro);
    $estornosAposPrimeiro = CenarioCarteiraDigital::listar($estornos());
    $ev->passo("1º DELETE /pedido-carona/{$pedido->id} → HTTP ".CenarioCarteiraDigital::resposta($primeiro)
        ." | pedido {$statusDoPedido} | saldo {$saldoAposPrimeiro} | estornos {$estornosAposPrimeiro}");

    $segundo = $this->actingAs($passageiro)->delete(route('pedido-carona.destroy', $pedido));
    $saldoAposSegundo = CenarioCarteiraDigital::saldo($passageiro);
    $estornosAposSegundo = CenarioCarteiraDigital::listar($estornos());
    $ev->passo("2º DELETE /pedido-carona/{$pedido->id} (mesmo pedido) → HTTP ".CenarioCarteiraDigital::resposta($segundo)
        ." | saldo {$saldoAposSegundo} | estornos {$estornosAposSegundo}");

    $esperado = [
        '1º cancelamento: resposta' => '302 → /home',
        '1º cancelamento: status do pedido' => 'cancelado',
        '1º cancelamento: saldo' => '100.00',
        '1º cancelamento: estornos do pedido' => '1 (30.00)',
        '2º cancelamento: saldo' => '100.00',
        '2º cancelamento: estornos do pedido' => '1 (30.00)',
    ];

    $obtido = [
        '1º cancelamento: resposta' => CenarioCarteiraDigital::resposta($primeiro),
        '1º cancelamento: status do pedido' => $statusDoPedido,
        '1º cancelamento: saldo' => $saldoAposPrimeiro,
        '1º cancelamento: estornos do pedido' => $estornosAposPrimeiro,
        '2º cancelamento: saldo' => $saldoAposSegundo,
        '2º cancelamento: estornos do pedido' => $estornosAposSegundo,
    ];

    $ev->concluir(
        $esperado,
        $obtido,
        "1º cancelamento: saldo {$saldoAposPrimeiro} e estornos {$estornosAposPrimeiro}; "
        ."2º cancelamento: saldo {$saldoAposSegundo} e estornos {$estornosAposSegundo}.",
        'O cancelarPedido não confere o status do pedido e o realizarEstorno não confere se a retenção já foi '
        .'estornada: cada DELETE repetido devolve os R$ 30,00 de novo (dá para gerar saldo). A rota também não '
        .'verifica se o pedido pertence ao usuário logado.'
    );

    expect($obtido)->toBe($esperado);
});

/*
|--------------------------------------------------------------------------
| CT06 — Liquidação ao finalizar o trajeto
|--------------------------------------------------------------------------
| Teste de laço em consolidarTransacoes (0 e N iterações), ramo "continue"
| (sem retenção com sucesso) e a guarda "total > 0" de depositarAjudaCusto.
*/
test('CT06 · liquidação ao finalizar o trajeto', function () {
    $ev = EvidenciaCarteiraDigital::caso('CT06');
    $servico = app(TrajetoService::class);

    // Cenário A: laço com 0 iterações.
    $motoristaA = CenarioCarteiraDigital::usuario('0.00');
    $trajetoVazio = CenarioCarteiraDigital::trajeto($motoristaA, StatusTrajeto::EM_ANDAMENTO);
    $ev->passo("Cenário A (0 iterações): motorista #{$motoristaA->id} com saldo 0.00 e trajeto #{$trajetoVazio->id} em_andamento sem caronas");

    $servico->finalizarTrajeto($trajetoVazio->id);
    $ev->passo("finalizarTrajeto(#{$trajetoVazio->id}) executado");

    $cenarioA = [
        'status' => Trajeto::find($trajetoVazio->id)?->status?->value ?? '—',
        'liquidacoes' => (string) Transacao::where('tipo', TipoTransacao::LIQUIDACAO)->count(),
        'ajuda' => CenarioCarteiraDigital::ajudaDeCusto($motoristaA),
        'saldo' => CenarioCarteiraDigital::saldo($motoristaA),
    ];

    // Cenário B: laço com N iterações.
    $motoristaB = CenarioCarteiraDigital::usuario('0.00');
    $trajeto = CenarioCarteiraDigital::trajeto($motoristaB, StatusTrajeto::EM_ANDAMENTO);

    $roteiro = [
        'A' => ['70.00', StatusCarona::EM_ANDAMENTO, '30.00', StatusTransacao::SUCESSO],
        'B' => ['87.50', StatusCarona::EM_ANDAMENTO, '12.50', StatusTransacao::SUCESSO],
        'C' => ['50.00', StatusCarona::EM_ANDAMENTO, '18.00', StatusTransacao::FALHOU],
        'D' => ['80.00', StatusCarona::CANCELADA_PASSAGEIRO, '20.00', StatusTransacao::SUCESSO],
    ];

    $passageiros = $pedidos = $caronas = [];
    foreach ($roteiro as $letra => [$saldo, $statusCarona, $retido, $statusRetencao]) {
        $passageiros[$letra] = CenarioCarteiraDigital::usuario($saldo);
        $pedidos[$letra] = CenarioCarteiraDigital::pedido($passageiros[$letra], StatusPedidoCarona::ATENDIDO);
        $caronas[$letra] = CenarioCarteiraDigital::carona($trajeto, $pedidos[$letra], $statusCarona);
        CenarioCarteiraDigital::transacao($passageiros[$letra], TipoTransacao::RETENCAO, $retido, $pedidos[$letra], $statusRetencao);
    }

    $ev->passo("Cenário B (N iterações): motorista #{$motoristaB->id} com saldo 0.00 e trajeto #{$trajeto->id} em_andamento "
        .'com caronas A, B, C (em_andamento) e D (cancelada_passageiro)');
    $ev->passo('Retenções dos passageiros: A = 30.00 sucesso · B = 12.50 sucesso · C = 18.00 falhou (força o continue) · D = 20.00 sucesso');

    $servico->finalizarTrajeto($trajeto->id);
    $ev->passo("finalizarTrajeto(#{$trajeto->id}) executado");

    $porLetra = fn (callable $coletar) => implode(' | ', array_map($coletar, array_keys($roteiro)));

    $esperado = [
        'A: status do trajeto' => 'concluido',
        'A: liquidações criadas' => '0',
        'A: ajuda de custo ao motorista' => '0',
        'A: saldo do motorista' => '0.00',
        'B: status do trajeto' => 'concluido',
        'B: caronas A | B | C | D' => 'concluida | concluida | concluida | cancelada_passageiro',
        'B: liquidações A | B | C | D' => '30.00 | 12.50 | — | —',
        'B: estornos criados' => '0',
        'B: saldos dos passageiros A | B | C | D' => '70.00 | 87.50 | 50.00 | 80.00',
        'B: saldo do motorista' => '42.50',
        'B: ajuda de custo ao motorista' => "1 (42.50) · trajeto #{$trajeto->id}",
    ];

    $obtido = [
        'A: status do trajeto' => $cenarioA['status'],
        'A: liquidações criadas' => $cenarioA['liquidacoes'],
        'A: ajuda de custo ao motorista' => $cenarioA['ajuda'],
        'A: saldo do motorista' => $cenarioA['saldo'],
        'B: status do trajeto' => Trajeto::find($trajeto->id)?->status?->value ?? '—',
        'B: caronas A | B | C | D' => $porLetra(fn ($l) => Carona::find($caronas[$l]->id)?->status?->value ?? '—'),
        'B: liquidações A | B | C | D' => $porLetra(function ($l) use ($pedidos, $passageiros) {
            $valores = Transacao::where('pedido_carona_id', $pedidos[$l]->id)
                ->where('user_id', $passageiros[$l]->id)
                ->where('tipo', TipoTransacao::LIQUIDACAO)
                ->pluck('valor')
                ->map(fn ($v) => CenarioCarteiraDigital::dinheiro($v));

            return $valores->isEmpty() ? '—' : $valores->implode(' + ');
        }),
        'B: estornos criados' => (string) Transacao::whereIn('pedido_carona_id', collect($pedidos)->pluck('id'))
            ->where('tipo', TipoTransacao::ESTORNO)
            ->count(),
        'B: saldos dos passageiros A | B | C | D' => $porLetra(fn ($l) => CenarioCarteiraDigital::saldo($passageiros[$l])),
        'B: saldo do motorista' => CenarioCarteiraDigital::saldo($motoristaB),
        'B: ajuda de custo ao motorista' => CenarioCarteiraDigital::ajudaDeCusto($motoristaB),
    ];

    $ev->concluir(
        $esperado,
        $obtido,
        "A: trajeto {$obtido['A: status do trajeto']}, {$obtido['A: liquidações criadas']} liquidações, ajuda de custo "
        ."{$obtido['A: ajuda de custo ao motorista']}. B: liquidações {$obtido['B: liquidações A | B | C | D']}, "
        ."{$obtido['B: estornos criados']} estornos, motorista com saldo {$obtido['B: saldo do motorista']} "
        ."e ajuda de custo {$obtido['B: ajuda de custo ao motorista']}."
    );

    $ev->nota('Achado (não altera o status): pedidos_carona não tem a coluna valor_final, então $pedido->valor_final é '
        .'sempre null em consolidarTransacoes. Os ramos de teto (valor final > retido) e de estorno da diferença nunca '
        .'executam, e o passageiro sempre paga o máximo retido (R$ 3/km).');

    expect($obtido)->toBe($esperado);
});

/*
|--------------------------------------------------------------------------
| CT07 — Falha ao gravar a transação desfaz o depósito (rollback)
|--------------------------------------------------------------------------
| Injeção de falha: um listener em Transacao::creating lança exceção depois
| que depositarCarteira já incrementou o saldo dentro do DB::transaction.
*/
test('CT07 · rollback do depósito quando a transação falha', function () {
    $ev = EvidenciaCarteiraDigital::caso('CT07');

    $usuario = CenarioCarteiraDigital::usuario('50.00');
    $ev->passo("Usuário #{$usuario->id} criado com carteira de saldo 50.00");

    $saldoNoMomentoDaFalha = '—';
    Transacao::creating(function () use ($usuario, &$saldoNoMomentoDaFalha) {
        $saldoNoMomentoDaFalha = CenarioCarteiraDigital::saldo($usuario);

        throw new RuntimeException('Falha simulada ao gravar a transação');
    });
    $ev->passo('Falha injetada: listener Transacao::creating lança RuntimeException ao gravar a transação');

    $excecao = null;
    try {
        app(PagamentoService::class)->depositarValor($usuario->id, '25.00');
    } catch (Throwable $e) {
        $excecao = $e;
    } finally {
        Transacao::flushEventListeners();
    }
    $ev->passo('depositarValor(usuario, "25.00") executado → '.($excecao ? 'lançou '.$excecao::class : 'sem exceção')
        ." | saldo lido dentro da transação, no momento da falha: {$saldoNoMomentoDaFalha}");

    $esperado = [
        'Exceção propagada' => 'RuntimeException: Falha simulada ao gravar a transação',
        'Saldo dentro da transação (antes da falha)' => '75.00',
        'Saldo após o rollback' => '50.00',
        'Transações do usuário' => '0',
    ];

    $obtido = [
        'Exceção propagada' => $excecao ? $excecao::class.': '.$excecao->getMessage() : 'nenhuma',
        'Saldo dentro da transação (antes da falha)' => $saldoNoMomentoDaFalha,
        'Saldo após o rollback' => CenarioCarteiraDigital::saldo($usuario),
        'Transações do usuário' => (string) Transacao::where('user_id', $usuario->id)->count(),
    ];

    $ev->concluir(
        $esperado,
        $obtido,
        CenarioCarteiraDigital::excecao($excecao)."; saldo {$saldoNoMomentoDaFalha} dentro da transação e "
        ."{$obtido['Saldo após o rollback']} depois do rollback; {$obtido['Transações do usuário']} transações gravadas.",
        'O incremento do saldo não foi desfeito junto com a falha na gravação da transação.'
    );

    expect($obtido)->toBe($esperado);
});

/*
|--------------------------------------------------------------------------
| CT08 — Extrato oculta retenções liquidadas e fecha com o saldo real
|--------------------------------------------------------------------------
| MC/DC na condição composta do scope withContextAndLedger:
|   C1 = tipo fora de (retencao, reembolso, estorno)
|   C2 = não existe liquidação do mesmo pedido
|   a transação aparece no extrato se C1 OU C2.
*/
test('CT08 · extrato e saldo histórico (MC/DC)', function () {
    $ev = EvidenciaCarteiraDigital::caso('CT08');
    $this->withoutVite();

    $passageiro = CenarioCarteiraDigital::usuario('55.00');
    $motorista = CenarioCarteiraDigital::usuario('0.00');
    $trajeto = CenarioCarteiraDigital::trajeto($motorista, StatusTrajeto::CONCLUIDO);
    $pedidoA = CenarioCarteiraDigital::pedido($passageiro, StatusPedidoCarona::ATENDIDO);
    CenarioCarteiraDigital::carona($trajeto, $pedidoA, StatusCarona::CONCLUIDA);
    $pedidoB = CenarioCarteiraDigital::pedido($passageiro, StatusPedidoCarona::CANCELADO);
    $pedidoC = CenarioCarteiraDigital::pedido($passageiro, StatusPedidoCarona::PROCURANDO_MOTORISTA);
    $ev->passo("Passageiro PADRAO #{$passageiro->id} com saldo 55.00; pedido A #{$pedidoA->id} (atendido, carona concluída), "
        ."B #{$pedidoB->id} (cancelado) e C #{$pedidoC->id} (procurando_motorista)");

    $roteiro = [
        1 => [TipoTransacao::DEPOSITO, '100.00', null],
        2 => [TipoTransacao::RETENCAO, '30.00', $pedidoA],
        3 => [TipoTransacao::LIQUIDACAO, '30.00', $pedidoA],
        4 => [TipoTransacao::RETENCAO, '20.00', $pedidoB],
        5 => [TipoTransacao::ESTORNO, '20.00', $pedidoB],
        6 => [TipoTransacao::RETENCAO, '15.00', $pedidoC],
    ];

    $numeroDaTransacao = [];
    foreach ($roteiro as $numero => [$tipo, $valor, $pedido]) {
        $numeroDaTransacao[CenarioCarteiraDigital::transacao($passageiro, $tipo, $valor, $pedido)->id] = $numero;
        $this->travel(1)->minutes();
    }
    $this->travelBack();
    $ev->passo('Transações criadas em ordem cronológica (1 min entre elas): #1 depósito 100.00 · #2 retenção A 30.00 · '
        .'#3 liquidação A 30.00 · #4 retenção B 20.00 · #5 estorno B 20.00 · #6 retenção C 15.00');

    $resposta = $this->actingAs($passageiro)->get(route('transacao.index'));
    $view = CenarioCarteiraDigital::nomeDaView($resposta);
    $lista = $view === 'usuario.atividade' ? collect($resposta->viewData('transacoes')) : collect();
    $ev->passo('GET /atividade → HTTP '.$resposta->status()." | view {$view} | {$lista->count()} transações no extrato");

    $exibidas = $lista->map(fn ($t) => $numeroDaTransacao[$t->id] ?? 0)->all();
    $situacao = fn (int $numero) => in_array($numero, $exibidas, true) ? 'exibida' : 'oculta';
    $cronologico = $lista->sortBy(fn ($t) => $numeroDaTransacao[$t->id] ?? 0)
        ->map(fn ($t) => CenarioCarteiraDigital::dinheiro($t->saldo_historico))
        ->implode(' → ');
    $saldoFinal = $lista->isEmpty() ? '—' : CenarioCarteiraDigital::dinheiro($lista->first()->saldo_historico);

    $esperado = [
        'Resposta (HTTP | view)' => '200 | usuario.atividade',
        '#1 depósito (C1=V, C2=V)' => 'exibida',
        '#2 retenção A (C1=F, C2=F)' => 'oculta',
        '#3 liquidação A (C1=V, C2=F)' => 'exibida',
        '#4 retenção B (C1=F, C2=V)' => 'exibida',
        '#5 estorno B (C1=F, C2=V)' => 'exibida',
        '#6 retenção C (C1=F, C2=V)' => 'exibida',
        'Ordem no extrato' => '#6, #5, #4, #3, #1',
        'Saldo histórico (cronológico)' => '100.00 → 70.00 → 50.00 → 70.00 → 55.00',
        'Saldo histórico final × carteira' => 'final 55.00 | carteira 55.00',
    ];

    $obtido = [
        'Resposta (HTTP | view)' => $resposta->status().' | '.$view,
        '#1 depósito (C1=V, C2=V)' => $situacao(1),
        '#2 retenção A (C1=F, C2=F)' => $situacao(2),
        '#3 liquidação A (C1=V, C2=F)' => $situacao(3),
        '#4 retenção B (C1=F, C2=V)' => $situacao(4),
        '#5 estorno B (C1=F, C2=V)' => $situacao(5),
        '#6 retenção C (C1=F, C2=V)' => $situacao(6),
        'Ordem no extrato' => implode(', ', array_map(fn ($n) => "#{$n}", $exibidas)) ?: '—',
        'Saldo histórico (cronológico)' => $cronologico ?: '—',
        'Saldo histórico final × carteira' => "final {$saldoFinal} | carteira ".CenarioCarteiraDigital::saldo($passageiro),
    ];

    $ev->concluir(
        $esperado,
        $obtido,
        "Extrato com {$obtido['Ordem no extrato']}; retenção #2 {$obtido['#2 retenção A (C1=F, C2=F)']}; "
        ."saldo histórico {$obtido['Saldo histórico (cronológico)']} ({$obtido['Saldo histórico final × carteira']})."
    );

    $ev->nota('Pares MC/DC: #3 × #2 isola C1 (C2 fixo em F) e #4 × #2 isola C2 (C1 fixo em F); #1 cobre o caso V,V. '
        .'C1 = tipo fora de (retencao, reembolso, estorno); C2 = não existe liquidação do mesmo pedido.');

    expect($obtido)->toBe($esperado);
});

/*
|--------------------------------------------------------------------------
| Montagem dos cenários
|--------------------------------------------------------------------------
*/
final class CenarioCarteiraDigital
{
    public static function usuario(string $saldo): User
    {
        // A factory não define "tipo"; sem PADRAO o middleware VerificarTipo barra as rotas.
        $usuario = User::factory()->create(['tipo' => TipoUsuario::PADRAO]);

        // "saldo" não é fillable em Carteira.
        $usuario->carteira()->create()->forceFill(['saldo' => $saldo])->save();

        return $usuario;
    }

    public static function pedido(User $passageiro, StatusPedidoCarona $status = StatusPedidoCarona::PROCURANDO_MOTORISTA): PedidoCarona
    {
        return PedidoCarona::create([
            'user_id' => $passageiro->id,
            'origem_coords' => new Point(-49.2733, -25.4284),
            'origem_endereco' => 'Rua de Origem, 100 - Centro',
            'destino_coords' => new Point(-49.2512, -25.4497),
            'destino_endereco' => 'Rua de Destino, 200 - Campus',
            'status' => $status,
        ]);
    }

    public static function trajeto(User $motorista, StatusTrajeto $status): Trajeto
    {
        return Trajeto::create([
            'user_id' => $motorista->id,
            'origem_coords' => new Point(-49.2733, -25.4284),
            'origem_endereco' => 'Rua de Origem, 100 - Centro',
            'destino_coords' => new Point(-49.2512, -25.4497),
            'destino_endereco' => 'Rua de Destino, 200 - Campus',
            'rota' => [
                'type' => 'LineString',
                'coordinates' => [[-49.2733, -25.4284], [-49.2620, -25.4390], [-49.2512, -25.4497]],
            ],
            'status' => $status,
        ]);
    }

    public static function carona(Trajeto $trajeto, PedidoCarona $pedido, StatusCarona $status): Carona
    {
        return Carona::create([
            'trajeto_id' => $trajeto->id,
            'pedido_carona_id' => $pedido->id,
            'status' => $status,
        ]);
    }

    public static function transacao(
        User $usuario,
        TipoTransacao $tipo,
        string $valor,
        ?PedidoCarona $pedido = null,
        StatusTransacao $status = StatusTransacao::SUCESSO,
    ): Transacao {
        return Transacao::create([
            'user_id' => $usuario->id,
            'pedido_carona_id' => $pedido?->id,
            'tipo' => $tipo,
            'status' => $status,
            'valor' => $valor,
        ]);
    }

    public static function saldo(User $usuario): string
    {
        return self::dinheiro(Carteira::where('user_id', $usuario->id)->value('saldo'));
    }

    public static function dinheiro(mixed $valor): string
    {
        return $valor === null ? '—' : number_format((float) $valor, 2, '.', '');
    }

    /** "0" quando vazio; senão "quantidade (valor, valor...)". */
    public static function listar(iterable $transacoes): string
    {
        $valores = collect($transacoes)->map(fn (Transacao $t) => self::dinheiro($t->valor));

        return $valores->isEmpty() ? '0' : $valores->count().' ('.$valores->implode(', ').')';
    }

    public static function ajudaDeCusto(User $motorista): string
    {
        $ajudas = Transacao::where('user_id', $motorista->id)->where('tipo', TipoTransacao::AJUDA_CUSTO)->get();

        if ($ajudas->isEmpty()) {
            return '0';
        }

        return self::listar($ajudas).' · trajeto #'.$ajudas->pluck('trajeto_id')->implode(', #');
    }

    public static function excecao(?Throwable $excecao): string
    {
        return $excecao ? 'Lançou '.$excecao::class.' ("'.$excecao->getMessage().'")' : 'Sem exceção';
    }

    public static function descrever(Transacao $transacao): string
    {
        return $transacao->tipo->value.' | '.$transacao->status->value.' | '.self::dinheiro($transacao->valor);
    }

    public static function resposta(TestResponse $resposta): string
    {
        $destino = $resposta->headers->get('Location');

        return $resposta->status().($destino ? ' → '.(parse_url($destino, PHP_URL_PATH) ?: '/') : '');
    }

    /**
     * Mensagens de validação da última requisição, por campo. A sessão do projeto usa
     * serialization = json, então depois de salva ela guarda os erros como array.
     *
     * @return array<string, list<string>>
     */
    public static function errosDaSessao(): array
    {
        $erros = session('errors');

        if ($erros instanceof ViewErrorBag) {
            return $erros->getBag('default')->getMessages();
        }

        return is_array($erros) ? ($erros['default']['messages'] ?? []) : [];
    }

    public static function nomeDaView(TestResponse $resposta): string
    {
        return $resposta->original instanceof View ? $resposta->original->name() : 'sem view';
    }
}

/*
|--------------------------------------------------------------------------
| Impressão das evidências no terminal
|--------------------------------------------------------------------------
*/
final class EvidenciaCarteiraDigital
{
    /** ID => [título, prioridade, tipo de teste, alvo] */
    public const CASOS = [
        'CT01' => [
            'Depósito válido atualiza o saldo e gera transação', 'Alta',
            'Integração (HTTP) · cobertura de instruções',
            'CarteiraController::depositar e exibir → PagamentoService::depositarValor',
        ],
        'CT02' => [
            'Depósito com valor inválido é rejeitado', 'Média',
            'Integração (HTTP) · cobertura de condições (cada regra de validação falsa isoladamente)',
            'Validação de CarteiraController::depositar (required|min:0|decimal:0,2)',
        ],
        'CT03' => [
            'Retenção aprovada com saldo exatamente igual ao valor', 'Alta',
            'Integração · cobertura de decisão (ramo falso) com valor-limite',
            'PagamentoService::reterValor / debitarCarteira',
        ],
        'CT04' => [
            'Retenção recusada por saldo insuficiente', 'Alta',
            'Integração · cobertura de decisão (ramo verdadeiro) + tratamento de exceção',
            'PagamentoService::reterValor',
        ],
        'CT05' => [
            'Cancelar o mesmo pedido duas vezes não pode estornar duas vezes', 'Alta',
            'Integração (HTTP) · fluxo de dados (def-uso da retenção)',
            'DELETE pedido-carona.destroy → PedidoCaronaService::cancelarPedido → PagamentoService::realizarEstorno',
        ],
        'CT06' => [
            'Liquidação ao finalizar o trajeto', 'Alta',
            'Integração · teste de laço (0 e N iterações) + decisões internas',
            'TrajetoService::finalizarTrajeto → PagamentoService::consolidarTransacoes e depositarAjudaCusto',
        ],
        'CT07' => [
            'Falha ao gravar a transação desfaz o depósito', 'Alta',
            'Integração · tratamento de exceção e rollback (injeção de falha)',
            'PagamentoService::depositarValor (DB::transaction)',
        ],
        'CT08' => [
            'Extrato oculta retenções liquidadas e fecha com o saldo real', 'Média',
            'Integração (HTTP) · MC/DC na condição composta do scope',
            'TransacaoController::index + Transacao::scopeWithContextAndLedger',
        ],
    ];

    /** @var array<string, list<array{variacao: ?string, ok: bool, resumo: string}>> */
    private static array $resultados = [];

    private static ?string $log = null;

    private static bool $cabecalhoImpresso = false;

    private int $passos = 0;

    private function __construct(private readonly string $id, private readonly ?string $variacao) {}

    public static function iniciarLog(): void
    {
        $pasta = dirname(__DIR__, 3).'/storage/logs';
        self::$log = is_dir($pasta) && is_writable($pasta) ? $pasta.'/evidencias-carteira-digital.log' : null;

        if (self::$log !== null) {
            file_put_contents(self::$log, '');
        }
    }

    public static function caso(string $id, ?string $variacao = null): self
    {
        if (! self::$cabecalhoImpresso) {
            self::cabecalhoDaExecucao();
        }

        [$titulo, $prioridade, $tipo, $alvo] = self::CASOS[$id];

        self::escrever();
        self::escrever(self::cor(str_repeat('═', self::largura()), '36'));
        self::escrever(' '.self::cor("{$id} · {$titulo}", '1;36').($variacao ? self::cor("  [{$variacao}]", '1;33') : ''));
        self::bloco(' Prioridade: ', $prioridade, '2');
        self::bloco(' Tipo de teste: ', $tipo, '2');
        self::bloco(' Alvo: ', $alvo, '2');
        self::escrever(self::cor(str_repeat('─', self::largura()), '90'));

        return new self($id, $variacao);
    }

    public function passo(string $texto): void
    {
        $this->passos++;
        self::bloco(" Passo {$this->passos} │ ", $texto, '1');
    }

    public function nota(string $texto): void
    {
        self::escrever();
        self::bloco(' Nota │ ', $texto, '33');
    }

    /**
     * Imprime a tabela Esperado × Obtido, o status OK/NOk e registra o resultado para o resumo.
     *
     * @param  array<string, string>  $esperado
     * @param  array<string, string>  $obtido
     */
    public function concluir(array $esperado, array $obtido, string $resumo, ?string $defeito = null): bool
    {
        $larguraUtil = self::largura() - 12;
        $colItem = (int) floor($larguraUtil * 0.30);
        $colEsperado = (int) floor(($larguraUtil - $colItem) / 2);
        $colObtido = $larguraUtil - $colItem - $colEsperado;

        self::escrever();
        self::escrever('  '.self::cor(
                self::ajustar('Verificação', $colItem).' │ '.self::ajustar('Resultado esperado', $colEsperado)
                .' │ '.self::ajustar('Resultado obtido', $colObtido).' │',
                '1'
            ));
        self::escrever('  '.self::cor(
                str_repeat('─', $colItem).'─┼─'.str_repeat('─', $colEsperado).'─┼─'.str_repeat('─', $colObtido).'─┼──',
                '90'
            ));

        $conferem = 0;
        foreach ($esperado as $item => $valorEsperado) {
            $valorObtido = $obtido[$item] ?? '(não coletado)';
            $igual = $valorObtido === $valorEsperado;
            $conferem += $igual ? 1 : 0;

            $linhasItem = self::quebrar($item, $colItem);
            $linhasEsperado = self::quebrar($valorEsperado, $colEsperado);
            $linhasObtido = self::quebrar($valorObtido, $colObtido);
            $altura = max(count($linhasItem), count($linhasEsperado), count($linhasObtido));

            for ($i = 0; $i < $altura; $i++) {
                $celulaObtido = self::ajustar($linhasObtido[$i] ?? '', $colObtido);
                $marca = $i > 0 ? '' : ($igual ? self::cor('✓', '1;32') : self::cor('✗', '1;31'));

                self::escrever('  '.self::ajustar($linhasItem[$i] ?? '', $colItem)
                    .' │ '.self::ajustar($linhasEsperado[$i] ?? '', $colEsperado)
                    .' │ '.($igual ? $celulaObtido : self::cor($celulaObtido, '31'))
                    .' │ '.$marca);
            }
        }

        $ok = $esperado === $obtido;
        $placar = "{$conferem}/".count($esperado).' verificações conferem com o esperado';

        self::escrever();
        self::escrever('  '.($ok ? self::cor(' OK ', '1;30;42') : self::cor(' NOk ', '1;97;41')).'  '.self::cor('Status do caso — ', '1').$placar);

        if (! $ok && $defeito !== null) {
            self::bloco('  Defeito encontrado: ', $defeito, '1;31');
        }

        self::$resultados[$this->id][] = ['variacao' => $this->variacao, 'ok' => $ok, 'resumo' => $resumo];

        return $ok;
    }

    public static function imprimirResumo(): void
    {
        if (! self::$cabecalhoImpresso) {
            return;
        }

        $largura = self::largura();
        $colCaso = (int) floor(($largura - 16) * 0.34);
        $colResultado = $largura - 16 - $colCaso;

        self::escrever();
        self::escrever(self::cor(str_repeat('═', $largura), '36'));
        self::escrever(' '.self::cor('RESUMO DA EXECUÇÃO — CARTEIRA DIGITAL (João Pozzan)', '1;36'));
        self::escrever(self::cor(str_repeat('─', $largura), '90'));
        self::escrever('  '.self::cor(
                self::ajustar('ID', 4).'  '.self::ajustar('Caso de teste', $colCaso).'  '.self::ajustar('Ok?', 4)
                .'  '.self::ajustar('Resultado obtido', $colResultado),
                '1'
            ));

        $totais = ['OK' => 0, 'NOk' => 0, '—' => 0];

        foreach (self::CASOS as $id => [$titulo]) {
            $registros = self::$resultados[$id] ?? [];

            if ($registros === []) {
                $status = '—';
                $resumo = 'Sem registro: o caso não rodou ou parou num erro inesperado (ver a saída do Pest).';
            } else {
                $aprovados = count(array_filter($registros, fn ($r) => $r['ok']));
                $status = $aprovados === count($registros) ? 'OK' : 'NOk';
                $resumo = count($registros) === 1
                    ? $registros[0]['resumo']
                    : "{$aprovados}/".count($registros).' entradas conforme o esperado — '
                    .implode('; ', array_map(fn ($r) => "{$r['variacao']}: {$r['resumo']}", $registros));
            }

            $totais[$status]++;

            $linhasCaso = self::quebrar($titulo, $colCaso);
            $linhasResultado = self::quebrar($resumo, $colResultado);
            $altura = max(count($linhasCaso), count($linhasResultado));
            $corStatus = match ($status) {
                'OK' => '1;32',
                'NOk' => '1;31',
                default => '1;33',
            };

            self::escrever(self::cor('  '.str_repeat('┄', $largura - 2), '90'));

            for ($i = 0; $i < $altura; $i++) {
                self::escrever('  '.self::ajustar($i === 0 ? $id : '', 4)
                    .'  '.self::ajustar($linhasCaso[$i] ?? '', $colCaso)
                    .'  '.($i === 0 ? self::cor(self::ajustar($status, 4), $corStatus) : self::ajustar('', 4))
                    .'  '.($linhasResultado[$i] ?? ''));
            }
        }

        $executados = $totais['OK'] + $totais['NOk'];
        $aprovacao = $executados > 0 ? (int) round($totais['OK'] * 100 / $executados) : 0;

        self::escrever(self::cor(str_repeat('─', $largura), '90'));
        self::escrever('  '.self::cor(count(self::CASOS).' casos', '1')
            .' · '.self::cor("{$totais['OK']} OK", '1;32')
            .' · '.self::cor("{$totais['NOk']} NOk", '1;31')
            .($totais['—'] > 0 ? ' · '.self::cor("{$totais['—']} sem registro", '1;33') : '')
            ." · aprovação {$aprovacao}% (critério do plano: ≥ 80%)");

        if (self::$log !== null) {
            self::escrever('  Evidências também salvas em storage/logs/'.basename(self::$log));
        }

        self::escrever(self::cor(str_repeat('═', $largura), '36'));
        self::escrever();
    }

    private static function cabecalhoDaExecucao(): void
    {
        self::$cabecalhoImpresso = true;

        $agora = (new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo')))->format('d/m/Y H:i:s');
        $banco = DB::connection()->getDriverName().' / '.DB::connection()->getDatabaseName();

        self::escrever();
        self::escrever(self::cor(str_repeat('━', self::largura()), '36'));
        self::escrever(' '.self::cor('EVIDÊNCIAS DE TESTE — CARTEIRA DIGITAL (área 5 do Plano de Teste do CoCar)', '1;36'));
        self::escrever(" Responsável: João Pozzan · Execução: {$agora} (horário de Brasília)");
        self::escrever(' Ambiente: PHP '.PHP_VERSION.' · Laravel '.app()->version().' · Pest '.Pest\version()." · banco {$banco}");
        self::escrever(self::cor(str_repeat('━', self::largura()), '36'));
    }

    /** Imprime um rótulo seguido de texto quebrado com recuo alinhado ao rótulo. */
    private static function bloco(string $rotulo, string $texto, string $corDoRotulo): void
    {
        $recuo = mb_strlen($rotulo);
        $linhas = self::quebrar($texto, max(20, self::largura() - $recuo));

        foreach ($linhas as $i => $linha) {
            self::escrever(($i === 0 ? self::cor($rotulo, $corDoRotulo) : str_repeat(' ', $recuo)).$linha);
        }
    }

    /** @return list<string> */
    private static function quebrar(string $texto, int $largura): array
    {
        $linhas = [];

        foreach (explode("\n", $texto) as $paragrafo) {
            $atual = '';

            foreach (preg_split('/ +/u', $paragrafo) as $palavra) {
                while (mb_strlen($palavra) > $largura) {
                    if ($atual !== '') {
                        $linhas[] = $atual;
                        $atual = '';
                    }
                    $linhas[] = mb_substr($palavra, 0, $largura);
                    $palavra = mb_substr($palavra, $largura);
                }

                $candidata = $atual === '' ? $palavra : "{$atual} {$palavra}";

                if (mb_strlen($candidata) <= $largura) {
                    $atual = $candidata;
                } else {
                    $linhas[] = $atual;
                    $atual = $palavra;
                }
            }

            $linhas[] = $atual;
        }

        return $linhas;
    }

    private static function ajustar(string $texto, int $largura): string
    {
        return mb_str_pad($texto, $largura);
    }

    private static function largura(): int
    {
        static $largura = null;

        return $largura ??= max(80, min(120, (new Terminal)->getWidth())) - 1;
    }

    private static function cor(string $texto, string $codigo): string
    {
        return self::usarCores() ? "\e[{$codigo}m{$texto}\e[0m" : $texto;
    }

    private static function usarCores(): bool
    {
        static $cores = null;

        if ($cores !== null) {
            return $cores;
        }

        $argumentos = $_SERVER['argv'] ?? [];

        if (in_array('--colors=never', $argumentos, true) || getenv('NO_COLOR') !== false) {
            return $cores = false;
        }

        if (in_array('--colors=always', $argumentos, true)) {
            return $cores = true;
        }

        if (DIRECTORY_SEPARATOR === '\\') {
            return $cores = function_exists('sapi_windows_vt100_support') && @sapi_windows_vt100_support(STDOUT, true);
        }

        return $cores = function_exists('stream_isatty') && @stream_isatty(STDOUT);
    }

    private static function escrever(string $linha = ''): void
    {
        fwrite(STDOUT, $linha.PHP_EOL);

        if (self::$log !== null) {
            file_put_contents(self::$log, preg_replace('/\e\[[0-9;]*m/', '', $linha).PHP_EOL, FILE_APPEND);
        }
    }
}