<?php

namespace App\Services;

use App\Enums\StatusCarona;
use App\Enums\StatusTransacao;
use App\Enums\TipoTransacao;
use App\Models\Carona;
use App\Models\Carteira;
use App\Models\Transacao;
use Exception;
use Illuminate\Support\Facades\DB;

class PagamentoService
{
    private function debitarCarteira(int $userID, string $valor): bool
    {
        $carteira = Carteira::where('user_id', $userID)->lockForUpdate()->firstOrFail();

        if (bccomp($carteira->saldo, $valor, 2) === -1) {
            return false;
        }

        $carteira->decrement('saldo', $valor);

        return true;
    }

    private function depositarCarteira(int $userID, string $valor): void
    {
        $carteira = Carteira::where('user_id', $userID)->lockForUpdate()->firstOrFail();
        $carteira->increment('saldo', $valor);
    }

    public function reterValor(int $userID, string $valor, int $pedidoCaronaID): void
    {
        if (! $this->debitarCarteira($userID, $valor)) {
            Transacao::create([
                'user_id' => $userID,
                'pedido_carona_id' => $pedidoCaronaID,
                'tipo' => TipoTransacao::RETENCAO,
                'status' => StatusTransacao::FALHOU,
                'valor' => $valor,
            ]);

            throw new Exception('Saldo insuficiente para garantir a vaga da carona.');
        }

        Transacao::create([
            'user_id' => $userID,
            'pedido_carona_id' => $pedidoCaronaID,
            'tipo' => TipoTransacao::RETENCAO,
            'status' => StatusTransacao::SUCESSO,
            'valor' => $valor,
        ]);
    }

    public function depositarValor(int $userID, string $valor): void
    {
        DB::transaction(function () use ($userID, $valor) {
            $this->depositarCarteira($userID, $valor);

            Transacao::create([
                'user_id' => $userID,
                'tipo' => TipoTransacao::DEPOSITO,
                'status' => StatusTransacao::SUCESSO,
                'valor' => $valor,
            ]);
        });
    }

    public function realizarEstorno(Transacao $transacao): void
    {
        DB::transaction(function () use ($transacao) {
            $this->depositarCarteira($transacao->user_id, $transacao->valor);

            Transacao::create([
                'user_id' => $transacao->user_id,
                'tipo' => TipoTransacao::ESTORNO,
                'status' => StatusTransacao::SUCESSO,
                'valor' => $transacao->valor,
                'pedido_carona_id' => $transacao->pedido_carona_id,
            ]);
        });
    }

    public function consolidarTransacoes(int $trajetoID): string
    {
        $caronas = Carona::where('trajeto_id', $trajetoID)
            ->where('status', StatusCarona::CONCLUIDA)
            ->with('pedidoCarona')
            ->get();

        $totalConsolidado = '0.00';

        foreach ($caronas as $carona) {
            $pedido = $carona->pedidoCarona;

            $retencao = Transacao::where('pedido_carona_id', $pedido->id)
                ->where('tipo', TipoTransacao::RETENCAO)
                ->where('status', StatusTransacao::SUCESSO)
                ->first();

            if (! $retencao) {
                continue;
            }

            $valorFinal = $pedido->valor_final ?? $retencao->valor;

            $valorCobrado = bccomp($valorFinal, $retencao->valor, 2) === 1
                ? $retencao->valor
                : $valorFinal;

            $diferencaEstorno = bcsub($retencao->valor, $valorCobrado, 2);

            if (bccomp($diferencaEstorno, '0.00', 2) === 1) {
                $this->depositarCarteira($retencao->user_id, $diferencaEstorno);

                Transacao::create([
                    'user_id' => $retencao->user_id,
                    'pedido_carona_id' => $pedido->id,
                    'tipo' => TipoTransacao::ESTORNO,
                    'status' => StatusTransacao::SUCESSO,
                    'valor' => $diferencaEstorno,
                ]);
            }

            Transacao::create([
                'user_id' => $retencao->user_id,
                'pedido_carona_id' => $pedido->id,
                'tipo' => TipoTransacao::LIQUIDACAO,
                'status' => StatusTransacao::SUCESSO,
                'valor' => $valorCobrado,
            ]);

            $totalConsolidado = bcadd($totalConsolidado, $valorCobrado, 2);
        }

        return $totalConsolidado;
    }

    public function depositarAjudaCusto(int $motoristaID, int $trajetoID, string $valorTotal): void
    {
        if (bccomp($valorTotal, '0.00', 2) !== 1) {
            return;
        }

        $this->depositarCarteira($motoristaID, $valorTotal);

        Transacao::create([
            'user_id' => $motoristaID,
            'trajeto_id' => $trajetoID,
            'tipo' => TipoTransacao::AJUDA_CUSTO,
            'status' => StatusTransacao::SUCESSO,
            'valor' => $valorTotal,
        ]);
    }
}
