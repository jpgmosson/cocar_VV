<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TipoUsuario;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UsuarioController extends Controller
{
    public function exibir(Request $request): View
    {
        $user = $request->user();
        $query = match ($user->tipo) {
            TipoUsuario::ADMINISTRADOR_ORGANIZACAO => $user->organizacao->integrantes(),
            TipoUsuario::ADMINISTRADOR_SISTEMA => User::query(),
        };

        $users = $query->comStatusMotorista($request->input('status-mototista'))
            ->when($request->pesquisa, fn ($q, $v) => $q->where(fn ($sq) => $sq->where('name', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%")))
            ->porTipo($request->tipo)
            ->get();

        return view('admin.usuarios', compact('users'));
    }
}
