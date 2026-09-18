<?php

namespace App\Http\Controllers;

use App\Models\Carteira;
use App\Models\Transacao;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransacaoController extends Controller
{
    public function index(Request $request): View
    {
        $userID = Auth::id();
        $transacoes = Transacao::withContextAndLedger()->where('user_id', $userID)->orderBy('updated_at')->get();
        $carteira = Carteira::where('user_id', $userID)->first();

        return view('usuario.atividade', compact('transacoes', 'carteira'));
    }
}
