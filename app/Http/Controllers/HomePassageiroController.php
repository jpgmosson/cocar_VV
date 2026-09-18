<?php

namespace App\Http\Controllers;

use App\Models\GrupoCarona;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class HomePassageiroController extends Controller
{
    // MODIFICADO: Novo controller criado para exibir a home do passageiro com dados dinâmicos
    public function mostrar(Request $request): View
    {
        $user = $request->user();
        
        // MODIFICADO: Busca grupos que o usuário já participa
        $meusGrupos = $user->grupoCaronas()->with('motorista.user')->get();
        
        // MODIFICADO: Busca grupos disponíveis (com vagas) que o usuário ainda não participa
        $gruposDisponiveis = GrupoCarona::with('motorista.user')
            ->withCount('passageiros')
            ->whereDoesntHave('passageiros', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereHas('motorista', function($query) use ($user) {
                $query->where('user_id', '!=', $user->id);
            })
            ->get()
            ->filter(fn($grupo) => $grupo->passageiros_count < $grupo->vagas);

        return view('passageiro.home', compact('meusGrupos', 'gruposDisponiveis'));
    }
}
