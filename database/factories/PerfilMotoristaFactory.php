<?php

namespace Database\Factories;

use App\Models\PerfilMotorista;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PerfilMotoristaFactory extends Factory
{
    protected $model = PerfilMotorista::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'cnh' => fake()->unique()->numerify('###########'),
            'aprovado_em' => now(),
        ];
    }

    public function pendente(): static
    {
        return $this->state(fn () => ['aprovado_em' => null]);
    }
}
