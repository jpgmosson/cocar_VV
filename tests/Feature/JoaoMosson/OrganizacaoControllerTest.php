<?php

namespace Tests\Feature\JoaoMosson;

use App\Enums\TipoUsuario;
use App\Models\Organizacao;
use App\Models\User;
use Tests\TestCase;

class OrganizacaoControllerTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'tipo' => TipoUsuario::ADMINISTRADOR_SISTEMA,
        ]);
    }

    public function test_lista_organizacoes_com_contagem_de_integrantes_e_filtro_por_termo(): void
    {
        $orgAlvo = Organizacao::create([
            'nome' => 'Alpha Motors',
            'cnpj' => '11444777000161',
            'dominio_email' => 'alpha.com',
        ]);
        $orgOutra = Organizacao::create([
            'nome' => 'Beta Tech',
            'cnpj' => '22555888000192',
            'dominio_email' => 'beta.com',
        ]);

        User::factory()->count(3)->create(['organizacao_id' => $orgAlvo->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.organizacoes', ['pesquisa' => 'alpha']));

        $response->assertOk();
        $response->assertViewHas('organizacoes', function ($orgs) use ($orgAlvo, $orgOutra) {
            $primeira = $orgs->first();

            return $orgs->count() === 1
                && $primeira->id === $orgAlvo->id
                && $primeira->integrantes_count === 3
                && ! $orgs->contains($orgOutra);
        });
    }

    public function test_atualiza_o_nome_da_organizacao_com_sucesso(): void
    {
        $org = Organizacao::create([
            'nome' => 'Antigo Nome',
            'cnpj' => '11444777000161',
            'dominio_email' => 'antigo.com',
        ]);

        $response = $this->actingAs($this->admin)
            ->from(route('admin.organizacoes'))
            ->put(route('organizacoes.alterar', $org), [
                'nome' => 'Novo Nome Atualizado',
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.organizacoes'));

        $this->assertDatabaseHas('organizacoes', [
            'id' => $org->id,
            'nome' => 'Novo Nome Atualizado',
        ]);
    }
}
