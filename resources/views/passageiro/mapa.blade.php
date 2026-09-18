@use('App\Enums\StatusCarona')
@use('App\Enums\FaseCarona')
@use('App\Enums\StatusPedidoCarona')
<x-map>
    <x-slot:title>Pedido Carona</x-slot:title>
    <x-slot:content>
        <div class="bottom-panel">
            @php
                $status = $pedidoCarona->status();
            @endphp

            @if ($status == StatusPedidoCarona::PROCURANDO_MOTORISTA)
                <div class="bottom-panel__header">
                    <span class="bottom-panel__title">Procurando motorista...</span>
                    <span class="bottom-panel__subtitle">Isso pode levar alguns minutos</span>
                </div>

                <form class="bottom-panel__actions"
                    action="{{ route('pedido-carona.destroy', ['pedidoCarona' => $pedidoCarona->id]) }}" method="post">
                    @method('DELETE')
                    <button class="btn btn--outline btn--red" type="sumbit">Cancelar</button>
                </form>
            @elseif ($status == StatusCarona::ACEITA)
                <div class="bottom-panel__header">
                    <span class="bottom-panel__title">Carona aceita</span>
                    <span class="bottom-panel__subtitle">Aguardando motorista iniciar trajeto</span>
                </div>

                <div class="bottom-panel__profile">
                    <div class="bottom-panel__avatar">{{ $pedidoCarona->caronaAtual->trajeto->user->iniciais() }}</div>
                    <div class="bottom-panel__info">
                        <span class="bottom-panel__name">{{ $pedidoCarona->caronaAtual->trajeto->user->name }}</span>
                        <span class="bottom-panel__text">Fiat Uno Branco • ABC-1234</span>
                    </div>
                </div>

                <div class="bottom-panel__actions">
                    <button class="btn btn--outline btn--blue" type="button">Contato</button>
                    <form class="action-form"
                        action="{{ route('pedido-carona.destroy', ['pedidoCarona' => $pedidoCarona->id]) }}"
                        method="post">
                        @method('DELETE')
                        <button class="btn btn--outline btn--red" type="submit">Cancelar</button>
                    </form>
                </div>
            @elseif ($status == StatusCarona::MOTORISTA_A_CAMINHO)
                <div class="bottom-panel__header">
                    <span class="bottom-panel__title">Motorista a caminho</span>
                    <span class="bottom-panel__subtitle">Chega em 5 minutos</span>
                </div>

                <div class="bottom-panel__profile">
                    <div class="bottom-panel__avatar">{{ $pedidoCarona->caronaAtual->trajeto->user->iniciais() }}</div>
                    <div class="bottom-panel__info">
                        <span class="bottom-panel__name">{{ $pedidoCarona->caronaAtual->trajeto->user->name }}</span>
                        <span class="bottom-panel__text">Fiat Uno Branco • ABC-1234</span>
                    </div>
                </div>
            @elseif ($status == StatusCarona::EM_ANDAMENTO)
                <div class="bottom-panel__header">
                    <span class="bottom-panel__title">A caminho do destino</span>
                    <span class="bottom-panel__subtitle">Chegada prevista às 14:30</span>
                </div>

                <div class="bottom-panel__actions">
                    <button class="btn btn--outline btn--blue" type="button">Compartilhar Rota</button>
                </div>
            @endif

            @if ($status->fase() == FaseCarona::ATIVA)
                <script>
                    map.on('load', async () => {
                        const res = await fetch(
                            "{{ route('trajeto.rota', ['trajeto' => $pedidoCarona->caronaAtual->trajeto]) }}");
                        const trajeto = await res.json();
                        atualizarRota(trajeto);
                    });
                </script>
            @endif
        </div>
        <script>
            map.on('load', () => {
                new maplibregl.Marker().setLngLat(@json($pedidoCarona->origem_coords)).addTo(map);
                new maplibregl.Marker().setLngLat(@json($pedidoCarona->destino_coords)).addTo(map);
            })
        </script>
    </x-slot:content>
</x-map>
