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
    public function test_atualizar_rota_reordena_paradas_com_base_nos_waypoints_otimizados(): void
    {
        $motorista = User::factory()->create();
        $p1 = User::factory()->create();
        $p2 = User::factory()->create();

        $trajeto = Trajeto::create([
            'user_id' => $motorista->id,
            'origem_coords' => new Point(-49.270, -25.420),
            'origem_endereco' => 'Origem T',
            'destino_coords' => new Point(-49.200, -25.420),
            'destino_endereco' => 'Destino T',
            'rota' => '{"type":"LineString","coordinates":[[-49.270,-25.420],[-49.200,-25.420]]}',
        ]);

        $pedido1 = PedidoCarona::create([
            'user_id' => $p1->id,
            'origem_coords' => new Point(-49.250, -25.420),
            'origem_endereco' => 'P1',
            'destino_coords' => new Point(-49.200, -25.420),
            'destino_endereco' => 'Destino',
        ]);
        $carona1 = Carona::create(['trajeto_id' => $trajeto->id, 'pedido_carona_id' => $pedido1->id, 'status' => StatusCarona::ACEITA]);

        $pedido2 = PedidoCarona::create([
            'user_id' => $p2->id,
            'origem_coords' => new Point(-49.230, -25.420),
            'origem_endereco' => 'P2',
            'destino_coords' => new Point(-49.200, -25.420),
            'destino_endereco' => 'Destino',
        ]);
        $carona2 = Carona::create(['trajeto_id' => $trajeto->id, 'pedido_carona_id' => $pedido2->id, 'status' => StatusCarona::ACEITA]);

        $routeMock = new RouteResult(
            routes: [new MapRoute('{"type":"LineString","coordinates":[]}', 5000.0, 600.0)],
            waypoints: [
                new RouteWaypoint(0, -49.270, -25.420),
                new RouteWaypoint(1, -49.230, -25.420),
                new RouteWaypoint(2, -49.250, -25.420),
                new RouteWaypoint(3, -49.200, -25.420),
            ]
        );

        $mapApiMock = Mockery::mock(MapApiService::class);
        $mapApiMock->shouldReceive('obterRotaOtimizada')->once()->andReturn($routeMock);
        $this->app->instance(MapApiService::class, $mapApiMock);

        $service = $this->app->make(TrajetoService::class);
        $service->atualizarRota($trajeto);

        $this->assertEquals(2, $carona1->fresh()->ordem_parada);
        $this->assertEquals(1, $carona2->fresh()->ordem_parada);
    }
}
