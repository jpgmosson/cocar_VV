<?php

namespace Tests\Feature\Renan;

use App\Enums\TipoUsuario;
use App\Models\Organizacao;
use App\Models\PerfilMotorista;
use App\Models\User;
use Tests\TestCase;

class TriagemMotoristaTest extends TestCase
{
    public function test_administrador_nao_pode_aprovar_motorista_de_outra_organizacao(): void
    {
        $orgA = Organizacao::factory()->create();
        $orgB = Organizacao::factory()->create();

        $adminOrgA = User::factory()->create([
            'organizacao_id' => $orgA->id,
            'tipo' => TipoUsuario::ADMINISTRADOR_ORGANIZACAO,
        ]);

        $userMotoristaB = User::factory()->create([
            'organizacao_id' => $orgB->id,
            'tipo' => TipoUsuario::PADRAO,
        ]);

        $perfilMotoristaOrgB = PerfilMotorista::factory()
            ->pendente()
            ->for($userMotoristaB)
            ->create();

        $response = $this->actingAs($adminOrgA)->post(
            route('triagem-motoristas.aprovar', $perfilMotoristaOrgB)
        );

        $response->assertForbidden();
        $this->assertNull($perfilMotoristaOrgB->fresh()->aprovado_em);
    }

    public function test_rejeicao_de_motorista_remove_o_perfil_do_banco(): void
    {
        $orgA = Organizacao::factory()->create();

        $adminOrgA = User::factory()->create([
            'organizacao_id' => $orgA->id,
            'tipo' => TipoUsuario::ADMINISTRADOR_ORGANIZACAO,
        ]);

        $userMotoristaA = User::factory()->create([
            'organizacao_id' => $orgA->id,
            'tipo' => TipoUsuario::PADRAO,
        ]);

        $perfilMotorista = PerfilMotorista::factory()
            ->pendente()
            ->for($userMotoristaA)
            ->create();

        $response = $this->actingAs($adminOrgA)->post(
            route('triagem-motoristas.rejeitar', $perfilMotorista)
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('perfis_motorista', [
            'id' => $perfilMotorista->id,
        ]);
    }
}
