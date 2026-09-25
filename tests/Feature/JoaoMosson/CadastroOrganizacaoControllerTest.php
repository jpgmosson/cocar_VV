<?php

namespace Tests\Feature\JoaoMosson;

use App\Enums\TipoUsuario;
use App\Models\Organizacao;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class CadastroOrganizacaoControllerTest extends TestCase
{
    public function test_cria_organizacao_e_administrador_em_transacao_atomica_e_realiza_login(): void
    {
        $payload = [
            'organizacao-nome' => 'Tech Solutions',
            'cnpj' => '11444777000161',
            'dominio_email' => 'techsolutions.com',
            'administrador-nome' => 'Admin Tech',
            'administrador-email' => 'admin@techsolutions.com',
            'administrador-cpf' => '52998224725',
            'password' => 'Segredo123#',
            'password_confirmation' => 'Segredo123#',
        ];

        $response = $this->post(route('organizacao.criar'), $payload);

        $response->assertRedirect(route('admin.painel'));

        $this->assertDatabaseHas('organizacoes', [
            'nome' => 'Tech Solutions',
            'cnpj' => '11444777000161',
            'dominio_email' => 'techsolutions.com',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@techsolutions.com',
            'tipo' => TipoUsuario::ADMINISTRADOR_ORGANIZACAO->value,
        ]);

        $admin = User::where('email', 'admin@techsolutions.com')->first();
        $this->assertTrue(Auth::check());
        $this->assertEquals(Auth::id(), $admin->id);
        $this->assertEquals($admin->organizacao_id, Organizacao::where('cnpj', '11444777000161')->first()->id);
    }

    public function test_reverte_criacao_da_organizacao_se_houver_falha_ao_criar_o_usuario(): void
    {
        // Força erro interceptando o momento da criação do usuário ou disparando exceção no evento
        User::saving(function () {
            throw new \Exception('Erro simulado de banco na criação do integrante.');
        });

        $payload = [
            'organizacao-nome' => 'Falha Corp',
            'cnpj' => '11444777000161',
            'dominio_email' => 'falhacorp.com',
            'administrador-nome' => 'Carlos',
            'administrador-email' => 'carlos@falhacorp.com',
            'administrador-cpf' => '52998224725',
            'password' => 'Segredo123#',
            'password_confirmation' => 'Segredo123#',
        ];

        try {
            $this->withoutExceptionHandling()->post(route('organizacao.criar'), $payload);
        } catch (\Exception $e) {
            $this->assertEquals('Erro simulado de banco na criação do integrante.', $e->getMessage());
        }

        $this->assertDatabaseMissing('organizacoes', ['cnpj' => '11444777000161']);
        $this->assertDatabaseMissing('users', ['email' => 'carlos@falhacorp.com']);
        $this->assertFalse(Auth::check());
    }
}
