<?php

namespace Database\Factories;

use App\Enums\StatusCarona;
use App\Models\Carona;
use App\Models\PedidoCarona;
use App\Models\Trajeto;
use Illuminate\Database\Eloquent\Factories\Factory;

class CaronaFactory extends Factory
{
    protected $model = Carona::class;

    public function definition(): array
    {
        return [
            'trajeto_id' => Trajeto::factory(),
            'pedido_carona_id' => PedidoCarona::factory(),
            'status' => StatusCarona::EM_ANDAMENTO,
        ];
    }
}
