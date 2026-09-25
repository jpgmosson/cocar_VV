<?php

namespace Tests\Feature\JoaoMosson;

use App\Actions\Fortify\DeleteUser;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class DeleteUserTest extends TestCase
{
    public function test_exclui_usuario_e_encerra_sessao_ativa(): void
    {
        $user = User::factory()->create();
        Auth::login($user);

        $this->assertTrue(Auth::check());

        $action = new DeleteUser;
        $action->delete($user);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);

        $this->assertFalse(Auth::check());
    }
}
