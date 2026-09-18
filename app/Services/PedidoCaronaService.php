<?php

namespace App\Services;

use App\DataTransferObjects\PedidoCaronaEstimativa;
use App\Enums\FaseCarona;
use App\Enums\StatusCarona;
use App\Enums\StatusPedidoCarona;
use App\Enums\TipoTransacao;
use App\Exceptions\ExibivelException;
use App\Models\Carteira;
use App\Models\PedidoCarona;
use App\ValueObjects\Point;
use Illuminate\Support\Facades\DB;

class PedidoCaronaService
{
    private string $custoKmMax = '3';

    private string $custoKmMin = '2';

    public function __construct(protected MapApiService $mapApiService, protected PagamentoService $pagamentoService) {}

    public function estimativaCusto(Point $origem, Point $destino, int $userID): PedidoCaronaEstimativa
    {
        $result = $this->mapApiService->obterRotaDireta($origem, $destino);
        $distanciaMetros = $result->routes[0]->distance;
        $distanciaKm = bcdiv((string) $distanciaMetros, '1000', 2);

        $min = bcmul($distanciaKm, $this->custoKmMin, 2);
        $max = bcmul($distanciaKm, $this->custoKmMax, 2);
        $saldo = Carteira::where('user_id', $userID)->firstOrFail()->saldo;

        return new PedidoCaronaEstimativa(
            min: $min,
            max: $max,
            saldoUsuario: $saldo,
            saldoUsuarioSuficiente: bccomp($saldo, $max, 2) >= 0
        );
    }

    public function temCaronaAtiva(int $userID): bool
    {
        return PedidoCarona::where('user_id', $userID)
            ->where(function ($query) {
                $query->where('status', StatusPedidoCarona::PROCURANDO_MOTORISTA)
                    ->orWhereHas('caronas', function ($caronasQuery) {
                        $caronasQuery->whereIn('status', StatusCarona::naFase(FaseCarona::ATIVA));
                    });
            })
            ->exists();
    }

    public function novoPedido(array $dados, int $userID): PedidoCarona
    {
        if ($this->temCaronaAtiva($userID)) {
            throw new ExibivelException('Usuário já possui um pedido em andamento');
        }

        $estimativa = $this->estimativaCusto($dados['origem_coords'], $dados['destino_coords'], $userID);

        if (! $estimativa->saldoUsuarioSuficiente) {
            throw new ExibivelException('Saldo insuficiente');
        }

        return DB::transaction(function () use ($dados, $userID, $estimativa) {
            $pedidoCarona = PedidoCarona::create([...$dados, 'user_id' => $userID]);

            $this->pagamentoService->reterValor($userID, $estimativa->max, $pedidoCarona->id);

            return $pedidoCarona;
        });
    }

    public function cancelarPedido(PedidoCarona $pedidoCarona): void
    {
        DB::transaction(function () use ($pedidoCarona) {
            $pedidoCarona->update(['status' => StatusPedidoCarona::CANCELADO]);
            $pedidoCarona->caronas()
                ->whereIn('status', StatusCarona::naFase(FaseCarona::ATIVA))
                ->update(['status' => StatusCarona::CANCELADA_PASSAGEIRO]);
            $transacao = $pedidoCarona->transacoes()
                ->where('tipo', TipoTransacao::RETENCAO)
                ->firstOrFail();
            $this->pagamentoService->realizarEstorno($transacao);
        });
    }
}
