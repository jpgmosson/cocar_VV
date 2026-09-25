<?php

namespace Tests\Feature\Renan;

use App\Enums\TipoUsuario;
use App\Models\GrupoCarona;
use App\Models\Organizacao;
use App\Models\PerfilMotorista;
use App\Models\User;
use Tests\TestCase;

class GrupoCaronaScopeTest extends TestCase
{
    public function test_escopo_global_isola_grupos_de_outras_organizacoes(): void
    {
        $orgA = Organizacao::factory()->create();
        $orgB = Organizacao::factory()->create();

        // Org A
        $motoristaA = User::factory()->create(['organizacao_id' => $orgA->id, 'tipo' => TipoUsuario::PADRAO]);
        $perfilA = PerfilMotorista::factory()->for($motoristaA)->create();
        $grupoOrgA = GrupoCarona::factory()->for($perfilA, 'motorista')->create();

        // Org B
        $motoristaB = User::factory()->create(['organizacao_id' => $orgB->id, 'tipo' => TipoUsuario::PADRAO]);
        $perfilB = PerfilMotorista::factory()->for($motoristaB)->create();
        $grupoOrgB = GrupoCarona::factory()->for($perfilB, 'motorista')->create();

        // Passageiro Org A
        $passageiroOrgA = User::factory()->create(['organizacao_id' => $orgA->id, 'tipo' => TipoUsuario::PADRAO]);
        $this->actingAs($passageiroOrgA);

        $gruposVisiveis = GrupoCarona::all();

        $this->assertTrue($gruposVisiveis->contains('id', $grupoOrgA->id));
        $this->assertFalse($gruposVisiveis->contains('id', $grupoOrgB->id));
    }
}
