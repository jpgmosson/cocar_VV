<?php

namespace App\Models;

use App\Enums\StatusCarona;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $pedido_carona_id
 * @property int $trajeto_id
 * @property int|null $ordem
 * @property CaronaStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read PedidoCarona $pedidoCarona
 * @property-read Trajeto $trajeto
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carona newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carona newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carona query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carona whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carona whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carona whereOrdem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carona wherePedidoCaronaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carona whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carona whereTrajetoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carona whereUpdatedAt($value)
 * @method static Builder<static>|Carona withDistanciaPercorrida()
 * @property int|null $ordem_parada
 * @property string|null $horario_embarque
 * @property string|null $horario_desembarque
 * @method static Builder<static>|Carona whereHorarioDesembarque($value)
 * @method static Builder<static>|Carona whereHorarioEmbarque($value)
 * @method static Builder<static>|Carona whereOrdemParada($value)
 * @mixin \Eloquent
 */
class Carona extends Model
{
    protected $fillable = ['status', 'ordem_parada', 'pedido_carona_id'];

    protected $casts = ['status' => StatusCarona::class];

    /**
     * @return BelongsTo<Trajeto,Carona>
     */
    public function trajeto(): BelongsTo
    {
        return $this->belongsTo(Trajeto::class, 'trajeto_id');
    }

    /**
     * @return BelongsTo<PedidoCarona,Carona>
     */
    public function pedidoCarona(): BelongsTo
    {
        return $this->belongsTo(PedidoCarona::class, 'pedido_carona_id');
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function scopeWithDistanciaPercorrida(Builder $query): Builder
    {
        $query->addSelect([
            'caronas.*',
            'distancia_percorrida_carona' => DB::table('caronas as c')
                ->join('trajetos as t', 't.id', '=', 'c.trajeto_id')
                ->join('pedidos_carona as p', 'p.id', '=', 'c.pedido_carona_id')
                ->whereColumn('c.id', 'caronas.id')
                ->select(DB::raw('
                ST_Length(
                    ST_LineSubstring(
                        t.rota::geometry,
                        ST_LineLocatePoint(t.rota::geometry, p.origem::geometry),
                        ST_LineLocatePoint(t.rota::geometry, p.destino::geometry)
                    )::geography
                )
            ')),
        ]);

        return $query;
    }
}
