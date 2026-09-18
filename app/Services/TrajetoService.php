<?php

namespace App\Services;

use App\Enums\FaseCarona;
use App\Enums\StatusCarona;
use App\Enums\StatusPedidoCarona;
use App\Enums\StatusTrajeto;
use App\Models\Carona;
use App\Models\PedidoCarona;
use App\Models\Trajeto;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TrajetoService
{
    private int $gapAceitavelDistanciaWaypoint = 500;

    private int $custoKM = 2;

    public function __construct(protected PagamentoService $pagamentoService, protected MapApiService $mapApiService) {}

    public function novoTrajeto(array $dados, int $userID): Trajeto
    {
        $result = $this->mapApiService->obterRotaDireta($dados['origem_coords'], $dados['destino_coords']);
        $geometry = $result->routes[0]->geometry;

        return Trajeto::create([
            ...$dados,
            'rota' => $geometry,
            'user_id' => $userID,
        ]);
    }

    public function iniciarTrajeto(Trajeto $trajeto): void
    {
        if ($trajeto->status != StatusTrajeto::PLANEJADO) {
            throw new Exception('Só é possível iniciar um trajeto planejado.');
        }

        $trajeto->update([
            'horario_inicio' => now(),
            'localizacao_motorista' => $trajeto->origem_coords,
            'status' => StatusTrajeto::EM_ANDAMENTO,
        ]);

        $trajeto->caronas()
            ->where('status', StatusCarona::ACEITA)
            ->update(['status' => StatusCarona::MOTORISTA_A_CAMINHO]);
    }

    public function finalizarTrajeto(int $trajetoID): void
    {
        DB::transaction(function () use ($trajetoID) {
            $trajeto = Trajeto::where('id', $trajetoID)
                ->where('status', StatusTrajeto::EM_ANDAMENTO)
                ->lockForUpdate()
                ->firstOrFail();

            $trajeto->update([
                'horario_fim' => now(),
                'localizacao_motorista' => $trajeto->destino_coords,
                'status' => StatusTrajeto::CONCLUIDO,
                'distancia_percorrida' => DB::raw('ST_Length(rota)'),
            ]);

            $trajeto->caronas()
                ->where('status', StatusCarona::EM_ANDAMENTO)
                ->update([
                    'status' => StatusCarona::CONCLUIDA,
                    'horario_desembarque' => now(),
                ]);

            $totalArrecadado = $this->pagamentoService->consolidarTransacoes($trajeto->id);
            $this->pagamentoService->depositarAjudaCusto($trajeto->user->id, $trajeto->id, $totalArrecadado);
        });
    }

    public function atualizarRota(Trajeto $trajeto): void
    {
        $paradas = $this->obterParadas($trajeto)->pluck('coord')->toArray();
        $result = $this->mapApiService->obterRotaOtimizada($paradas);

        DB::transaction(function () use ($trajeto, $result) {
            $geometry = $result->routes[0]->geometry;
            $trajeto->update(['rota' => $geometry]);

            $waypoints = $result->waypoints;
            array_shift($waypoints);
            array_pop($waypoints);

            foreach ($waypoints as $waypoint) {
                $carona = $trajeto->caronas()->whereHas('pedidoCarona', function ($query) use ($waypoint) {
                    $query->whereRaw(
                        'ST_DWithin(origem_coords, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ?)',
                        [$waypoint->longitude, $waypoint->latitude, $this->gapAceitavelDistanciaWaypoint]
                    );
                })->first();
                $carona->update(['ordem_parada' => $waypoint->index]);
            }
        });
    }

    public function obterParadas(Trajeto $trajeto): Collection
    {
        $trajeto->loadMissing('caronas.pedidoCarona');
        $paradas = $trajeto->caronas()
            ->whereIn('status', StatusCarona::naFase(FaseCarona::ATIVA))
            ->get()
            ->map(fn ($carona) => [
                'carona_id' => $carona->id,
                'coord' => $carona->pedidoCarona->origem_coords,
            ]);

        $paradas->prepend([
            'carona_id' => null,
            'coord' => $trajeto->origem_coords,
        ]);

        $paradas->push([
            'carona_id' => null,
            'coord' => $trajeto->destino_coords,
        ]);

        return $paradas;
    }

    private function calcularDistanciaCarona(Trajeto $trajeto, PedidoCarona $pedidoCarona): int
    {
        $resultado = DB::selectOne('
        SELECT ST_Length(
            ST_LineSubstring(
                :rota::geometry,
                ST_LineLocatePoint(:rota::geometry, :origem::geometry),
                ST_LineLocatePoint(:rota::geometry, :destino::geometry)
            )::geography
        ) AS distancia::INTEGER
    ', [
            'rota' => $trajeto->getRawOriginal('rota'),
            'origem' => $pedidoCarona->getRawOriginal('origem_coords'),
            'destino' => $pedidoCarona->getRawOriginal('destino_coord'),
        ]);

        return $resultado?->distancia ?? 0;
    }

    public function atenderPedidoCarona(Trajeto $trajeto, PedidoCarona $pedidoCarona): Carona
    {
        return DB::transaction(function () use ($trajeto, $pedidoCarona) {
            $pedidoCarona->update(['status' => StatusPedidoCarona::ATENDIDO]);
            $carona = $trajeto->caronas()->create(['pedido_carona_id' => $pedidoCarona->id]);
            $this->atualizarRota($trajeto);

            return $carona;
        });
    }

    public function embarcarPassageiro(Trajeto $trajeto, Carona $carona): void
    {
        $trajeto->update(['localizacao_motorista' => $carona->pedidoCarona->origem_coords]);
        $carona->update([
            'status' => StatusCarona::EM_ANDAMENTO,
            'horario_embarque' => now(),
        ]);
    }

    public function cancelarCarona(Carona $carona): void
    {
        DB::transaction(function () use ($carona) {
            $carona->update(['status' => StatusCarona::CANCELADA_MOTORISTA]);
            $carona->pedidoCarona()->update(['status' => StatusPedidoCarona::PROCURANDO_MOTORISTA]);
            $this->atualizarRota($carona->trajeto);
        });

    }

    public function cancelarTrajeto(Trajeto $trajeto): void
    {
        DB::transaction(function () use ($trajeto) {
            $trajeto->update(['status' => StatusTrajeto::CANCELADO]);

            PedidoCarona::whereHas('caronas', function ($query) use ($trajeto) {
                $query->where('trajeto_id', $trajeto->id)
                    ->whereIn('status', StatusCarona::naFase(FaseCarona::ATIVA));
            })->update(['status' => StatusPedidoCarona::PROCURANDO_MOTORISTA]);

            $trajeto->caronas()
                ->whereIn('status', StatusCarona::naFase(FaseCarona::ATIVA))
                ->update(['status' => StatusCarona::CANCELADA_MOTORISTA]);
        });
    }
}
