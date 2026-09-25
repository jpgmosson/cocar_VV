<?php

namespace Tests\Feature\Renan;

use App\Enums\TipoUsuario;
use App\Models\Organizacao;
use App\Models\User;
use Tests\TestCase;

class PerfilMotoristaTest extends TestCase
{
    public function test_impede_cadastro_de_motorista_com_cnh_invalida(): void
    {
        $org = Organizacao::factory()->create();
        $usuario = User::factory()->create([
            'organizacao_id' => $org->id,
            'tipo' => TipoUsuario::PADRAO,
        ]);

        $response = $this->actingAs($usuario)->post(route('motorista.cadastro'), [
            'cnh' => '11111111111',
        ]);

        $response->assertSessionHasErrors('cnh');
        $this->assertDatabaseMissing('perfis_motorista', [
            'user_id' => $usuario->id,
        ]);
    }
}
