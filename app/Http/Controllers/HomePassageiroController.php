<?php

namespace App\Http\Controllers;

use App\Models\GrupoCarona;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomePassageiroController extends Controller
{
    public function mostrar(Request $request): View
    {
        $user = $request->user();

        $meusGrupos = $user->grupoCaronas()->with('motorista.user')->get();

        $gruposDisponiveis = GrupoCarona::with('motorista.user')
            ->withCount('passageiros')
            ->whereDoesntHave('passageiros', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereHas('motorista', function ($query) use ($user) {
                $query->where('user_id', '!=', $user->id);
            })
            ->get()
            ->filter(fn ($grupo) => $grupo->passageiros_count < $grupo->vagas);

        return view('passageiro.home', compact('meusGrupos', 'gruposDisponiveis'));
    }
}
