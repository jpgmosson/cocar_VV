<?php

use App\Enums\TipoUsuario;
use App\Models\Organizacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function criarUsuarioDaOrganizacao(Organizacao $organizacao, array $atributos = []): User
{
    return User::factory()->create(array_merge([
        'organizacao_id' => $organizacao->id,
        'tipo' => TipoUsuario::PADRAO,
        'cpf' => fake()->numerify('###########'),
    ], $atributos));
}

test('motorista aprovado visualiza apenas passageiros elegiveis da mesma organizacao', function () {
    $organizacao = Organizacao::create([
        'nome' => 'Empresa Teste',
        'cnpj' => '12345678000199',
        'dominio_email' => 'empresa.com',
    ]);

    $motorista = criarUsuarioDaOrganizacao($organizacao, ['email' => 'motorista@empresa.com']);
    $motorista->perfilMotorista()->create([
        'cnh' => '12345678901',
        'aprovado_em' => now(),
    ]);

    $passageiroElegivel = criarUsuarioDaOrganizacao($organizacao, [
        'name' => 'Passageiro Elegivel',
        'email' => 'passageiro1@empresa.com',
    ]);

    $adminDaOrganizacao = criarUsuarioDaOrganizacao($organizacao, [
        'name' => 'Admin da Organizacao',
        'email' => 'admin@empresa.com',
        'tipo' => TipoUsuario::ADMINISTRADOR_ORGANIZACAO,
    ]);

    $motoristaDaMesmaOrganizacao = criarUsuarioDaOrganizacao($organizacao, [
        'name' => 'Outro Motorista',
        'email' => 'motorista2@empresa.com',
    ]);
    $motoristaDaMesmaOrganizacao->perfilMotorista()->create([
        'cnh' => '10987654321',
        'aprovado_em' => now(),
    ]);

    $outraOrganizacao = Organizacao::create([
        'nome' => 'Outra Empresa',
        'cnpj' => '99999999000199',
        'dominio_email' => 'outra.com',
    ]);

    $passageiroDeOutraOrganizacao = criarUsuarioDaOrganizacao($outraOrganizacao, [
        'name' => 'Passageiro Externo',
        'email' => 'externo@outra.com',
    ]);

    $response = $this->actingAs($motorista)->get(route('motorista.grupos.criar'));

    $response->assertOk();
    $response->assertSee($passageiroElegivel->name);
    $response->assertDontSee($adminDaOrganizacao->name);
    $response->assertDontSee($motoristaDaMesmaOrganizacao->name);
    $response->assertDontSee($passageiroDeOutraOrganizacao->name);
});

test('motorista aprovado cria grupo e vincula passageiros selecionados', function () {
    $organizacao = Organizacao::create([
        'nome' => 'Empresa Teste',
        'cnpj' => '12345678000199',
        'dominio_email' => 'empresa.com',
    ]);

    $motorista = criarUsuarioDaOrganizacao($organizacao, ['email' => 'motorista@empresa.com']);
    $motorista->perfilMotorista()->create([
        'cnh' => '12345678901',
        'aprovado_em' => now(),
    ]);

    $passageiroUm = criarUsuarioDaOrganizacao($organizacao, ['email' => 'passageiro1@empresa.com']);
    $passageiroDois = criarUsuarioDaOrganizacao($organizacao, ['email' => 'passageiro2@empresa.com']);

    $response = $this->actingAs($motorista)->post(route('motorista.grupos.store'), [
        'nome' => 'Rota Centro',
        'frequencia' => 'semanal',
        'vagas' => 2,
        'passageiros' => [$passageiroUm->id, $passageiroDois->id],
    ]);

    $response->assertRedirect(route('motorista.home'));

    $this->assertDatabaseHas('grupos_carona', [
        'perfil_motorista_id' => $motorista->perfilMotorista->id,
        'nome' => 'Rota Centro',
        'frequencia' => 'semanal',
        'vagas' => 2,
    ]);

    $grupo = $motorista->perfilMotorista->grupos()->first();

    expect($grupo->passageiros()->pluck('users.id')->all())
        ->toMatchArray([$passageiroUm->id, $passageiroDois->id]);
});

test('nao permite selecionar mais passageiros do que vagas disponiveis', function () {
    $organizacao = Organizacao::create([
        'nome' => 'Empresa Teste',
        'cnpj' => '12345678000199',
        'dominio_email' => 'empresa.com',
    ]);

    $motorista = criarUsuarioDaOrganizacao($organizacao, ['email' => 'motorista@empresa.com']);
    $motorista->perfilMotorista()->create([
        'cnh' => '12345678901',
        'aprovado_em' => now(),
    ]);

    $passageiroUm = criarUsuarioDaOrganizacao($organizacao, ['email' => 'passageiro1@empresa.com']);
    $passageiroDois = criarUsuarioDaOrganizacao($organizacao, ['email' => 'passageiro2@empresa.com']);

    $response = $this->actingAs($motorista)
        ->from(route('motorista.grupos.criar'))
        ->post(route('motorista.grupos.store'), [
            'nome' => 'Rota Centro',
            'frequencia' => 'semanal',
            'vagas' => 1,
            'passageiros' => [$passageiroUm->id, $passageiroDois->id],
        ]);

    $response->assertRedirect(route('motorista.grupos.criar'));
    $response->assertSessionHasErrors('passageiros');

    $this->assertDatabaseCount('grupos_carona', 0);
    $this->assertDatabaseCount('grupo_carona_user', 0);
});
