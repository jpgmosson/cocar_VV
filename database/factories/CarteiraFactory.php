<?php

namespace Database\Factories;

use App\Models\Carteira;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarteiraFactory extends Factory
{
    protected $model = Carteira::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'saldo' => '0.00',
        ];
    }
}
