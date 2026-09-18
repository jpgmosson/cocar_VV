<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organizacao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizacaoController extends Controller
{
    public function listar(Request $request): View
    {
        $organizacoes = Organizacao::withCount('integrantes')
            ->when($request->pesquisa,
                fn ($q, $v) => $q->where('nome', 'like', "%{$v}%")->orWhere('dominio_email', 'like', "%{$v}%")->orWhere('cnpj', 'like', "%{$v}%")
            )
            ->get();

        return view('admin.organizacoes', compact('organizacoes'));
    }

    public function alterar(Request $request, Organizacao $organizacao): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        $organizacao->update([
            'nome' => $validated['nome'],
        ]);

        return back();
    }
}
