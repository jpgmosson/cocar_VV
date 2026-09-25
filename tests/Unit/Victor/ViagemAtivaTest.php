<?php

namespace Tests\Unit\Victor;

use App\Enums\StatusCarona;
use App\Enums\StatusPedidoCarona;
use App\Enums\StatusTrajeto;
use App\Models\Carona;
use App\Models\PedidoCarona;
use App\Models\Trajeto;
use App\Models\User;
use App\View\Components\ViagemAtiva;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ViagemAtivaTest extends TestCase
{
    public function test_renderiza_quando_passageiro_tem_pedido_procurando_motorista(): void
    {
        $user = User::factory()->create();
        PedidoCarona::factory()->create([
            'user_id' => $user->id,
            'status' => StatusPedidoCarona::PROCURANDO_MOTORISTA,
        ]);

        Auth::login($user);

        $component = new ViagemAtiva;

        $this->assertTrue($component->shouldRender());
        $this->assertNotNull($component->pedido);
        $this->assertNull($component->trajeto);
    }

    public function test_renderiza_quando_passageiro_tem_carona_ativa_em_andamento(): void
    {
        $user = User::factory()->create();
        $pedido = PedidoCarona::factory()->create([
            'user_id' => $user->id,
            'status' => StatusPedidoCarona::ATENDIDO,
        ]);

        $trajeto = Trajeto::factory()->create([
            'user_id' => User::factory()->create()->id,
            'status' => StatusTrajeto::EM_ANDAMENTO,
        ]);

        Carona::create([
            'trajeto_id' => $trajeto->id,
            'pedido_carona_id' => $pedido->id,
            'status' => StatusCarona::EM_ANDAMENTO,
        ]);

        Auth::login($user);

        $component = new ViagemAtiva;

        $this->assertTrue($component->shouldRender());
        $this->assertNotNull($component->pedido);
    }

    public function test_renderiza_quando_motorista_possui_trajeto_planejado_ou_em_andamento(): void
    {
        $user = User::factory()->create();
        Trajeto::factory()->create([
            'user_id' => $user->id,
            'status' => StatusTrajeto::PLANEJADO,
        ]);

        Auth::login($user);

        $component = new ViagemAtiva;

        $this->assertTrue($component->shouldRender());
        $this->assertNotNull($component->trajeto);
    }

    public function test_nao_renderiza_quando_todas_as_viagens_estao_concluidas_ou_inexistentes(): void
    {
        $user = User::factory()->create();

        Trajeto::factory()->create([
            'user_id' => $user->id,
            'status' => StatusTrajeto::CONCLUIDO,
        ]);

        PedidoCarona::factory()->create([
            'user_id' => $user->id,
            'status' => StatusPedidoCarona::CANCELADO,
        ]);

        Auth::login($user);

        $component = new ViagemAtiva;

        $this->assertFalse($component->shouldRender());
        $this->assertNull($component->pedido);
        $this->assertNull($component->trajeto);
    }
}
