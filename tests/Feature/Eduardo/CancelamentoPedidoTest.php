<?php

namespace Tests\Feature\Eduardo;

use App\Enums\StatusCarona;
use App\Enums\StatusPedidoCarona;
use App\Enums\StatusTransacao;
use App\Enums\TipoTransacao;
use App\Models\Carona;
use App\Models\Carteira;
use App\Models\PedidoCarona;
use App\Models\Trajeto;
use App\Models\Transacao;
use App\Models\User;
use App\Services\PedidoCaronaService;
use App\ValueObjects\Point;
use Tests\TestCase;

class CancelamentoPedidoTest extends TestCase
{
    public function test_cancelamento_de_pedido_atualiza_status_e_estorna_retencao(): void
    {
        $passageiro = User::factory()->create();
        $motorista = User::factory()->create();

        Carteira::forceCreate(['user_id' => $passageiro->id, 'saldo' => '10.00']);

        $pedido = PedidoCarona::create([
            'user_id' => $passageiro->id,
            'origem_coords' => new Point(0.0, 0.0),
            'origem_endereco' => 'Origem',
            'destino_coords' => new Point(1.0, 1.0),
            'destino_endereco' => 'Destino',
            'status' => StatusPedidoCarona::ATENDIDO,
        ]);

        $trajeto = Trajeto::create([
            'user_id' => $motorista->id,
            'rota' => '{"type":"LineString","coordinates":[[0.0,0.0],[1.0,1.0]]}',
            'origem_coords' => new Point(0.0, 0.0),
            'origem_endereco' => 'Origem',
            'destino_coords' => new Point(1.0, 1.0),
            'destino_endereco' => 'Destino',
        ]);

        $carona = Carona::create([
            'trajeto_id' => $trajeto->id,
            'pedido_carona_id' => $pedido->id,
            'status' => StatusCarona::MOTORISTA_A_CAMINHO,
        ]);

        Transacao::create([
            'user_id' => $passageiro->id,
            'pedido_carona_id' => $pedido->id,
            'tipo' => TipoTransacao::RETENCAO,
            'status' => StatusTransacao::SUCESSO,
            'valor' => '30.00',
        ]);

        $service = $this->app->make(PedidoCaronaService::class);
        $service->cancelarPedido($pedido);

        $this->assertEquals(StatusPedidoCarona::CANCELADO, $pedido->fresh()->status);
        $this->assertEquals(StatusCarona::CANCELADA_PASSAGEIRO, $carona->fresh()->status);
        $this->assertEquals('40.00', Carteira::where('user_id', $passageiro->id)->value('saldo'));

        $this->assertDatabaseHas('transacoes', [
            'user_id' => $passageiro->id,
            'pedido_carona_id' => $pedido->id,
            'tipo' => TipoTransacao::ESTORNO,
            'status' => StatusTransacao::SUCESSO,
            'valor' => '30.00',
        ]);
    }
}
