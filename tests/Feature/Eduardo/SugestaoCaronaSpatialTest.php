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
    // 1 grau latitude/longitude = 111.320 metros
    private const METROS_POR_GRAU = 111_320.0;

    private function offsetMetros(float $metros): float
    {
        return $metros / self::METROS_POR_GRAU;
    }

    public function test_obter_sugestoes_filtra_estritamente_distancia_organizacao_e_vinculo(): void
    {
        $orgA = Organizacao::create(['nome' => 'Org A', 'cnpj' => '11111111000111', 'dominio_email' => 'orga.com']);
        $orgB = Organizacao::create(['nome' => 'Org B', 'cnpj' => '22222222000122', 'dominio_email' => 'orgb.com']);

        $motorista = User::factory()->create(['organizacao_id' => $orgA->id]);
        $passageiroValido = User::factory()->create(['organizacao_id' => $orgA->id]);
        $passageiroOutraOrg = User::factory()->create(['organizacao_id' => $orgB->id]);
        $passageiroOrigemLonge = User::factory()->create(['organizacao_id' => $orgA->id]);
        $passageiroDestinoLonge = User::factory()->create(['organizacao_id' => $orgA->id]);
        $passageiroJaAceito = User::factory()->create(['organizacao_id' => $orgA->id]);

        $trajeto = Trajeto::create([
            'user_id' => $motorista->id,
            'origem_coords' => new Point(0.0, 0.0),
            'origem_endereco' => 'Origem',
            'destino_coords' => new Point(1.0, 0.0),
            'destino_endereco' => 'Destino',
            'rota' => json_encode([
                'type' => 'LineString',
                'coordinates' => [[0.0, 0.0], [1.0, 0.0]],
            ]),
        ]);

        // 1. Cenário Válido: Embarque a 3 km da rota, Desembarque a 2 km do destino
        $pedidoValido = PedidoCarona::create([
            'user_id' => $passageiroValido->id,
            'origem_coords' => new Point(0.5, $this->offsetMetros(3000)),
            'origem_endereco' => 'Embarque a 3km',
            'destino_coords' => new Point(1.0, $this->offsetMetros(2000)),
            'destino_endereco' => 'Desembarque a 2km',
            'status' => StatusPedidoCarona::PROCURANDO_MOTORISTA,
        ]);

        // 2. Inválido: Dentro do raio (3km/2km), mas pertence a outra organização
        PedidoCarona::create([
            'user_id' => $passageiroOutraOrg->id,
            'origem_coords' => new Point(0.5, $this->offsetMetros(3000)),
            'origem_endereco' => 'Embarque a 3km',
            'destino_coords' => new Point(1.0, $this->offsetMetros(2000)),
            'destino_endereco' => 'Desembarque a 2km',
            'status' => StatusPedidoCarona::PROCURANDO_MOTORISTA,
        ]);

        // 3. Inválido: Origem fora do raio limite de 5 km (a 6,5 km da rota)
        PedidoCarona::create([
            'user_id' => $passageiroOrigemLonge->id,
            'origem_coords' => new Point(0.5, $this->offsetMetros(6500)),
            'origem_endereco' => 'Embarque a 6.5km',
            'destino_coords' => new Point(1.0, 0.0),
            'destino_endereco' => 'Desembarque exato',
            'status' => StatusPedidoCarona::PROCURANDO_MOTORISTA,
        ]);

        // 4. Inválido: Destino fora do raio limite de 5 km (a 7,5 km do fim da rota)
        PedidoCarona::create([
            'user_id' => $passageiroDestinoLonge->id,
            'origem_coords' => new Point(0.5, $this->offsetMetros(1000)),
            'origem_endereco' => 'Embarque a 1km',
            'destino_coords' => new Point(1.0, $this->offsetMetros(7500)),
            'destino_endereco' => 'Desembarque a 7.5km',
            'status' => StatusPedidoCarona::PROCURANDO_MOTORISTA,
        ]);

        // 5. Inválido: Distâncias válidas (1km), mas carona já aceita no mesmo trajeto
        $pedidoJaVinculado = PedidoCarona::create([
            'user_id' => $passageiroJaAceito->id,
            'origem_coords' => new Point(0.2, $this->offsetMetros(1000)),
            'origem_endereco' => 'Embarque a 1km',
            'destino_coords' => new Point(1.0, $this->offsetMetros(1000)),
            'destino_endereco' => 'Desembarque a 1km',
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
        $this->assertEqualsWithDelta(3000, $sugestoes->first()->desvio_metros, 50);
    }
}
