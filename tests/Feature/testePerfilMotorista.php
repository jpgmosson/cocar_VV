<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

test('deve criar um perfil de motorista com dados válidos e redirecionar', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('motorista.cadastro'), [
            'cnh' => '1234567890',
        ]);

    $response->assertRedirect(route('motorista.home'));

    $this->assertDatabaseHas('perfil_motoristas', [
        'user_id' => $user->id,
        'cnh' => '1234567890',
    ]);
});

test('não deve criar perfil se a CNH não for enviada', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('motorista.cadastro'), [
            'cnh' => '',
        ]);

    $response->assertSessionHasErrors(['cnh']);

    $this->assertDatabaseCount('perfil_motoristas', 0);
});

test('convidados (não logados) não podem criar perfis', function () {
    $response = $this->post(route('motorista.cadastro'), [
        'cnh' => '1234567890',
    ]);

    $response->assertRedirect('/login');
});
