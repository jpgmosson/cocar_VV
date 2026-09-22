<?php

namespace Tests\Feature\Eduardo;

use App\DataTransferObjects\MapRoute;
use App\DataTransferObjects\RouteResult;
use App\Enums\StatusTransacao;
use App\Enums\TipoTransacao;
use App\Exceptions\ExibivelException;
use App\Models\Carteira;
use App\Models\User;
use App\Services\MapApiService;
use App\Services\PedidoCaronaService;
use App\ValueObjects\Point;
use Mockery;
use Tests\TestCase;

class PedidoCaronaRetencaoTest extends TestCase
{
    public function test_lanca_excecao_e_nao_grava_registros_quando_saldo_insuficiente(): void
    {
        $user = User::factory()->create();
        Carteira::forceCreate(['user_id' => $user->id, 'saldo' => '40.00']);

        $mapApiMock = Mockery::mock(MapApiService::class);
        // 15 km = 15.000m -> Max = 15 * 3 = R$ 45.00
        $mapApiMock->shouldReceive('obterRotaDireta')
            ->andReturn(new RouteResult([new MapRoute('geom', 15000.0, 1200.0)]));
        $this->app->instance(MapApiService::class, $mapApiMock);

        $service = $this->app->make(PedidoCaronaService::class);

        $this->expectException(ExibivelException::class);

        try {
            $service->novoPedido([
                'origem_coords' => new Point(0.0, 0.0),
                'origem_endereco' => 'Origem',
                'destino_coords' => new Point(1.0, 1.0),
                'destino_endereco' => 'Destino',
            ], $user->id);
        } finally {
            $this->assertDatabaseMissing('pedidos_carona', ['user_id' => $user->id]);
            $this->assertDatabaseMissing('transacoes', ['user_id' => $user->id]);
            $this->assertEquals('40.00', Carteira::where('user_id', $user->id)->value('saldo'));
        }
    }

    public function test_retém_saldo_maximo_com_sucesso_quando_saldo_suficiente(): void
    {
        $user = User::factory()->create();
        Carteira::forceCreate(['user_id' => $user->id, 'saldo' => '50.00']);

        $mapApiMock = Mockery::mock(MapApiService::class);
        $mapApiMock->shouldReceive('obterRotaDireta')
            ->andReturn(new RouteResult([new MapRoute('geom', 15000.0, 1200.0)]));
        $this->app->instance(MapApiService::class, $mapApiMock);

        $service = $this->app->make(PedidoCaronaService::class);

        $pedido = $service->novoPedido([
            'origem_coords' => new Point(0.0, 0.0),
            'origem_endereco' => 'Origem',
            'destino_coords' => new Point(1.0, 1.0),
            'destino_endereco' => 'Destino',
        ], $user->id);

        $this->assertDatabaseHas('pedidos_carona', ['id' => $pedido->id]);
        $this->assertEquals('5.00', Carteira::where('user_id', $user->id)->value('saldo'));
        $this->assertDatabaseHas('transacoes', [
            'user_id' => $user->id,
            'pedido_carona_id' => $pedido->id,
            'tipo' => TipoTransacao::RETENCAO,
            'status' => StatusTransacao::SUCESSO,
            'valor' => '45.00',
        ]);
    }
}
