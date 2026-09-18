<?php

use App\Models\User;
use App\Models\Carteira;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Model;


uses(Tests\TestCase::class, RefreshDatabase::class);

test('usuário consegue visualizar sua carteira', function () {
    Model::unguard();

    $user = User::factory()->create();
    $carteira = Carteira::create([
        'user_id' => $user->id,
        'Saldo_atual' => 100.50
    ]);

    Model::reguard();

    $response = $this->actingAs($user)
        ->get(route('usuario.carteira'));

    $response->assertStatus(200);
    $response->assertViewIs('usuario.carteira');
    $response->assertViewHas('carteira');
});

test('usuário consegue inserir saldo na carteira', function () {
    Model::unguard();

    $user = User::factory()->create();
    $carteira = Carteira::create([
        'user_id' => $user->id,
        'Saldo_atual' => 50.00
    ]);

    Model::reguard();

    $response = $this->actingAs($user)
        ->put(route('usuario.carteira.inserir'), [
            'valor' => 25.50
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('carteiras', [
        'user_id' => $user->id,
        'Saldo_atual' => 75.50
    ]);
});

test('não deve permitir inserir valores negativos ou inválidos', function () {
    Model::unguard();
    $user = User::factory()->create();
    $carteira = Carteira::create([
        'user_id' => $user->id,
        'Saldo_atual' => 50.00
    ]);
    Model::reguard();

    $response = $this->actingAs($user)
        ->from(route('usuario.carteira'))
        ->put(route('usuario.carteira.inserir'), [
            'valor' => -10.00
        ]);

    $response->assertSessionHasErrors(['valor']);

    $this->assertEquals(50.00, $carteira->fresh()->Saldo_atual);
});
