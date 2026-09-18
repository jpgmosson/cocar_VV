<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class HomeMotoristaController extends Controller
{
    public function mostrar(Request $request): View
    {
        $perfilMotorista = $request->user()->perfilMotorista;
        if (! $perfilMotorista) {
            return view('motorista.cadastro');
        }

        if (! $perfilMotorista->aprovado_em) {
            return view('motorista.pendente');
        }

        // MODIFICADO: Buscar grupos com a quantidade de passageiros inscritos e os dados dos passageiros
        $grupos = $perfilMotorista->grupos()->withCount('passageiros')->with('passageiros')->get();

        return view('motorista.home', compact('grupos'));
    }
}
