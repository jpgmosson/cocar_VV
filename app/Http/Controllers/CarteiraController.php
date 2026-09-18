<?php

namespace App\Http\Controllers;

use App\Services\PagamentoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarteiraController extends Controller
{
    public function exibir(Request $request): View
    {
        $carteira = $request->user()->carteira;

        return view('usuario.carteira', compact('carteira'));
    }

    public function depositar(Request $request, PagamentoService $pagamentoService): RedirectResponse
    {
        $validated = $request->validate(
            ['valor' => 'required|min:0|decimal:0,2 '],
            ['valor' => 'Valor inválido, verifique e tente novamente']
        );

        $pagamentoService->depositarValor(Auth::id(), $validated['valor']);

        return back();
    }
}
