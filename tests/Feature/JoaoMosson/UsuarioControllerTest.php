<?php

namespace Tests\Feature\JoaoMosson;

use App\Enums\TipoUsuario;
use App\Models\Organizacao;
use App\Models\User;
use Tests\TestCase;

class UsuarioControllerTest extends TestCase
{
    public function test_administrador_de_organizacao_visualiza_apenas_integrantes_da_propria_empresa(): void
    {
        $orgA = Organizacao::create(['nome' => 'Org A', 'cnpj' => '11444777000161', 'dominio_email' => 'orga.com']);
        $orgB = Organizacao::create(['nome' => 'Org B', 'cnpj' => '22555888000192', 'dominio_email' => 'orgb.com']);

        $adminOrgA = User::factory()->create([
            'organizacao_id' => $orgA->id,
            'tipo' => TipoUsuario::ADMINISTRADOR_ORGANIZACAO,
        ]);

        $membroA = User::factory()->create(['name' => 'Funcionario A', 'organizacao_id' => $orgA->id]);
        $membroB = User::factory()->create(['name' => 'Funcionario B', 'organizacao_id' => $orgB->id]);

        $response = $this->actingAs($adminOrgA)->get(route('admin.usuarios'));

        $response->assertOk();
        $response->assertViewHas('users', function ($users) use ($membroA, $membroB, $adminOrgA) {
            return $users->contains($membroA)
                && $users->contains($adminOrgA)
                && ! $users->contains($membroB);
        });
    }

    public function test_administrador_do_sistema_visualiza_usuarios_de_todas_as_organizacoes(): void
    {
        $orgA = Organizacao::create(['nome' => 'Org A', 'cnpj' => '11444777000161', 'dominio_email' => 'orga.com']);
        $orgB = Organizacao::create(['nome' => 'Org B', 'cnpj' => '22555888000192', 'dominio_email' => 'orgb.com']);

        $superAdmin = User::factory()->create(['tipo' => TipoUsuario::ADMINISTRADOR_SISTEMA]);
        $membroA = User::factory()->create(['organizacao_id' => $orgA->id]);
        $membroB = User::factory()->create(['organizacao_id' => $orgB->id]);

        $response = $this->actingAs($superAdmin)->get(route('admin.usuarios'));

        $response->assertOk();
        $response->assertViewHas('users', function ($users) use ($membroA, $membroB, $superAdmin) {
            return $users->contains($membroA)
                && $users->contains($membroB)
                && $users->contains($superAdmin);
        });
    }
}
