<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $organizacao_id
 * @property string $nome
 * @property string|null $descricao
 * @property numeric $meta_km
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PerfilMotorista> $motoristas
 * @property-read int|null $motoristas_count
 * @property-read \App\Models\Organizacao $organizacao
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Beneficio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Beneficio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Beneficio query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Beneficio whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Beneficio whereDescricao($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Beneficio whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Beneficio whereMetaKm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Beneficio whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Beneficio whereOrganizacaoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Beneficio whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Beneficio extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizacao_id',
        'nome',
        'descricao',
        'meta_km',
    ];

    public function organizacao()
    {
        return $this->belongsTo(Organizacao::class);
    }
    public function motoristas()
    {
        return $this->belongsToMany(PerfilMotorista::class, 'beneficio_motorista')
            ->withPivot('km_acumulado', 'status', 'atingido_em')
            ->withTimestamps();
    }
}
