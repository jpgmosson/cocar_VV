<?php

namespace Tests\Unit\Victor;

use App\DataTransferObjects\MapRoute;
use App\DataTransferObjects\RouteResult;
use App\DataTransferObjects\RouteWaypoint;
use App\Enums\StatusCarona;
use App\Enums\StatusPedidoCarona;
use App\Enums\StatusTrajeto;
use App\Models\Carona;
use App\Models\PedidoCarona;
use App\Models\Trajeto;
use App\Models\User;
use App\Services\MapApiService;
use App\Services\PagamentoService;
use App\Services\TrajetoService;
use Exception;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class TrajetoServiceTest extends TestCase
{
    protected MockInterface $pagamentoService;

    protected MockInterface $mapApiService;

    protected TrajetoService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pagamentoService = Mockery::mock(PagamentoService::class);
        $this->mapApiService = Mockery::mock(MapApiService::class);

        $this->service = new TrajetoService(
            $this->pagamentoService,
            $this->mapApiService
        );
    }

    public function test_iniciar_trajeto_avanca_status_e_atualiza_apenas_caronas_aceitas(): void
    {
        $trajeto = Trajeto::factory()->create([
            'status' => StatusTrajeto::PLANEJADO,
        ]);

        $pedidoAceito = PedidoCarona::factory()->create(['status' => StatusPedidoCarona::ATENDIDO]);
        $pedidoCancelado = PedidoCarona::factory()->create(['status' => StatusPedidoCarona::ATENDIDO]);

        $caronaAceita = Carona::create([
            'trajeto_id' => $trajeto->id,
            'pedido_carona_id' => $pedidoAceito->id,
            'status' => StatusCarona::ACEITA,
        ]);

        $caronaCancelada = Carona::create([
            'trajeto_id' => $trajeto->id,
            'pedido_carona_id' => $pedidoCancelado->id,
            'status' => StatusCarona::CANCELADA_PASSAGEIRO,
        ]);

        $this->service->iniciarTrajeto($trajeto);

        $trajeto->refresh();
        $this->assertEquals(StatusTrajeto::EM_ANDAMENTO, $trajeto->status);
        $this->assertNotNull($trajeto->horario_inicio);
        $this->assertNotNull($trajeto->localizacao_motorista);

        $this->assertEquals(StatusCarona::MOTORISTA_A_CAMINHO, $caronaAceita->fresh()->status);
        $this->assertEquals(StatusCarona::CANCELADA_PASSAGEIRO, $caronaCancelada->fresh()->status);
    }

    public function test_falha_ao_iniciar_trajeto_em_status_diferente_de_planejado(): void
    {
        $invalidStatuses = [
            StatusTrajeto::EM_ANDAMENTO,
            StatusTrajeto::CONCLUIDO,
            StatusTrajeto::CANCELADO,
        ];

        foreach ($invalidStatuses as $invalidStatus) {
            $trajeto = Trajeto::factory()->create([
                'status' => $invalidStatus,
            ]);

            try {
                $this->service->iniciarTrajeto($trajeto);
                $this->fail("Esperada exceção para status: {$invalidStatus->value}");
            } catch (Exception $e) {
                $this->assertSame('Só é possível iniciar um trajeto planejado.', $e->getMessage());
            }
        }
    }

    public function test_embarque_atualiza_posicao_do_motorista_e_status_da_carona(): void
    {
        $trajeto = Trajeto::factory()->create([
            'status' => StatusTrajeto::EM_ANDAMENTO,
        ]);

        $pedido = PedidoCarona::factory()->create([
            'status' => StatusPedidoCarona::ATENDIDO,
        ]);

        $carona = Carona::create([
            'trajeto_id' => $trajeto->id,
            'pedido_carona_id' => $pedido->id,
            'status' => StatusCarona::MOTORISTA_A_CAMINHO,
        ]);

        $this->service->embarcarPassageiro($trajeto, $carona);

        $carona->refresh();
        $trajeto->refresh();

        $this->assertEquals(StatusCarona::EM_ANDAMENTO, $carona->status);
        $this->assertNotNull($carona->fresh()->getAttribute('horario_embarque'));
        $this->assertNotNull($trajeto->localizacao_motorista);
    }

    public function test_finalizar_trajeto_conclui_caronas_ativas_e_consolida_valores_financeiros(): void
    {
        $motorista = User::factory()->create();
        $trajeto = Trajeto::factory()->create([
            'user_id' => $motorista->id,
            'status' => StatusTrajeto::EM_ANDAMENTO,
        ]);

        $pedidoAtivo = PedidoCarona::factory()->create(['status' => StatusPedidoCarona::ATENDIDO]);
        $pedidoAusente = PedidoCarona::factory()->create(['status' => StatusPedidoCarona::ATENDIDO]);

        $caronaEmAndamento = Carona::create([
            'trajeto_id' => $trajeto->id,
            'pedido_carona_id' => $pedidoAtivo->id,
            'status' => StatusCarona::EM_ANDAMENTO,
        ]);

        $caronaAusente = Carona::create([
            'trajeto_id' => $trajeto->id,
            'pedido_carona_id' => $pedidoAusente->id,
            'status' => StatusCarona::PASSAGEIRO_AUSENTE,
        ]);

        $valorArrecadado = fake()->randomFloat(2, 10, 100);

        $this->pagamentoService
            ->shouldReceive('consolidarTransacoes')
            ->once()
            ->with($trajeto->id)
            ->andReturn($valorArrecadado);

        $this->pagamentoService
            ->shouldReceive('depositarAjudaCusto')
            ->once()
            ->with($motorista->id, $trajeto->id, $valorArrecadado);

        $this->service->finalizarTrajeto($trajeto->id);

        $trajeto->refresh();
        $this->assertEquals(StatusTrajeto::CONCLUIDO, $trajeto->status);
        $this->assertNotNull($trajeto->horario_fim);

        $this->assertEquals(StatusCarona::CONCLUIDA, $caronaEmAndamento->fresh()->status);
        $this->assertNotNull($caronaEmAndamento->fresh()->getAttribute('horario_desembarque'));
        $this->assertEquals(StatusCarona::PASSAGEIRO_AUSENTE, $caronaAusente->fresh()->status);
    }

    public function test_cancelar_carona_reverte_pedido_e_recalcula_rota(): void
    {
        $trajeto = Trajeto::factory()->create([
            'status' => StatusTrajeto::EM_ANDAMENTO,
        ]);

        $pedido = PedidoCarona::factory()->create([
            'status' => StatusPedidoCarona::ATENDIDO,
        ]);

        $carona = Carona::create([
            'trajeto_id' => $trajeto->id,
            'pedido_carona_id' => $pedido->id,
            'status' => StatusCarona::MOTORISTA_A_CAMINHO,
        ]);

        $mockRouteResult = new RouteResult(
            routes: [
                new MapRoute(
                    json_encode([
                        'type' => 'LineString',
                        'coordinates' => [[0.0, 0.0], [1.0, 1.0]],
                    ]),
                    1000.0,
                    60.0
                ),
            ],
            waypoints: [
                new RouteWaypoint(index: 0, longitude: 0.0, latitude: 0.0),
                new RouteWaypoint(index: 1, longitude: 1.0, latitude: 1.0),
            ]
        );

        $this->mapApiService
            ->shouldReceive('obterRotaOtimizada')
            ->once()
            ->andReturn($mockRouteResult);

        $this->service->cancelarCarona($carona);

        $this->assertEquals(StatusCarona::CANCELADA_MOTORISTA, $carona->fresh()->status);
        $this->assertEquals(StatusPedidoCarona::PROCURANDO_MOTORISTA, $pedido->fresh()->status);
    }

    public function test_cancelar_trajeto_reverte_pedidos_e_cancela_todas_caronas_ativas(): void
    {
        $trajeto = Trajeto::factory()->create([
            'status' => StatusTrajeto::PLANEJADO,
        ]);

        $pedidoAtivo1 = PedidoCarona::factory()->create(['status' => StatusPedidoCarona::ATENDIDO]);
        $pedidoAtivo2 = PedidoCarona::factory()->create(['status' => StatusPedidoCarona::ATENDIDO]);
        $pedidoJaCancelado = PedidoCarona::factory()->create(['status' => StatusPedidoCarona::CANCELADO]);

        $carona1 = Carona::create([
            'trajeto_id' => $trajeto->id,
            'pedido_carona_id' => $pedidoAtivo1->id,
            'status' => StatusCarona::ACEITA,
        ]);

        $carona2 = Carona::create([
            'trajeto_id' => $trajeto->id,
            'pedido_carona_id' => $pedidoAtivo2->id,
            'status' => StatusCarona::EM_ANDAMENTO,
        ]);

        $caronaConcluida = Carona::create([
            'trajeto_id' => $trajeto->id,
            'pedido_carona_id' => $pedidoJaCancelado->id,
            'status' => StatusCarona::CONCLUIDA,
        ]);

        $this->service->cancelarTrajeto($trajeto);

        $trajeto->refresh();
        $this->assertEquals(StatusTrajeto::CANCELADO, $trajeto->status);

        $this->assertEquals(StatusCarona::CANCELADA_MOTORISTA, $carona1->fresh()->status);
        $this->assertEquals(StatusCarona::CANCELADA_MOTORISTA, $carona2->fresh()->status);
        $this->assertEquals(StatusCarona::CONCLUIDA, $caronaConcluida->fresh()->status);

        $this->assertEquals(StatusPedidoCarona::PROCURANDO_MOTORISTA, $pedidoAtivo1->fresh()->status);
        $this->assertEquals(StatusPedidoCarona::PROCURANDO_MOTORISTA, $pedidoAtivo2->fresh()->status);
        $this->assertEquals(StatusPedidoCarona::CANCELADO, $pedidoJaCancelado->fresh()->status);
    }

    public function test_atender_pedido_cria_carona_e_atualiza_status_do_pedido(): void
    {
        $trajeto = Trajeto::factory()->create([
            'status' => StatusTrajeto::PLANEJADO,
        ]);

        $pedido = PedidoCarona::factory()->create([
            'status' => StatusPedidoCarona::PROCURANDO_MOTORISTA,
        ]);

        $mockRouteResult = new RouteResult(
            routes: [
                new MapRoute(
                    json_encode([
                        'type' => 'LineString',
                        'coordinates' => [[0.0, 0.0], [1.0, 1.0]],
                    ]),
                    1000.0,
                    60.0
                ),
            ],
            waypoints: [
                new RouteWaypoint(index: 0, longitude: 0.0, latitude: 0.0),
                new RouteWaypoint(index: 1, longitude: 1.0, latitude: 1.0),
            ]
        );

        $this->mapApiService
            ->shouldReceive('obterRotaOtimizada')
            ->once()
            ->andReturn($mockRouteResult);

        $carona = $this->service->atenderPedidoCarona($trajeto, $pedido);

        $this->assertInstanceOf(Carona::class, $carona);
        $this->assertEquals($trajeto->id, $carona->trajeto_id);
        $this->assertEquals($pedido->id, $carona->pedido_carona_id);
        $this->assertEquals(StatusPedidoCarona::ATENDIDO, $pedido->fresh()->status);
    }
}
