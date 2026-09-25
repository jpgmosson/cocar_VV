<?php

namespace Tests\Feature\Victor;

use App\Enums\StatusTrajeto;
use App\Enums\TipoUsuario;
use App\Models\Carona;
use App\Models\PedidoCarona;
use App\Models\Trajeto;
use App\Models\User;
use App\Services\TrajetoService;
use Mockery;
use Tests\TestCase;

class TrajetoControllerTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'tipo' => TipoUsuario::PADRAO,
        ]);
    }

    public function test_endpoint_iniciar_executa_servico_e_retorna_hx_refresh(): void
    {
        $trajeto = Trajeto::factory()->create([
            'user_id' => $this->user->id,
            'status' => StatusTrajeto::PLANEJADO,
        ]);

        $mockService = Mockery::mock(TrajetoService::class);
        $mockService->shouldReceive('iniciarTrajeto')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg->id === $trajeto->id));

        $this->app->instance(TrajetoService::class, $mockService);

        $response = $this->actingAs($this->user)
            ->post("/trajeto/{$trajeto->id}/iniciar");

        $response->assertStatus(200);
        $response->assertHeader('Hx-Refresh', 'true');
    }

    public function test_endpoint_embarcar_aciona_servico_e_retorna_trigger_htmx(): void
    {
        $trajeto = Trajeto::factory()->create([
            'user_id' => $this->user->id,
            'status' => StatusTrajeto::EM_ANDAMENTO,
        ]);

        $pedido = PedidoCarona::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $carona = Carona::create([
            'trajeto_id' => $trajeto->id,
            'pedido_carona_id' => $pedido->id,
        ]);

        $mockService = Mockery::mock(TrajetoService::class);
        $mockService->shouldReceive('embarcarPassageiro')
            ->once()
            ->with(
                Mockery::on(fn ($t) => $t->id === $trajeto->id),
                Mockery::on(fn ($c) => $c->id === $carona->id)
            );

        $this->app->instance(TrajetoService::class, $mockService);

        $response = $this->actingAs($this->user)
            ->post("/trajeto/{$trajeto->id}/embarcar/{$carona->id}");

        $response->assertStatus(200);
        $response->assertHeader('Hx-Trigger', 'atualizarRota');
    }
}
