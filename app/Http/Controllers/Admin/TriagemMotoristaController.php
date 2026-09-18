<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PerfilMotorista;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use function Illuminate\Support\now;

class TriagemMotoristaController extends Controller
{
    public function exibir(Request $request): View
    {
        $organizacaoId = $request->user()->organizacao_id;
        $motoristasPendentes = PerfilMotorista::whereNull('aprovado_em')
            ->withWhereHas('user', fn ($query) => $query->where('organizacao_id', $organizacaoId))->get();

        return view('admin.triagem-motoristas', compact('motoristasPendentes'));
    }

    public function aprovar(Request $request, PerfilMotorista $perfilMotorista): RedirectResponse
    {
        if ($request->user()->organizacao_id !== $perfilMotorista->user->organizacao_id) {
            abort(403, 'O usuário não faz parte da sua organizacao.');
        }

        $perfilMotorista->update(['aprovado_em' => now()]);

        return back();
    }

    public function rejeitar(Request $request, PerfilMotorista $perfilMotorista): RedirectResponse
    {
        $perfilMotorista->delete();

        return back();
    }
}
