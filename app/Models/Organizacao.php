<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $cnpj
 * @property string $nome
 * @property string|null $dominio_email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Beneficio> $beneficios
 * @property-read int|null $beneficios_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $integrantes
 * @property-read int|null $integrantes_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacao query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacao whereCnpj($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacao whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacao whereDominioEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacao whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacao whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Organizacao extends Model
{
    protected $table = 'organizacoes';

    protected $fillable = ['nome', 'cnpj', 'dominio_email'];

    /**
     * @return HasMany<User,$this>
     */
    public function integrantes(): HasMany
    {
        return $this->hasMany(User::class);
    }
    public function beneficios()
    {
        return $this->hasMany(Beneficio::class);
    }
}
