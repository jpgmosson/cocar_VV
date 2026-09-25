<?php

namespace Database\Factories;

use App\Enums\StatusTrajeto;
use App\Models\Trajeto;
use App\Models\User;
use App\ValueObjects\Point;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trajeto>
 */
class TrajetoFactory extends Factory
{
    protected $model = Trajeto::class;

    public function definition(): array
    {
        $latOrigem = fake()->latitude();
        $lngOrigem = fake()->longitude();
        $latDestino = fake()->latitude();
        $lngDestino = fake()->longitude();

        return [
            'user_id' => User::factory(),
            'origem_coords' => new Point($latOrigem, $lngOrigem),
            'origem_endereco' => fake()->streetAddress(),
            'destino_coords' => new Point($latDestino, $lngDestino),
            'destino_endereco' => fake()->streetAddress(),
            'rota' => json_encode([
                'type' => 'LineString',
                'coordinates' => [
                    [$lngOrigem, $latOrigem],
                    [$lngDestino, $latDestino],
                ],
            ]),
            'status' => StatusTrajeto::PLANEJADO,
        ];
    }
}
