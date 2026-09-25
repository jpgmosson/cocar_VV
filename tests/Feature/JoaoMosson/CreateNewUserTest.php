<?php

namespace Tests\Feature\JoaoMosson;

use App\Actions\Fortify\CreateNewUser;
use App\Enums\TipoUsuario;
use App\Models\Organizacao;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateNewUserTest extends TestCase
{
    private CreateNewUser $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new CreateNewUser;
    }

    public function test_impede_cadastro_quando_o_dominio_do_email_nao_pertence_a_nenhuma_organizacao(): void
    {
        $dados = [
            'name' => 'João Silva',
            'email' => 'joao@desconhecido.com.br',
            'password' => 'SenhaForte123!',
            'password_confirmation' => 'SenhaForte123!',
            'cpf' => '52998224725',
        ];

        $this->expectException(ValidationException::class);

        try {
            $this->action->create($dados);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
            $this->assertEquals(
                'O domínio do seu e-mail não está cadastrado com nenhuma organização parceira.',
                $e->errors()['email'][0]
            );
            $this->assertDatabaseMissing('users', ['email' => 'joao@desconhecido.com.br']);
            throw $e;
        }
    }

    public function test_cadastra_usuario_com_sucesso_vincula_a_organizacao_e_cria_carteira(): void
    {
        $organizacao = Organizacao::create([
            'nome' => 'Acme Corp',
            'cnpj' => '11444777000161',
            'dominio_email' => 'acme.com',
        ]);

        $dados = [
            'name' => 'Maria Souza',
            'email' => 'maria@acme.com',
            'password' => 'SenhaForte123!',
            'password_confirmation' => 'SenhaForte123!',
            'cpf' => '52998224725',
        ];

        $user = $this->action->create($dados);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(TipoUsuario::PADRAO, $user->tipo);
        $this->assertEquals($organizacao->id, $user->organizacao_id);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'maria@acme.com',
            'organizacao_id' => $organizacao->id,
            'tipo' => TipoUsuario::PADRAO->value,
        ]);

        // Valida se a carteira associada foi persistida
        $this->assertNotNull($user->carteira);
        $this->assertDatabaseHas('carteiras', [
            'user_id' => $user->id,
        ]);
    }
}
