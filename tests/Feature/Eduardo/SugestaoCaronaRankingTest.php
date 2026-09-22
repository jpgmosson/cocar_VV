<?php

namespace Tests\Feature\Eduardo;

use App\Enums\StatusPedidoCarona;
use App\Models\Organizacao;
use App\Models\PedidoCarona;
use App\Models\Trajeto;
use App\Models\User;
use App\Services\SugestaoCaronaService;
use App\ValueObjects\Point;
use Tests\TestCase;

class SugestaoCaronaRankingTest extends TestCase
{
    public function test_sugestoes_respeitam_limite_de_24_e_ordenacao_ascendente_de_distancia(): void
    {
        $org = Organizacao::create(['nome' => 'Org Limite', 'cnpj' => '33333333000133', 'dominio_email' => 'limite.com']);
        $motorista = User::factory()->create(['organizacao_id' => $org->id]);

        $lineGeojson = json_encode([
            'type' => 'LineString',
            'coordinates' => [[-49.27, -25.42], [-49.20, -25.42]],
        ]);

        $trajeto = Trajeto::create([
            'user_id' => $motorista->id,
            'origem_coords' => new Point(-49.27, -25.42),
            'origem_endereco' => 'Origem',
            'destino_coords' => new Point(-49.20, -25.42),
            'destino_endereco' => 'Destino',
            'rota' => $lineGeojson,
        ]);

        for ($i = 1; $i <= 30; $i++) {
            $user = User::factory()->create(['organizacao_id' => $org->id]);
            $offsetLat = $i * 0.0009;

            PedidoCarona::create([
                'user_id' => $user->id,
                'origem_coords' => new Point(-49.25, -25.42 + $offsetLat),
                'origem_endereco' => "Origem {$i}",
                'destino_coords' => new Point(-49.20, -25.42),
                'destino_endereco' => 'Destino',
                'status' => StatusPedidoCarona::PROCURANDO_MOTORISTA,
            ]);
        }

        $service = new SugestaoCaronaService;
        $sugestoes = $service->obterSugestoesParaTrajeto($trajeto->id);

        $this->assertCount(24, $sugestoes);

        $distancias = $sugestoes->pluck('desvio_metros')->toArray();
        $distanciasOrdenadas = $distancias;
        sort($distanciasOrdenadas);

        $this->assertSame($distanciasOrdenadas, $distancias);
    }
}
