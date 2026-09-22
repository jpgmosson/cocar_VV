<?php

namespace Tests\Feature\Eduardo;

use App\DataTransferObjects\MapRoute;
use App\DataTransferObjects\RouteResult;
use App\DataTransferObjects\RouteWaypoint;
use App\Enums\StatusCarona;
use App\Models\Carona;
use App\Models\PedidoCarona;
use App\Models\Trajeto;
use App\Models\User;
use App\Services\MapApiService;
use App\Services\TrajetoService;
use App\ValueObjects\Point;
use Mockery;
use Tests\TestCase;

class TrajetoAtualizarRotaTest extends TestCase
{
    public function test_atualizar_rota_corrige_rota_ineficiente_e_reordena_paradas(): void
    {
        $motorista = User::factory()->create();
        $passageiroPerto = User::factory()->create();
        $passageiroLonge = User::factory()->create();

        // Rota inicial ineficiente em zigue-zague: Origem (0.0) -> Parada Longe (0.6) -> Volta Parada Perto (0.2) -> Destino (1.0)
        $trajeto = Trajeto::create([
            'user_id' => $motorista->id,
            'origem_coords' => new Point(0.0, 0.0),
            'origem_endereco' => 'Origem (km 0)',
            'destino_coords' => new Point(1.0, 0.0),
            'destino_endereco' => 'Destino Final (km 100)',
            'rota' => json_encode([
                'type' => 'LineString',
                'coordinates' => [
                    [0.0, 0.0],
                    [0.6, 0.0],
                    [0.2, 0.0],
                    [1.0, 0.0],
                ],
            ]),
        ]);

        // Passageiro 1: Embarque mais distante (x = 0.6)
        $pedidoLonge = PedidoCarona::create([
            'user_id' => $passageiroLonge->id,
            'origem_coords' => new Point(0.6, 0.0),
            'origem_endereco' => 'Parada Distante',
            'destino_coords' => new Point(1.0, 0.0),
            'destino_endereco' => 'Destino',
        ]);

        // Passageiro 2: Embarque próximo ao início (x = 0.2)
        $pedidoPerto = PedidoCarona::create([
            'user_id' => $passageiroPerto->id,
            'origem_coords' => new Point(0.2, 0.0),
            'origem_endereco' => 'Parada Proxima',
            'destino_coords' => new Point(1.0, 0.0),
            'destino_endereco' => 'Destino',
        ]);

        // Caronas criadas com ordem inicial ineficiente (Longe primeiro, Perto depois)
        $caronaLonge = Carona::create([
            'trajeto_id' => $trajeto->id,
            'pedido_carona_id' => $pedidoLonge->id,
            'status' => StatusCarona::ACEITA,
            'ordem_parada' => 1,
        ]);

        $caronaPerto = Carona::create([
            'trajeto_id' => $trajeto->id,
            'pedido_carona_id' => $pedidoPerto->id,
            'status' => StatusCarona::ACEITA,
            'ordem_parada' => 2,
        ]);

        // Rota corrigida e eficiente: Linha contínua 0.0 -> 0.2 -> 0.6 -> 1.0
        $rotaOtimizadaGeoJson = json_encode([
            'type' => 'LineString',
            'coordinates' => [
                [0.0, 0.0],
                [0.2, 0.0],
                [0.6, 0.0],
                [1.0, 0.0],
            ],
        ]);

        $resultadoOtimizado = new RouteResult(
            routes: [new MapRoute($rotaOtimizadaGeoJson, 100_000.0, 3600.0)],
            waypoints: [
                new RouteWaypoint(index: 0, longitude: 0.0, latitude: 0.0),
                new RouteWaypoint(index: 1, longitude: 0.2, latitude: 0.0),
                new RouteWaypoint(index: 2, longitude: 0.6, latitude: 0.0),
                new RouteWaypoint(index: 3, longitude: 1.0, latitude: 0.0),
            ]
        );

        $mapApiMock = Mockery::mock(MapApiService::class);
        $mapApiMock->shouldReceive('obterRotaOtimizada')->once()->andReturn($resultadoOtimizado);
        $this->app->instance(MapApiService::class, $mapApiMock);

        $service = $this->app->make(TrajetoService::class);
        $service->atualizarRota($trajeto);

        // 1. A rota salva no trajeto deixa de ser o zigue-zague e assume o traçado otimizado
        $this->assertNotEquals($rotaIneficienteGeoJson, $trajeto->fresh()->rota);

        // 2. As ordens de parada foram corrigidas para a sequência lógica do caminho
        $this->assertEquals(1, $caronaPerto->fresh()->ordem_parada);
        $this->assertEquals(2, $caronaLonge->fresh()->ordem_parada);
    }
}
