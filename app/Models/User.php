<?php

namespace App\Models;

use App\Enums\TipoUsuario;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property int|null $organizacao_id
 * @property TipoUsuario $tipo
 * @property string $cpf
 * @property-read Organizacao|null $Organizacao
 * @property-read Collection<int, Carona> $caronas
 * @property-read int|null $caronas_count
 * @property-read Carteira|null $carteira
 * @property-read mixed $e_admin_organizacao
 * @property-read mixed $e_admin_sistema
 * @property-read Collection<int, GrupoCarona> $grupoCaronas
 * @property-read int|null $grupo_caronas_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, PedidoCarona> $pedidosCarona
 * @property-read int|null $pedidos_carona_count
 * @property-read PerfilMotorista|null $perfilMotorista
 * @property-read Collection<int, Trajeto> $trajetos
 * @property-read int|null $trajetos_count
 * @method static Builder<static>|User comStatusMotorista(?string $filtroStatus = null)
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder<static>|User newModelQuery()
 * @method static Builder<static>|User newQuery()
 * @method static Builder<static>|User porTipo(?string $tipo)
 * @method static Builder<static>|User query()
 * @method static Builder<static>|User whereCpf($value)
 * @method static Builder<static>|User whereCreatedAt($value)
 * @method static Builder<static>|User whereEmail($value)
 * @method static Builder<static>|User whereEmailVerifiedAt($value)
 * @method static Builder<static>|User whereId($value)
 * @method static Builder<static>|User whereName($value)
 * @method static Builder<static>|User whereOrganizacaoId($value)
 * @method static Builder<static>|User wherePassword($value)
 * @method static Builder<static>|User whereRememberToken($value)
 * @method static Builder<static>|User whereTipo($value)
 * @method static Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static Builder<static>|User whereTwoFactorSecret($value)
 * @method static Builder<static>|User whereUpdatedAt($value)
 * @property-read \App\Models\Organizacao|null $organizacao
 * @mixin \Eloquent
 */
#[Fillable(['name', 'email', 'password', 'tipo', 'organizacao_id', 'cpf', 'observacao'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'tipo' => TipoUsuario::class,
        ];
    }

    /**
     * @return BelongsTo<Organizacao,User>
     */
    public function organizacao(): BelongsTo
    {
        return $this->belongsTo(Organizacao::class);
    }

    /**
     * @return HasOne<PerfilMotorista,User>
     */
    public function perfilMotorista(): HasOne
    {
        return $this->hasOne(PerfilMotorista::class);
    }

    public function eAdminOrganizacao(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->tipo == TipoUsuario::ADMINISTRADOR_ORGANIZACAO
        );

    }

    protected function eAdminSistema(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->tipo == TipoUsuario::ADMINISTRADOR_SISTEMA
        );
    }

    /**
     * @param  Builder<Model>  $query
     */
    public function scopeComStatusMotorista(Builder $query, ?string $filtroStatus = null): Builder
    {
        $query->withExists(['perfilMotorista as e_motorista'])
            ->withExists(['perfilMotorista as foi_aprovado' => function ($q) {
                $q->whereNotNull('aprovado_em');
            }]);

        return $query->when($filtroStatus, fn ($q) => match ($filtroStatus) {
            'pendente' => $q->whereHas('perfilMotorista', fn ($sq) => $sq->whereNull('aprovado_em')),
            default => $q,
            'aprovado' => $q->whereHas('perfilMotorista', fn ($sq) => $sq->whereNotNull('aprovado_em')),

        });
    }

    /**
     * @param  Builder<Model>  $query
     */
    public function scopePorTipo(Builder $query, ?string $tipo): Builder
    {
        return $query->when($tipo, function ($q) use ($tipo) {
            match ($tipo) {
                'admin-sistema' => $q->where('tipo', TipoUsuario::ADMINISTRADOR_SISTEMA),
                'admin-organizacao' => $q->where('tipo', TipoUsuario::ADMINISTRADOR_ORGANIZACAO),
                'motorista' => $q->whereHas('perfilMotorista'),
                'passageiro' => $q->where('tipo', TipoUsuario::PADRAO)
                    ->whereDoesntHave('perfilMotorista'),
                default => $q,
            };
        });
    }

    public function homeUrl(): string
    {
        return match ($this->tipo) {
            TipoUsuario::ADMINISTRADOR_ORGANIZACAO => 'admin.painel',
            TipoUsuario::ADMINISTRADOR_SISTEMA => 'admin.painel',
            TipoUsuario::PADRAO => 'home'
        };
    }

    /**
     * @return HasOne<Carteira,User>
     */
    public function carteira(): HasOne
    {
        return $this->hasOne(Carteira::class);
    }

    public function grupoCaronas(): BelongsToMany
    {
        return $this->belongsToMany(GrupoCarona::class, 'grupo_carona_user', 'user_id', 'grupo_carona_id')->withTimestamps();
    }

    /**
     * @return HasMany<Carona,User>
     */
    public function trajetos(): HasMany
    {
        return $this->hasMany(Trajeto::class);
    }

    /**
     * @return HasMany<PedidoCarona,User>
     */
    public function pedidosCarona(): HasMany
    {
        return $this->hasMany(PedidoCarona::class);
    }

    /**
     * @return HasManyThrough<Carona,Model,User>
     */
    public function caronas(): HasManyThrough
    {
        return $this->hasManyThrough(Carona::class, PedidoCarona::class);
    }

    public function iniciais(): string
    {
        return Str::initials($this->name);
    }
}
