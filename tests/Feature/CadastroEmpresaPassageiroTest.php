<?php

use App\Models\Organizacao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('cadastra uma empresa e depois permite cadastrar um passageiro do mesmo dominio', function () {
    $this->post('/registrar/empresa', [
        'empresa-nome' => 'Empresa Teste',
        'cnpj' => '12345678901234',
        'dominio-email' => 'empresa-teste.com',
        'administrador-nome' => 'Admin Teste',
        'administrador-email' => 'admin@empresa-teste.com',
        'password' => 'Senha123!',
        'password_confirmation' => 'Senha123!',
    ])->assertRedirect(route('admin-empresa.painel'));

    $empresa = Organizacao::query()->where('dominio_email', 'empresa-teste.com')->first();

    expect($empresa)->not->toBeNull();
    expect(User::query()->where('email', 'admin@empresa-teste.com')->exists())->toBeTrue();

    auth()->logout();

    $this->post('/register', [
        'name' => 'Passageiro Teste',
        'email' => 'passageiro@empresa-teste.com',
        'password' => 'Senha123!',
        'password_confirmation' => 'Senha123!',
        'papel' => 'passageiro',
    ])->assertRedirect('/home');

    $usuario = User::query()->where('email', 'passageiro@empresa-teste.com')->first();

    expect($usuario)->not->toBeNull();
    expect($usuario->empresa_id)->toBe($empresa->id);
    expect($usuario->papel->value)->toBe('passageiro');
});
