<?php

namespace App\Services;

use App\Enums\StatusPedidoCarona;
use App\Models\PedidoCarona;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SugestaoCaronaService
{
    private int $distanciaMaximaSugestaoCarona = 5000;

    private int $limiteSugestoesCarona = 24;

    /**
     * @return Collection<int,Model>
     */
    public function obterSugestoesParaTrajeto(int $trajetoID): Collection
    {
        return PedidoCarona::withoutGlobalScope('as_geojson')
            ->select([
                'pedidos_carona.*',
                DB::raw('ST_AsGeoJSON(pedidos_carona.origem_coords) as origem_coords'),
                DB::raw('ST_AsGeoJSON(pedidos_carona.destino_coords) as destino_coords'),
                DB::raw('ST_Distance(pedidos_carona.origem_coords, trajetos.rota)::float AS desvio_metros'),
            ])
            ->join('trajetos', function ($join) use ($trajetoID) {
                $join->where('trajetos.id', $trajetoID)
                    ->whereRaw('ST_DWithin(pedidos_carona.origem_coords, trajetos.rota, ?::float)', [$this->distanciaMaximaSugestaoCarona])
                    ->whereRaw('ST_DWithin(pedidos_carona.destino_coords, trajetos.destino_coords, ?::float)', [$this->distanciaMaximaSugestaoCarona]);
            })
            ->join('users as passageiro_user', 'passageiro_user.id', '=', 'pedidos_carona.user_id')
            ->join('users as motorista_user', 'motorista_user.id', '=', 'trajetos.user_id')
            ->whereColumn('passageiro_user.organizacao_id', '=', 'motorista_user.organizacao_id')
            ->where('pedidos_carona.status', StatusPedidoCarona::PROCURANDO_MOTORISTA)
            ->whereDoesntHave('caronas', function ($query) use ($trajetoID) {
                $query->where('trajeto_id', $trajetoID);
            })
            ->orderByRaw('ST_Distance(pedidos_carona.origem_coords, trajetos.rota)')
            ->limit($this->limiteSugestoesCarona)
            ->get();
    }
}
