<?php

namespace App\Http\Controllers;

use App\Models\Carona;
use App\Models\PedidoCarona;
use App\Models\Trajeto;
use App\Services\SugestaoCaronaService;
use App\Services\TrajetoService;
use App\ValueObjects\Point;
use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TrajetoController extends Controller
{
    public function store(Request $request, TrajetoService $trajetoService): RedirectResponse
    {
        $validated = $request->validate([
            'origem_coords' => 'required',
            'origem_endereco' => 'required',
            'destino_coords' => 'required',
            'destino_endereco' => 'required',
        ]);

        $validated['origem_coords'] = Point::from($validated['origem_coords']);
        $validated['destino_coords'] = Point::from($validated['destino_coords']);

        $trajeto = $trajetoService->novoTrajeto(
            $validated,
            Auth::id()
        );

        return to_route('trajeto.show', ['trajeto' => $trajeto->id]);
    }

    public function show(Request $request, Trajeto $trajeto): Response
    {
        return response()
            ->view('motorista.mapa', compact('trajeto'))
            ->header('Hx-Trigger-After-Settle', 'atualizarRota');
    }

    public function rota(Request $request, Trajeto $trajeto, TrajetoService $trajetoService)
    {
        $trajeto->paradas = $trajetoService->obterParadas($trajeto);

        return json_encode($trajeto);
    }

    public function sugestoesCarona(Request $request, int $trajetoID, SugestaoCaronaService $service): Response
    {
        $sugestoes = $service->obterSugestoesParaTrajeto($trajetoID);

        return response()
            ->view('motorista.sugestoes-carona', compact('sugestoes'))
            ->header('Hx-Trigger-After-Settle', 'hidratarSugestoesCarona');
    }

    public function atenderPedidoCarona(Request $request, Trajeto $trajeto, PedidoCarona $pedidoCarona, TrajetoService $service): Response
    {
        $service->atenderPedidoCarona($trajeto, $pedidoCarona);

        return response('')->header('Hx-Trigger', 'atualizarRota');
    }

    public function iniciar(Request $request, Trajeto $trajeto, TrajetoService $trajetoService): Response
    {
        $trajetoService->iniciarTrajeto($trajeto);

        return response('')->header('Hx-Refresh', 'true');
    }

    public function finalizar(Request $request, int $trajetoID, TrajetoService $trajetoService): Response
    {
        $trajetoService->finalizarTrajeto($trajetoID);

        return response('')->header('Hx-Redirect', route($request->user()->homeUrl()));
    }

    public function embarcar(Request $request, Trajeto $trajeto, Carona $carona, TrajetoService $service): Response
    {
        $service->embarcarPassageiro($trajeto, $carona);

        return response('')->header('Hx-Trigger', 'atualizarRota');
    }

    public function cancelarCarona(Request $request, Trajeto $trajeto, Carona $carona, TrajetoService $service): Response
    {
        $service->cancelarCarona($carona);

        return response('')->header('Hx-Trigger', 'atualizarRota');

    }

    public function destroy(Request $request, Trajeto $trajeto, TrajetoService $service): Response
    {
        $service->cancelarTrajeto($trajeto);

        return response('')->header('Hx-Redirect', route($request->user()->homeUrl()));
    }
}
