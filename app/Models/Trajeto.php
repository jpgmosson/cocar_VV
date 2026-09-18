<?php

namespace App\Models;

use App\Casts\GeoJSONCast;
use App\Casts\PointCast;
use App\Enums\StatusTrajeto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property User|null $motorista
 * @property mixed|null $localizacao_motorista
 * @property mixed $origem
 * @property mixed $destino
 * @property mixed $rota
 * @property float $distancia_percorrida
 * @property numeric $custo
 * @property TrajetoStatus $status
 * @property string|null $horario_inicio
 * @property string|null $horario_fim
 * @property-read Collection<int, Carona> $caronas
 * @property-read int|null $caronas_count
 * @property-read Collection<int, PedidoCarona> $pedidosCarona
 * @property-read int|null $pedidos_carona_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereCusto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereDestino($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereDistanciaPercorrida($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereHorarioFim($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereHorarioInicio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereLocalizacaoMotorista($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereMotorista($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereOrigem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereRota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereStatus($value)
 *
 * @property int $user_id
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereUserId($value)
 *
 * @property string $endereco_origem
 * @property string $endereco_destino
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereEnderecoDestino($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereEnderecoOrigem($value)
 *
 * @property mixed $origem_coords
 * @property string $origem_endereco
 * @property mixed $destino_coords
 * @property string $destino_endereco
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereDestinoCoords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereDestinoEndereco($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereOrigemCoords($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Trajeto whereOrigemEndereco($value)
 *
 * @mixin \Eloquent
 */
class Trajeto extends Model
{
    protected $fillable = [
        'origem_coords',
        'status',
        'localizacao_motorista',
        'horario_inicio',
        'horario_fim',
        'origem_endereco',
        'destino_coords',
        'destino_endereco',
        'rota',
        'distancia_percorrida',
        'custo_total',
        'user_id',
    ];

    protected $casts = [
        'origem_coords' => PointCast::class,
        'destino_coords' => PointCast::class,
        'rota' => GeoJSONCast::class,
        'localizacao_motorista' => PointCast::class,
        'status' => StatusTrajeto::class,
    ];

    /**
     * @return BelongsTo<User,Carona>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pedidosCarona(): BelongsToMany
    {
        return $this->belongsToMany(PedidoCarona::class, 'caronas', 'trajeto_id', 'pedido_carona_id')->withPivot('status', 'ordem');
    }

    /**
     * @return HasMany<Carona,Trajeto>
     */
    public function caronas(): HasMany
    {
        return $this->hasMany(Carona::class);
    }

    public function distanciaPercorridaKM(): string
    {
        return bcdiv($this->distancia_percorrida, '1000', 2);
    }

    protected static function booted(): void
    {
        static::addGlobalScope('as_geojson', function ($builder) {
            $builder->addSelect([
                '*',
                DB::raw('ST_AsGeoJSON(origem_coords) as origem_coords'),
                DB::raw('ST_AsGeoJSON(destino_coords) as destino_coords'),
                DB::raw('ST_AsGeoJSON(rota) as rota'),
                DB::raw('ST_AsGeoJSON(localizacao_motorista) as localizacao_motorista'),
            ]);
        });
    }
}
