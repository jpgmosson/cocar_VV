<?php

namespace App\Http\Controllers;

use App\Rules\Cnh;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PerfilMotoristaController extends Controller
{
    public function criar(Request $request): RedirectResponse
    {

        $user = $request->user();
        $validated = $request->validate([
            'cnh' => ['required', 'string', new Cnh],
        ]);

        $user->perfilMotorista()->create([
            'cnh' => $validated['cnh'],
        ]);

        return redirect()->route('motorista.home');
    }
}
