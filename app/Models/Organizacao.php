<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $cnpj
 * @property string $nome
 * @property string|null $dominio_email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Beneficio> $beneficios
 * @property-read int|null $beneficios_count
 * @property-read Collection<int, User> $integrantes
 * @property-read int|null $integrantes_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacao newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacao newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacao query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacao whereCnpj($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacao whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacao whereDominioEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacao whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacao whereNome($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organizacao whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Organizacao extends Model
{
    use HasFactory;

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
