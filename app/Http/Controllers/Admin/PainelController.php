<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusCarona;
use App\Enums\StatusTrajeto;
use App\Enums\TipoUsuario;
use App\Http\Controllers\Controller;
use App\Models\Carona;
use App\Models\Organizacao;
use App\Models\PerfilMotorista;
use App\Models\Trajeto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PainelController extends Controller
{
    public function exibir(Request $request): View
    {
        if ($request->user()->eAdminSistema) {
            $totalOrganizacoes = Organizacao::count();
            $totalUsuarios = User::count();
            $totalMotoristasAtivos = PerfilMotorista::whereNotNull('aprovado_em')->count();

            $distanciaTotalMetros = Trajeto::where('status', StatusTrajeto::CONCLUIDO)->sum('distancia_percorrida');
            $distanciaTotal = bcdiv($distanciaTotalMetros, '1000', '2');

            $caronasRealizadas = Carona::where('status', StatusCarona::CONCLUIDA)->count();

            return view('admin.painel-sistema', [
                'totalOrganizacoes' => $totalOrganizacoes,
                'totalUsuarios' => $totalUsuarios,
                'totalMotoristasAtivos' => $totalMotoristasAtivos,
                'distanciaTotal' => $distanciaTotal,
                'emissoesEvitadas' => round($distanciaTotal * 0.12, 2),
                'caronasRealizadas' => $caronasRealizadas,
            ]);
        }

        $organizacao = $request->user()->organizacao;

        $stats = $organizacao->integrantes()
            ->where('tipo', TipoUsuario::PADRAO)
            ->leftJoin('perfis_motorista', 'users.id', '=', 'perfis_motorista.user_id')
            ->selectRaw('
                count(*) as nao_admins,
                count(CASE WHEN perfis_motorista.aprovado_em IS NOT NULL THEN 1 END) as motoristas_ativos,
                count(CASE WHEN perfis_motorista.id IS NOT NULL AND perfis_motorista.aprovado_em IS NULL THEN 1 END) as motoristas_pendentes
            ')
            ->first();

        $distanciaTotalMetros = Trajeto::whereHas('user', function ($query) use ($organizacao) {
            $query->where('organizacao_id', $organizacao->id);
        })
            ->where('status', StatusTrajeto::CONCLUIDO)
            ->sum('distancia_percorrida');
        $distanciaTotal = bcdiv($distanciaTotalMetros, '1000', 2);

        $caronasRealizadas = Carona::whereHas('pedidoCarona.user', function ($query) use ($organizacao) {
            $query->where('organizacao_id', $organizacao->id);
        })
            ->where('status', StatusCarona::CONCLUIDA)
            ->count();

        return view('admin.painel-organizacao', [
            'organizacao' => $organizacao,
            'totalUsuarios' => $stats->nao_admins,
            'totalMotoristasAtivos' => $stats->motoristas_ativos,
            'totalMotoristasPendentes' => $stats->motoristas_pendentes,
            'distanciaTotal' => round($distanciaTotal, 2),
            'emissoesEvitadas' => round($distanciaTotal * 0.12, 2),
            'caronasRealizadas' => $caronasRealizadas,
        ]);
    }
}
