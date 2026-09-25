<?php

namespace Database\Factories;

use App\Enums\StatusPedidoCarona;
use App\Models\PedidoCarona;
use App\Models\User;
use App\ValueObjects\Point;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PedidoCarona>
 */
class PedidoCaronaFactory extends Factory
{
    protected $model = PedidoCarona::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'origem_coords' => new Point(fake()->latitude(), fake()->longitude()),
            'origem_endereco' => fake()->streetAddress(),
            'destino_coords' => new Point(fake()->latitude(), fake()->longitude()),
            'destino_endereco' => fake()->streetAddress(),
            'status' => StatusPedidoCarona::PROCURANDO_MOTORISTA,
        ];
    }
}
