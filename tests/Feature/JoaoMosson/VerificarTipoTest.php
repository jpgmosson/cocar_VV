<?php

namespace Tests\Feature\JoaoMosson;

use App\Enums\TipoUsuario;
use App\Http\Middleware\VerificarTipo;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class VerificarTipoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/area-restrita-admin', fn () => response('autorizado', 200))
            ->middleware(['web', VerificarTipo::sendo(TipoUsuario::ADMINISTRADOR_ORGANIZACAO, TipoUsuario::ADMINISTRADOR_SISTEMA)]);
    }

    public function test_bloqueia_usuario_padrao_e_redireciona_com_mensagem_flash(): void
    {
        $userPadrao = User::factory()->make([
            'tipo' => TipoUsuario::PADRAO,
        ]);

        $response = $this->actingAs($userPadrao)->get('/area-restrita-admin');

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error', 'Acesso negado. Você não tem permissão para acessar esta área.');
    }

    public function test_permite_acesso_para_usuarios_com_os_papeis_configurados(): void
    {
        $adminOrg = User::factory()->make([
            'tipo' => TipoUsuario::ADMINISTRADOR_ORGANIZACAO,
        ]);

        $response = $this->actingAs($adminOrg)->get('/area-restrita-admin');
        $response->assertOk();
        $this->assertEquals('autorizado', $response->getContent());
    }
}
