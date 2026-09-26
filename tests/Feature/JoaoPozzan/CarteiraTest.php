<?php

namespace Tests\Feature\JoaoPozzan;

use App\Enums\StatusCarona;
use App\Enums\StatusPedidoCarona;
use App\Enums\StatusTrajeto;
use App\Enums\StatusTransacao;
use App\Enums\TipoTransacao;
use App\Enums\TipoUsuario;
use App\Models\Carona;
use App\Models\PedidoCarona;
use App\Models\Trajeto;
use App\Models\Transacao;
use App\Models\User;
use App\Services\PagamentoService;
use App\Services\TrajetoService;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class CarteiraTest extends TestCase
{
    private function criarUsuarioComSaldo(string $saldo = '0.00'): User
    {
        $usuario = User::factory()->create(['tipo' => TipoUsuario::PADRAO]);
        $usuario->carteira()->create()->forceFill(['saldo' => $saldo])->save();

        return $usuario;
    }

    public function test_deposito_valido_atualiza_saldo_e_gera_transacao(): void
    {
        $usuario = $this->criarUsuarioComSaldo('50.00');

        $response = $this->actingAs($usuario)
            ->from(route('usuario.carteira'))
            ->patch(route('carteira.depositar'), ['valor' => '25.50']);

        $response->assertRedirect(route('usuario.carteira'));
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('carteiras', [
            'user_id' => $usuario->id,
            'saldo' => '75.50',
        ]);

        $this->assertDatabaseHas('transacoes', [
            'user_id' => $usuario->id,
            'tipo' => TipoTransacao::DEPOSITO,
            'status' => StatusTransacao::SUCESSO,
            'valor' => '25.50',
            'pedido_carona_id' => null,
            'trajeto_id' => null,
        ]);

        $this->actingAs($usuario)
            ->get(route('usuario.carteira'))
            ->assertOk()
            ->assertViewIs('usuario.carteira')
            ->assertViewHas('carteira', fn ($carteira) => (float) $carteira->saldo === 75.50);
    }

    public static function valoresInvalidosDepositoProvider(): array
    {
        return [
            'campo ausente' => [null],
            'valor negativo' => ['-10.00'],
            'mais de duas casas decimais' => ['10.555'],
            'nao numerico' => ['abc'],
            'valor zero' => ['0.00'],
        ];
    }

    #[DataProvider('valoresInvalidosDepositoProvider')]
    public function test_deposito_com_valor_invalido_e_rejeitado(?string $valor): void
    {
        $usuario = $this->criarUsuarioComSaldo('50.00');
        $payload = $valor === null ? [] : ['valor' => $valor];

        $response = $this->actingAs($usuario)
            ->from(route('usuario.carteira'))
            ->patch(route('carteira.depositar'), $payload);

        $response->assertRedirect(route('usuario.carteira'));
        $response->assertSessionHasErrors('valor');

        $this->assertDatabaseHas('carteiras', [
            'user_id' => $usuario->id,
            'saldo' => '50.00',
        ]);

        $this->assertDatabaseCount('transacoes', 0);
    }

    public function test_retencao_com_saldo_exatamente_igual_ao_valor(): void
    {
        $passageiro = $this->criarUsuarioComSaldo('30.00');
        $pedido = PedidoCarona::factory()->create(['user_id' => $passageiro->id]);

        app(PagamentoService::class)->reterValor($passageiro->id, '30.00', $pedido->id);

        $this->assertDatabaseHas('carteiras', [
            'user_id' => $passageiro->id,
            'saldo' => '0.00',
        ]);

        $this->assertDatabaseHas('transacoes', [
            'user_id' => $passageiro->id,
            'pedido_carona_id' => $pedido->id,
            'tipo' => TipoTransacao::RETENCAO,
            'status' => StatusTransacao::SUCESSO,
            'valor' => '30.00',
        ]);
    }

    public function test_retencao_com_saldo_insuficiente(): void
    {
        $passageiro = $this->criarUsuarioComSaldo('29.99');
        $pedido = PedidoCarona::factory()->create(['user_id' => $passageiro->id]);

        $this->expectException(Exception::class);

        try {
            app(PagamentoService::class)->reterValor($passageiro->id, '30.00', $pedido->id);
        } finally {
            $this->assertDatabaseHas('carteiras', [
                'user_id' => $passageiro->id,
                'saldo' => '29.99',
            ]);

            $this->assertDatabaseHas('transacoes', [
                'user_id' => $passageiro->id,
                'pedido_carona_id' => $pedido->id,
                'tipo' => TipoTransacao::RETENCAO,
                'status' => StatusTransacao::FALHOU,
                'valor' => '30.00',
            ]);
        }
    }

    public function test_cancelar_mesmo_pedido_nao_pode_estornar_duas_vezes(): void
    {
        $passageiro = $this->criarUsuarioComSaldo('100.00');
        $pedido = PedidoCarona::factory()->create([
            'user_id' => $passageiro->id,
            'status' => StatusPedidoCarona::PROCURANDO_MOTORISTA,
        ]);

        app(PagamentoService::class)->reterValor($passageiro->id, '30.00', $pedido->id);

        $this->actingAs($passageiro)
            ->delete(route('pedido-carona.destroy', $pedido))
            ->assertRedirect('/home');

        $this->assertDatabaseHas('pedidos_carona', [
            'id' => $pedido->id,
            'status' => StatusPedidoCarona::CANCELADO,
        ]);

        $this->assertDatabaseHas('carteiras', [
            'user_id' => $passageiro->id,
            'saldo' => '100.00',
        ]);

        // Segunda tentativa de cancelamento
        $this->actingAs($passageiro)
            ->delete(route('pedido-carona.destroy', $pedido));

        $this->assertDatabaseHas('carteiras', [
            'user_id' => $passageiro->id,
            'saldo' => '100.00',
        ]);

        $this->assertDatabaseCount('transacoes', 2); // 1 Retenção + 1 Estorno
        $this->assertEquals(
            1,
            Transacao::where('pedido_carona_id', $pedido->id)
                ->where('tipo', TipoTransacao::ESTORNO)
                ->count()
        );
    }

    public function test_liquidacao_ao_finalizar_trajeto_sem_caronas(): void
    {
        $motorista = $this->criarUsuarioComSaldo('0.00');
        $trajeto = Trajeto::factory()->create([
            'user_id' => $motorista->id,
            'status' => StatusTrajeto::EM_ANDAMENTO,
        ]);

        app(TrajetoService::class)->finalizarTrajeto($trajeto->id);

        $this->assertDatabaseHas('trajetos', [
            'id' => $trajeto->id,
            'status' => StatusTrajeto::CONCLUIDO,
        ]);

        $this->assertDatabaseCount('transacoes', 0);
        $this->assertDatabaseHas('carteiras', [
            'user_id' => $motorista->id,
            'saldo' => '0.00',
        ]);
    }

    public function test_liquidacao_ao_finalizar_trajeto_com_multiplas_caronas(): void
    {
        $motorista = $this->criarUsuarioComSaldo('0.00');
        $trajeto = Trajeto::factory()->create([
            'user_id' => $motorista->id,
            'status' => StatusTrajeto::EM_ANDAMENTO,
        ]);

        $passageiroA = $this->criarUsuarioComSaldo('70.00');
        $pedidoA = PedidoCarona::factory()->create(['user_id' => $passageiroA->id, 'status' => StatusPedidoCarona::ATENDIDO]);
        $caronaA = Carona::factory()->create(['trajeto_id' => $trajeto->id, 'pedido_carona_id' => $pedidoA->id, 'status' => StatusCarona::EM_ANDAMENTO]);
        Transacao::factory()->create(['user_id' => $passageiroA->id, 'pedido_carona_id' => $pedidoA->id, 'tipo' => TipoTransacao::RETENCAO, 'valor' => '30.00', 'status' => StatusTransacao::SUCESSO]);

        $passageiroB = $this->criarUsuarioComSaldo('87.50');
        $pedidoB = PedidoCarona::factory()->create(['user_id' => $passageiroB->id, 'status' => StatusPedidoCarona::ATENDIDO]);
        $caronaB = Carona::factory()->create(['trajeto_id' => $trajeto->id, 'pedido_carona_id' => $pedidoB->id, 'status' => StatusCarona::EM_ANDAMENTO]);
        Transacao::factory()->create(['user_id' => $passageiroB->id, 'pedido_carona_id' => $pedidoB->id, 'tipo' => TipoTransacao::RETENCAO, 'valor' => '12.50', 'status' => StatusTransacao::SUCESSO]);

        $passageiroC = $this->criarUsuarioComSaldo('50.00');
        $pedidoC = PedidoCarona::factory()->create(['user_id' => $passageiroC->id, 'status' => StatusPedidoCarona::ATENDIDO]);
        $caronaC = Carona::factory()->create(['trajeto_id' => $trajeto->id, 'pedido_carona_id' => $pedidoC->id, 'status' => StatusCarona::EM_ANDAMENTO]);
        Transacao::factory()->create(['user_id' => $passageiroC->id, 'pedido_carona_id' => $pedidoC->id, 'tipo' => TipoTransacao::RETENCAO, 'valor' => '18.00', 'status' => StatusTransacao::FALHOU]);

        $passageiroD = $this->criarUsuarioComSaldo('80.00');
        $pedidoD = PedidoCarona::factory()->create(['user_id' => $passageiroD->id, 'status' => StatusPedidoCarona::ATENDIDO]);
        $caronaD = Carona::factory()->create(['trajeto_id' => $trajeto->id, 'pedido_carona_id' => $pedidoD->id, 'status' => StatusCarona::CANCELADA_PASSAGEIRO]);
        Transacao::factory()->create(['user_id' => $passageiroD->id, 'pedido_carona_id' => $pedidoD->id, 'tipo' => TipoTransacao::RETENCAO, 'valor' => '20.00', 'status' => StatusTransacao::SUCESSO]);

        app(TrajetoService::class)->finalizarTrajeto($trajeto->id);

        $this->assertDatabaseHas('trajetos', ['id' => $trajeto->id, 'status' => StatusTrajeto::CONCLUIDO]);
        $this->assertDatabaseHas('caronas', ['id' => $caronaA->id, 'status' => StatusCarona::CONCLUIDA]);
        $this->assertDatabaseHas('caronas', ['id' => $caronaB->id, 'status' => StatusCarona::CONCLUIDA]);
        $this->assertDatabaseHas('caronas', ['id' => $caronaC->id, 'status' => StatusCarona::CONCLUIDA]);
        $this->assertDatabaseHas('caronas', ['id' => $caronaD->id, 'status' => StatusCarona::CANCELADA_PASSAGEIRO]);

        $this->assertDatabaseHas('transacoes', [
            'pedido_carona_id' => $pedidoA->id,
            'tipo' => TipoTransacao::LIQUIDACAO,
            'valor' => '30.00',
        ]);
        $this->assertDatabaseHas('transacoes', [
            'pedido_carona_id' => $pedidoB->id,
            'tipo' => TipoTransacao::LIQUIDACAO,
            'valor' => '12.50',
        ]);

        $this->assertDatabaseMissing('transacoes', [
            'pedido_carona_id' => $pedidoC->id,
            'tipo' => TipoTransacao::LIQUIDACAO,
        ]);
        $this->assertDatabaseMissing('transacoes', [
            'pedido_carona_id' => $pedidoD->id,
            'tipo' => TipoTransacao::LIQUIDACAO,
        ]);

        $this->assertDatabaseHas('carteiras', ['user_id' => $motorista->id, 'saldo' => '42.50']);
        $this->assertDatabaseHas('transacoes', [
            'user_id' => $motorista->id,
            'trajeto_id' => $trajeto->id,
            'tipo' => TipoTransacao::AJUDA_CUSTO,
            'valor' => '42.50',
        ]);
    }

    public function test_falha_ao_gravar_transacao_desfaz_deposito(): void
    {
        $usuario = $this->criarUsuarioComSaldo('50.00');

        Transacao::creating(function () {
            throw new RuntimeException('Falha simulada ao gravar a transação');
        });

        try {
            app(PagamentoService::class)->depositarValor($usuario->id, '25.00');
            $this->fail('A exceção esperada não foi lançada.');
        } catch (RuntimeException) {
            // Exceção esperada
        } finally {
            Transacao::flushEventListeners();
        }

        $this->assertDatabaseHas('carteiras', [
            'user_id' => $usuario->id,
            'saldo' => '50.00',
        ]);

        $this->assertDatabaseCount('transacoes', 0);
    }

    public function test_extrato_filtra_retencoes_liquidadas_e_mantem_saldo_historico(): void
    {
        $passageiro = $this->criarUsuarioComSaldo('55.00');
        $motorista = $this->criarUsuarioComSaldo('0.00');
        $trajeto = Trajeto::factory()->create(['user_id' => $motorista->id, 'status' => StatusTrajeto::CONCLUIDO]);

        $pedidoA = PedidoCarona::factory()->create(['user_id' => $passageiro->id, 'status' => StatusPedidoCarona::ATENDIDO]);
        Carona::factory()->create(['trajeto_id' => $trajeto->id, 'pedido_carona_id' => $pedidoA->id, 'status' => StatusCarona::CONCLUIDA]);
        $pedidoB = PedidoCarona::factory()->create(['user_id' => $passageiro->id, 'status' => StatusPedidoCarona::CANCELADO]);
        $pedidoC = PedidoCarona::factory()->create(['user_id' => $passageiro->id, 'status' => StatusPedidoCarona::PROCURANDO_MOTORISTA]);

        $deposito = Transacao::factory()->create(['user_id' => $passageiro->id, 'tipo' => TipoTransacao::DEPOSITO, 'valor' => '100.00']);
        $this->travel(1)->minutes();

        $retencaoA = Transacao::factory()->create(['user_id' => $passageiro->id, 'tipo' => TipoTransacao::RETENCAO, 'valor' => '30.00', 'pedido_carona_id' => $pedidoA->id]);
        $this->travel(1)->minutes();

        $liquidacaoA = Transacao::factory()->create(['user_id' => $passageiro->id, 'tipo' => TipoTransacao::LIQUIDACAO, 'valor' => '30.00', 'pedido_carona_id' => $pedidoA->id]);
        $this->travel(1)->minutes();

        $retencaoB = Transacao::factory()->create(['user_id' => $passageiro->id, 'tipo' => TipoTransacao::RETENCAO, 'valor' => '20.00', 'pedido_carona_id' => $pedidoB->id]);
        $this->travel(1)->minutes();

        $estornoB = Transacao::factory()->create(['user_id' => $passageiro->id, 'tipo' => TipoTransacao::ESTORNO, 'valor' => '20.00', 'pedido_carona_id' => $pedidoB->id]);
        $this->travel(1)->minutes();

        $retencaoC = Transacao::factory()->create(['user_id' => $passageiro->id, 'tipo' => TipoTransacao::RETENCAO, 'valor' => '15.00', 'pedido_carona_id' => $pedidoC->id]);

        $response = $this->actingAs($passageiro)
            ->get(route('transacao.index'))
            ->assertOk()
            ->assertViewIs('usuario.atividade');

        $transacoesExibidas = collect($response->viewData('transacoes'));

        $idsExibidos = $transacoesExibidas->pluck('id')->all();

        // Garante que a retenção que já virou liquidação fica oculta do extrato
        $this->assertNotContains($retencaoA->id, $idsExibidos);

        // Garante a presença dos demais registros no extrato
        $this->assertContains($deposito->id, $idsExibidos);
        $this->assertContains($liquidacaoA->id, $idsExibidos);
        $this->assertContains($retencaoB->id, $idsExibidos);
        $this->assertContains($estornoB->id, $idsExibidos);
        $this->assertContains($retencaoC->id, $idsExibidos);

        // Ordem cronológica decrescente (padrão de extrato)
        $this->assertEquals(
            [$retencaoC->id, $estornoB->id, $retencaoB->id, $liquidacaoA->id, $deposito->id],
            $idsExibidos
        );

        $this->assertEquals('55.00', number_format((float) $transacoesExibidas->first()->saldo_historico, 2, '.', ''));
    }
}
