<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DeleteUser
{
    public function delete(User $user): void
    {
        Auth::logout();
        $user->delete();
    }
}
