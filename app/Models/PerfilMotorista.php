<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $cnh
 * @property string|null $aprovado_em
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Beneficio> $beneficios
 * @property-read int|null $beneficios_count
 * @property-read Collection<int, GrupoCarona> $grupos
 * @property-read int|null $grupos_count
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerfilMotorista newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerfilMotorista newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerfilMotorista query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerfilMotorista whereAprovadoEm($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerfilMotorista whereCnh($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerfilMotorista whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerfilMotorista whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerfilMotorista whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PerfilMotorista whereUserId($value)
 * @mixin \Eloquent
 */
class PerfilMotorista extends Model
{
    protected $fillable = ['cnh', 'aprovado_em'];

    protected $table = 'perfis_motorista';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grupos(): HasMany
    {
        return $this->hasMany(GrupoCarona::class, 'perfil_motorista_id');
    }

    public function beneficios(): BelongsToMany
    {
        return $this->belongsToMany(Beneficio::class, 'beneficio_motorista')
            ->withPivot('km_acumulado', 'status', 'atingido_em')
            ->withTimestamps();
    }
}
