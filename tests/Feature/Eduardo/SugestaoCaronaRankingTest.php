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
    // 1 grau de latitude = 111.320 metros
    private const METROS_POR_GRAU = 111_320.0;

    private const TOTAL_PEDIDOS_CRIADOS = 30;

    private const LIMITE_MAXIMO_ESPERADO = 24;

    private const DISTANCIA_PASSAGEIROS_METROS = 100; // Incrementa 100m a cada passageiro

    private function offsetMetros(float $metros): float
    {
        return $metros / self::METROS_POR_GRAU;
    }

    public function test_sugestoes_respeitam_limite_de_24_e_ordenacao_ascendente_de_distancia(): void
    {
        $org = Organizacao::create([
            'nome' => 'Org Limite',
            'cnpj' => '33333333000133',
            'dominio_email' => 'limite.com',
        ]);

        $motorista = User::factory()->create(['organizacao_id' => $org->id]);

        $trajeto = Trajeto::create([
            'user_id' => $motorista->id,
            'origem_coords' => new Point(0.0, 0.0),
            'origem_endereco' => 'Ponto de Partida',
            'destino_coords' => new Point(1.0, 0.0),
            'destino_endereco' => 'Ponto Final',
            'rota' => json_encode([
                'type' => 'LineString',
                'coordinates' => [[0.0, 0.0], [1.0, 0.0]],
            ]),
        ]);

        // Cria 30 passageiros espaçados de 100m em 100m da rota (100m a 3.000m)
        for ($i = 1; $i <= self::TOTAL_PEDIDOS_CRIADOS; $i++) {
            $distanciaMetros = $i * self::DISTANCIA_PASSAGEIROS_METROS;
            $passageiro = User::factory()->create(['organizacao_id' => $org->id]);

            PedidoCarona::create([
                'user_id' => $passageiro->id,
                'origem_coords' => new Point(0.5, $this->offsetMetros($distanciaMetros)),
                'origem_endereco' => "Embarque a {$distanciaMetros}m da rota",
                'destino_coords' => new Point(1.0, 0.0), // Destino idêntico ao do motorista
                'destino_endereco' => 'Ponto Final',
                'status' => StatusPedidoCarona::PROCURANDO_MOTORISTA,
            ]);
        }

        $service = new SugestaoCaronaService;
        $sugestoes = $service->obterSugestoesParaTrajeto($trajeto->id);

        $this->assertCount(self::LIMITE_MAXIMO_ESPERADO, $sugestoes);

        $distanciasObtidas = $sugestoes->pluck('desvio_metros')->map(fn ($d) => (float) $d)->values()->all();

        $distanciasEsperadas = $distanciasObtidas;
        sort($distanciasEsperadas);

        $this->assertSame($distanciasEsperadas, $distanciasObtidas);

        $this->assertEqualsWithDelta(100, $distanciasObtidas[0], 10);
        $this->assertEqualsWithDelta(2400, end($distanciasObtidas), 50);
    }
}
