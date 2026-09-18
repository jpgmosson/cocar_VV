<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property numeric $saldo
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carteira newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carteira newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carteira query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carteira whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carteira whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carteira whereSaldo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carteira whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Carteira whereUserId($value)
 * @mixin \Eloquent
 */
class Carteira extends Model
{
    protected $fillable = [
        'user_id',
    ];

    /**
     * @return BelongsTo<User,Carteira>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
