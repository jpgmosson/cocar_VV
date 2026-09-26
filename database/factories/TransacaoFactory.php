<?php

namespace Database\Factories;

use App\Enums\StatusTransacao;
use App\Enums\TipoTransacao;
use App\Models\Transacao;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transacao>
 */
class TransacaoFactory extends Factory
{
    protected $model = Transacao::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'pedido_carona_id' => null,
            'tipo' => TipoTransacao::DEPOSITO,
            'status' => StatusTransacao::SUCESSO,
            'valor' => '50.00',
        ];
    }
}
