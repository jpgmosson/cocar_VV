<?php

namespace Tests\Feature\Eduardo;

use App\Enums\StatusCarona;
use App\Enums\StatusPedidoCarona;
use App\Enums\StatusTrajeto;
use App\Models\Carona;
use App\Models\PedidoCarona;
use App\Models\Trajeto;
use App\Models\User;
use App\Services\TrajetoService;
use App\ValueObjects\Point;
use Tests\TestCase;

class CancelamentoTrajetoCascataTest extends TestCase
{
    public function test_cancelar_trajeto_reverte_pedidos_ativos_e_preserva_historico_de_falha(): void
    {
        $motorista = User::factory()->create();
        $passageiro1 = User::factory()->create();
        $passageiro2 = User::factory()->create();

        $trajeto = Trajeto::create([
            'user_id' => $motorista->id,
            'rota' => '{"type":"LineString","coordinates":[[0.0,0.0],[1.0,1.0]]}',
            'status' => StatusTrajeto::PLANEJADO,
            'origem_coords' => new Point(0.0, 0.0),
            'origem_endereco' => 'Origem',
            'destino_coords' => new Point(1.0, 1.0),
            'destino_endereco' => 'Destino',
        ]);

        $pedido1 = PedidoCarona::create([
            'user_id' => $passageiro1->id,
            'status' => StatusPedidoCarona::ATENDIDO,
            'origem_coords' => new Point(0.0, 0.0),
            'origem_endereco' => 'P1',
            'destino_coords' => new Point(1.0, 1.0),
            'destino_endereco' => 'P1',
        ]);
        $carona1 = Carona::create([
            'trajeto_id' => $trajeto->id,
            'pedido_carona_id' => $pedido1->id,
            'status' => StatusCarona::ACEITA,
        ]);

        $pedido2 = PedidoCarona::create([
            'user_id' => $passageiro2->id,
            'status' => StatusPedidoCarona::CANCELADO,
            'origem_coords' => new Point(0.0, 0.0),
            'origem_endereco' => 'P2',
            'destino_coords' => new Point(1.0, 1.0),
            'destino_endereco' => 'P2',
        ]);
        $carona2 = Carona::create([
            'trajeto_id' => $trajeto->id,
            'pedido_carona_id' => $pedido2->id,
            'status' => StatusCarona::CANCELADA_PASSAGEIRO,
        ]);

        $service = $this->app->make(TrajetoService::class);
        $service->cancelarTrajeto($trajeto);

        $this->assertEquals(StatusTrajeto::CANCELADO, $trajeto->fresh()->status);
        $this->assertEquals(StatusCarona::CANCELADA_MOTORISTA, $carona1->fresh()->status);
        $this->assertEquals(StatusPedidoCarona::PROCURANDO_MOTORISTA, $pedido1->fresh()->status);

        $this->assertEquals(StatusCarona::CANCELADA_PASSAGEIRO, $carona2->fresh()->status);
        $this->assertEquals(StatusPedidoCarona::CANCELADO, $pedido2->fresh()->status);
    }
}
