<?php

namespace App\View\Components;

use App\Enums\FaseCarona;
use App\Enums\StatusCarona;
use App\Enums\StatusPedidoCarona;
use App\Enums\StatusTrajeto;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class ViagemAtiva extends Component
{
    public $pedido;

    public $trajeto;

    public function __construct()
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $pedido = $user->pedidosCarona()
            ->where('status', StatusPedidoCarona::PROCURANDO_MOTORISTA)
            ->latest()
            ->first();

        if (! $pedido) {
            $pedido = $user->pedidosCarona()
                ->where('status', StatusPedidoCarona::ATENDIDO)
                ->whereHas('caronaAtual', function ($query) {
                    $query->whereIn('status', StatusCarona::naFase(FaseCarona::ATIVA));
                })
                ->latest()
                ->first();
        }
$this->pedido = $pedido;

        $this->trajeto = $user->trajetos()
            ->whereIn('status', [StatusTrajeto::EM_ANDAMENTO, StatusTrajeto::PLANEJADO])
            ->latest()
            ->first();
    }

    public function shouldRender(): bool
    {
        return $this->pedido !== null || $this->trajeto !== null;
    }

    public function render(): View|Closure|string
    {
        return view('components.viagem-ativa');
    }
}
