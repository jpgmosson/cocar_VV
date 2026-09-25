<?php

namespace Tests\Feature\Renan;

use App\Enums\TipoUsuario;
use App\Models\GrupoCarona;
use App\Models\Organizacao;
use App\Models\PerfilMotorista;
use App\Models\User;
use Tests\TestCase;

class GrupoCaronaTest extends TestCase
{
    private function criarMotorista(int $organizacaoId): User
    {
        $user = User::factory()->create([
            'organizacao_id' => $organizacaoId,
            'tipo' => TipoUsuario::PADRAO,
        ]);

        PerfilMotorista::factory()->for($user)->create();

        return $user;
    }

    public function test_criacao_de_grupo_aplica_validacao_condicional_e_limita_vagas(): void
    {
        $org = Organizacao::factory()->create();
        $motorista = $this->criarMotorista($org->id);

        $respostaInvalida = $this->actingAs($motorista)->post(route('motorista.grupos.store'), [
            'nome' => 'Carona Centro',
            'frequencia' => 'semanal',
            'dias_semana' => ['seg', 'qua'],
            'vagas' => 5,
        ]);
        $respostaInvalida->assertSessionHasErrors('vagas');

        $respostaValida = $this->actingAs($motorista)->post(route('motorista.grupos.store'), [
            'nome' => 'Carona Centro',
            'frequencia' => 'semanal',
            'dias_semana' => ['seg', 'qui'],
            'dias_mes' => [10, 20],
            'vagas' => 4,
        ]);

        $respostaValida->assertRedirect(route('motorista.home'));
        $this->assertDatabaseHas('grupos_carona', [
            'nome' => 'Carona Centro',
            'frequencia' => 'semanal',
            'dias_mes' => null,
            'vagas' => 4,
        ]);
    }

    public function test_impede_selecao_de_passageiros_acima_das_vagas_ou_de_outra_organizacao(): void
    {
        $orgA = Organizacao::factory()->create();
        $orgB = Organizacao::factory()->create();

        $motorista = $this->criarMotorista($orgA->id);
        $passageiro1 = User::factory()->create(['organizacao_id' => $orgA->id, 'tipo' => TipoUsuario::PADRAO]);
        $passageiro2 = User::factory()->create(['organizacao_id' => $orgA->id, 'tipo' => TipoUsuario::PADRAO]);
        $passageiroOrgB = User::factory()->create(['organizacao_id' => $orgB->id, 'tipo' => TipoUsuario::PADRAO]);

        $this->actingAs($motorista)->post(route('motorista.grupos.store'), [
            'nome' => 'Carona Fixa',
            'frequencia' => 'semanal',
            'dias_semana' => ['seg'],
            'vagas' => 2,
            'passageiros' => [$passageiroOrgB->id],
        ])->assertSessionHasErrors('passageiros.0');

        $this->actingAs($motorista)->post(route('motorista.grupos.store'), [
            'nome' => 'Carona Fixa',
            'frequencia' => 'semanal',
            'dias_semana' => ['seg'],
            'vagas' => 1,
            'passageiros' => [$passageiro1->id, $passageiro2->id],
        ])->assertSessionHasErrors('passageiros');
    }

    public function test_impede_entrada_em_grupo_lotado_e_duplicidade_de_passageiro(): void
    {
        $org = Organizacao::factory()->create();
        $motorista = $this->criarMotorista($org->id);

        $grupo = GrupoCarona::factory()
            ->for($motorista->perfilMotorista, 'motorista')
            ->create(['vagas' => 1]);

        $passageiro1 = User::factory()->create(['organizacao_id' => $org->id, 'tipo' => TipoUsuario::PADRAO]);
        $passageiro2 = User::factory()->create(['organizacao_id' => $org->id, 'tipo' => TipoUsuario::PADRAO]);

        $this->actingAs($passageiro1)->post(route('grupos.entrar', $grupo))
            ->assertSessionHas('sucesso');

        $this->actingAs($passageiro1)->post(route('grupos.entrar', $grupo))
            ->assertSessionHas('erro', 'Você já está neste grupo.');

        $this->actingAs($passageiro2)->post(route('grupos.entrar', $grupo))
            ->assertSessionHas('erro', 'Este grupo já está lotado.');

        $this->assertEquals(1, $grupo->passageiros()->count());
    }

    public function test_passageiro_consegue_sair_e_somente_dono_pode_excluir_o_grupo(): void
    {
        $org = Organizacao::factory()->create();
        $motoristaDono = $this->criarMotorista($org->id);
        $outroMotorista = $this->criarMotorista($org->id);

        $grupo = GrupoCarona::factory()
            ->for($motoristaDono->perfilMotorista, 'motorista')
            ->create(['vagas' => 2]);

        $passageiro = User::factory()->create(['organizacao_id' => $org->id, 'tipo' => TipoUsuario::PADRAO]);
        $grupo->passageiros()->attach($passageiro->id);

        $this->actingAs($passageiro)->delete(route('grupos.sair', $grupo))
            ->assertSessionHas('sucesso', 'Você saiu do grupo de carona com sucesso!');

        $this->assertDatabaseMissing('grupo_carona_user', [
            'grupo_carona_id' => $grupo->id,
            'user_id' => $passageiro->id,
        ]);

        $this->actingAs($outroMotorista)->delete(route('motorista.grupos.destroy', $grupo))
            ->assertSessionHas('erro', 'Acesso não autorizado para exclusão deste grupo.');

        $this->assertDatabaseHas('grupos_carona', ['id' => $grupo->id]);

        $this->actingAs($motoristaDono)->delete(route('motorista.grupos.destroy', $grupo))
            ->assertSessionHas('sucesso', 'Grupo de carona excluído com sucesso.');

        $this->assertDatabaseMissing('grupos_carona', ['id' => $grupo->id]);
    }
}
