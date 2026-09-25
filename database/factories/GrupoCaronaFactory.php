<?php

namespace Database\Factories;

use App\Models\GrupoCarona;
use App\Models\PerfilMotorista;
use Illuminate\Database\Eloquent\Factories\Factory;

class GrupoCaronaFactory extends Factory
{
    protected $model = GrupoCarona::class;

    public function definition(): array
    {
        return [
            'perfil_motorista_id' => PerfilMotorista::factory(),
            'nome' => fake()->words(2, true),
            'frequencia' => 'semanal',
            'dias_semana' => ['seg', 'qua'],
            'dias_mes' => null,
            'vagas' => 3,
        ];
    }
}
