<?php

namespace App\Http\Controllers;

use App\Enums\TipoUsuario;
use App\Models\GrupoCarona;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
// MODIFICADO: Adicionado import do GrupoCarona
use Illuminate\Validation\Rule;

class GrupoCaronaController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $perfilMotorista = $user->perfilMotorista;

        if ($perfilMotorista && $perfilMotorista->aprovado_em) {
            $grupos = $perfilMotorista->grupos()->withCount('passageiros')->with('passageiros')->get();
            return view('grupos.index', compact('grupos', 'perfilMotorista'));
        }

        $meusGrupos = $user->grupoCaronas()->with('motorista.user')->get();
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

        return view('grupos.index', compact('meusGrupos', 'gruposDisponiveis', 'perfilMotorista'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $motorista = $request->user()->perfilMotorista;

        if (! $motorista || ! $motorista->aprovado_em) {
            return redirect()->route('motorista.home');
        }

        $passageirosDisponiveis = $this->buscarPassageirosDisponiveis($request->user());

        return view('motorista.criar', compact('passageirosDisponiveis'));
    }

    public function store(Request $request): RedirectResponse
    {
        $motorista = $request->user()->perfilMotorista;

        if (! $motorista || ! $motorista->aprovado_em) {
            return redirect()->route('motorista.home');
        }

        $passageirosDisponiveis = $this->buscarPassageirosDisponiveis($request->user());
        $idsPassageirosDisponiveis = $passageirosDisponiveis->pluck('id')->all();

        $dadosValidados = $request->validate([
            'nome' => 'required|string|max:255',
            'frequencia' => 'required|in:semanal,mensal',
            // MODIFICADO: Validação condicional dos dias de semana
            'dias_semana' => 'required_if:frequencia,semanal|array',
            'dias_semana.*' => 'in:seg,ter,qua,qui,sex,sab,dom',
            // MODIFICADO: Validação condicional dos dias do mês
            'dias_mes' => 'required_if:frequencia,mensal|array',
            'dias_mes.*' => 'integer|min:1|max:31',
            'vagas' => 'required|integer|min:1|max:4',
            'passageiros' => 'nullable|array',
            'passageiros.*' => [
                'integer',
                Rule::in($idsPassageirosDisponiveis),
            ],
        ],
            [
                'nome.required' => 'Ei, você esqueceu de dar um nome para a carona!',
                'nome.max' => 'O nome do grupo está muito grande.',
                'frequencia.required' => 'Por favor, informe se a carona é semanal ou mensal.',
                'frequencia.in' => 'Opção de frequência inválida.',
                'vagas.required' => 'Você precisa informar quantas vagas tem no carro.',
                'vagas.max' => 'O limite máximo é de 4 vagas por carro.',
            ]);

        // MODIFICADO: Se selecionou semanal, apaga mensal (caso haja sujeira no POST). E vice-versa.
        if ($dadosValidados['frequencia'] === 'semanal') {
            $dadosValidados['dias_mes'] = null;
        } else {
            $dadosValidados['dias_semana'] = null;
        }

        $passageirosSelecionados = collect($dadosValidados['passageiros'] ?? [])->unique()->values();

        if ($passageirosSelecionados->count() > $dadosValidados['vagas']) {
            return back()
                ->withErrors(['passageiros' => 'Selecione no máximo a mesma quantidade de passageiros das vagas disponíveis.'])
                ->withInput();
        }

        unset($dadosValidados['passageiros']);

        $grupo = $motorista->grupos()->create($dadosValidados);
        $grupo->passageiros()->sync($passageirosSelecionados->all());

        return redirect()->route('motorista.home')
            ->with('sucesso', 'Grupo de carona criado com sucesso!');
    }

    private function buscarPassageirosDisponiveis(User $user)
    {
        return User::query()
            ->where('organizacao_id', $user->organizacao_id)
            ->whereKeyNot($user->id)
            ->where('tipo', TipoUsuario::PADRAO)
            ->whereDoesntHave('perfilMotorista')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    // MODIFICADO: Adicionado método para inscrever um passageiro em um grupo de carona
    public function entrar(GrupoCarona $grupo, Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($grupo->passageiros()->where('user_id', $user->id)->exists()) {
            return back()->with('erro', 'Você já está neste grupo.');
        }

        if ($grupo->passageiros()->count() >= $grupo->vagas) {
            return back()->with('erro', 'Este grupo já está lotado.');
        }

        $grupo->passageiros()->attach($user->id);

        return back()->with('sucesso', 'Você entrou no grupo de carona com sucesso!');
    }

    public function sair(GrupoCarona $grupo, Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $grupo->passageiros()->where('user_id', $user->id)->exists()) {
            return back()->with('erro', 'Você não está neste grupo.');
        }

        $grupo->passageiros()->detach($user->id);

        return back()->with('sucesso', 'Você saiu do grupo de carona com sucesso!');
    }

    // MODIFICADO: Método para o motorista excluir o próprio grupo
    public function destroy(GrupoCarona $grupo, Request $request): RedirectResponse
    {
        $perfilMotorista = $request->user()->perfilMotorista;

        if (! $perfilMotorista || $grupo->perfil_motorista_id !== $perfilMotorista->id) {
            return back()->with('erro', 'Acesso não autorizado para exclusão deste grupo.');
        }

        $grupo->delete();

        return back()->with('sucesso', 'Grupo de carona excluído com sucesso.');
    }
}
