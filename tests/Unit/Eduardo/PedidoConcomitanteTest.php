<?php

namespace Tests\Unit\Eduardo;

use App\Exceptions\ExibivelException;
use App\Services\MapApiService;
use App\Services\PagamentoService;
use App\Services\PedidoCaronaService;
use App\ValueObjects\Point;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class PedidoConcomitanteTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_impede_criacao_de_pedido_se_usuario_ja_possui_carona_ativa(): void
    {
        $mapApiMock = Mockery::mock(MapApiService::class);
        $pagamentoMock = Mockery::mock(PagamentoService::class);

        /** @var PedidoCaronaService|Mockery\MockInterface $service */
        $service = Mockery::mock(PedidoCaronaService::class, [$mapApiMock, $pagamentoMock])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $service->shouldReceive('temCaronaAtiva')
            ->once()
            ->with(10)
            ->andReturn(true);

        $this->expectException(ExibivelException::class);

        $service->novoPedido([
            'origem_coords' => new Point(10.0, 20.0),
            'destino_coords' => new Point(10.1, 20.1),
        ], 10);
    }
}
