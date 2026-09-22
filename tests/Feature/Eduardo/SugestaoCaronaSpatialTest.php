<?php

namespace Tests\Feature\Eduardo;

use App\Enums\StatusPedidoCarona;
use App\Models\Carona;
use App\Models\Organizacao;
use App\Models\PedidoCarona;
use App\Models\Trajeto;
use App\Models\User;
use App\Services\SugestaoCaronaService;
use App\ValueObjects\Point;
use Tests\TestCase;

class SugestaoCaronaSpatialTest extends TestCase
{
    public function test_obter_sugestoes_filtra_estritamente_distancia_organizacao_e_vinculo(): void
    {
        $orgA = Organizacao::create(['nome' => 'Org A', 'cnpj' => '11111111000111', 'dominio_email' => 'orga.com']);
        $orgB = Organizacao::create(['nome' => 'Org B', 'cnpj' => '22222222000122', 'dominio_email' => 'orgb.com']);

        $motorista = User::factory()->create(['organizacao_id' => $orgA->id]);
        $passageiro1 = User::factory()->create(['organizacao_id' => $orgA->id]);
        $passageiro2 = User::factory()->create(['organizacao_id' => $orgB->id]);
        $passageiro3 = User::factory()->create(['organizacao_id' => $orgA->id]);
        $passageiro4 = User::factory()->create(['organizacao_id' => $orgA->id]);
        $passageiro5 = User::factory()->create(['organizacao_id' => $orgA->id]);

        $lineGeojson = json_encode([
            'type' => 'LineString',
            'coordinates' => [[0.0, 0.0], [1.0, 0.0]],
        ]);

        $trajeto = Trajeto::create([
            'user_id' => $motorista->id,
            'origem_coords' => new Point(0.0, 0.0),
            'origem_endereco' => 'Origem',
            'destino_coords' => new Point(1.0, 0.0),
            'destino_endereco' => 'Destino',
            'rota' => $lineGeojson,
        ]);

        $pedidoValido = PedidoCarona::create([
            'user_id' => $passageiro1->id,
            'origem_coords' => new Point(0.5, 0.027),
            'origem_endereco' => 'Origem P1',
            'destino_coords' => new Point(1.0, 0.018),
            'destino_endereco' => 'Destino P1',
            'status' => StatusPedidoCarona::PROCURANDO_MOTORISTA,
        ]);

        PedidoCarona::create([
            'user_id' => $passageiro2->id,
            'origem_coords' => new Point(0.5, 0.027),
            'origem_endereco' => 'Origem P2',
            'destino_coords' => new Point(1.0, 0.018),
            'destino_endereco' => 'Destino P2',
            'status' => StatusPedidoCarona::PROCURANDO_MOTORISTA,
        ]);

        PedidoCarona::create([
            'user_id' => $passageiro3->id,
            'origem_coords' => new Point(0.5, 0.06),
            'origem_endereco' => 'Origem P3',
            'destino_coords' => new Point(1.0, 0.0),
            'destino_endereco' => 'Destino P3',
            'status' => StatusPedidoCarona::PROCURANDO_MOTORISTA,
        ]);

        PedidoCarona::create([
            'user_id' => $passageiro4->id,
            'origem_coords' => new Point(0.5, 0.01),
            'origem_endereco' => 'Origem P4',
            'destino_coords' => new Point(1.0, 0.07),
            'destino_endereco' => 'Destino P4',
            'status' => StatusPedidoCarona::PROCURANDO_MOTORISTA,
        ]);

        $pedidoJaVinculado = PedidoCarona::create([
            'user_id' => $passageiro5->id,
            'origem_coords' => new Point(0.2, 0.01),
            'origem_endereco' => 'Origem P5',
            'destino_coords' => new Point(1.0, 0.01),
            'destino_endereco' => 'Destino P5',
            'status' => StatusPedidoCarona::PROCURANDO_MOTORISTA,
        ]);
        Carona::create([
            'trajeto_id' => $trajeto->id,
            'pedido_carona_id' => $pedidoJaVinculado->id,
        ]);

        $service = new SugestaoCaronaService;
        $sugestoes = $service->obterSugestoesParaTrajeto($trajeto->id);

        $this->assertCount(1, $sugestoes);
        $this->assertEquals($pedidoValido->id, $sugestoes->first()->id);
        $this->assertNotNull($sugestoes->first()->desvio_metros);
        $this->assertLessThan(5000, $sugestoes->first()->desvio_metros);
    }
}
