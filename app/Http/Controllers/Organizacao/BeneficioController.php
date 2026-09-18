<?php

namespace App\Http\Controllers\Organizacao;

use App\Http\Controllers\Controller;
use App\Models\Beneficio;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BeneficioController extends Controller
{
    public function index(Request $request): View
    {
        $beneficios = Beneficio::where('organizacao_id', $request->user()->organizacao_id)
            ->when($request->input('pesquisa'), fn ($q, $p) => $q->where('nome', 'ilike', "%{$p}%"))
            ->get();

        return view('admin.beneficios.index', compact('beneficios'));
    }

    public function create(): View
    {
        return view('admin.beneficios.criar');
    }

    public function store(Request $request): RedirectResponse
    {

        $dadosValidados = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'meta_km' => 'required|numeric|min:1',
        ], [
            'nome.required' => 'O nome do benefício é obrigatório.',
            'meta_km.required' => 'A meta de quilômetros é obrigatória.',
            'meta_km.numeric' => 'Insira um número válido para a meta de KM.',
            'meta_km.min' => 'A meta deve ser de pelo menos 1 KM.',
        ]);

        $organizacaoId = $request->user()->organizacao_id;

        if (! $organizacaoId) {
            return redirect()->back()->withErrors(['erro' => 'Você não está vinculado a nenhuma organização.']);
        }

        Beneficio::create([
            'organizacao_id' => $organizacaoId,
            'nome' => $dadosValidados['nome'],
            'descricao' => $dadosValidados['descricao'],
            'meta_km' => $dadosValidados['meta_km'],
        ]);

        return to_route('admin.beneficios.index')->with('sucesso', 'Benefício cadastrado com sucesso!');
    }

    public function edit(Request $request, Beneficio $beneficio): View
    {
        return view('admin.beneficios.criar', compact('beneficio'));
    }

    public function update(Request $request, Beneficio $beneficio): RedirectResponse
    {
        $dadosValidados = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'meta_km' => 'required|numeric|min:1',
        ], [
            'nome.required' => 'O nome do benefício é obrigatório.',
            'meta_km.required' => 'A meta de quilômetros é obrigatória.',
            'meta_km.numeric' => 'Insira um número válido para a meta de KM.',
            'meta_km.min' => 'A meta deve ser de pelo menos 1 KM.',
        ]);

        $beneficio->update($dadosValidados);

        return to_route('admin.beneficios.index')->with('sucesso', 'Benefício alterado com sucesso!');
    }

    public function destroy(Beneficio $beneficio): RedirectResponse
    {
        $beneficio->delete();

        return redirect()->back();
    }
}
