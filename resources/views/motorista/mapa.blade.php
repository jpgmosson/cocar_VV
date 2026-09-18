@use('App\Enums\StatusTrajeto')
<x-map>
    <x-slot:title>
        Adicionar Passageiros
    </x-slot:title>
    <x-slot:content>
        <div class="bottom-panel">
            @if ($trajeto->status == StatusTrajeto::PLANEJADO)
                <form class="bottom-panel__actions" hx-post="{{ route('trajeto.iniciar', ['trajeto' => $trajeto->id]) }}">
                    <button class="btn btn--green" type="submit">Iniciar Trajeto</button>
                </form>
                <div hx-trigger="load" hx-get="{{ route('trajeto.sugestoes-carona', ['trajeto' => $trajeto->id]) }}"
                    id="sugestoes-carona" style="display: none;"></div>
            @endif
            <form class="bottom-panel__actions" hx-post="{{ route('trajeto.destroy', ['trajeto' => $trajeto->id]) }}">
                @csrf
                @method('DELETE')
                <button class="btn btn--red" type="submit">Cancelar Trajeto</button>
            </form>
        </div>

        <script>
            async function syncTrajeto() {
                const res = await fetch("{{ route('trajeto.rota', ['trajeto' => $trajeto->id]) }}");
                const trajeto = await res.json();
                atualizarRota(trajeto)

                for (const [idx, parada] of trajeto.paradas.entries()) {
                    const isFirst = idx === 0;
                    const isLast = idx === trajeto.paradas.length - 1;

                    let action = '';

                    if (!isFirst) {
                        if (isLast) {
                            if (trajeto.status != 'planejado') {
                                const urlFinalizar = "{{ route('trajeto.finalizar', ['trajeto' => $trajeto->id]) }}";
                                action += `<form class="action-form" hx-post="${urlFinalizar}">
                                    <button class="btn btn--blue" type="submit">Finalizar</button>
                                </form>`;
                            }
                        } else {
                            let urlCancelar =
                                "{{ route('trajeto.cancelar-carona', ['trajeto' => $trajeto->id, 'carona' => ':caronaID']) }}"
                                .replace(':caronaID', parada.carona_id);

                            action += `<form class="action-form" hx-delete="${urlCancelar}" hx-swap="outerHTML">
                                <button class="btn btn--red" type="submit">Cancelar</button>
                            </form>`;

                            if (trajeto.status != 'planejado') {
                                let urlEmbarcar =
                                    "{{ route('trajeto.embarcar-carona', ['trajeto' => $trajeto->id, 'carona' => ':caronaID']) }}"
                                    .replace(':caronaID', parada.carona_id);

                                action += `<form class="action-form" hx-post="${urlEmbarcar}" hx-swap="outerHTML">
                                    <button class="btn btn--blue" type="submit">Embarcar</button>
                                </form>`;
                            }
                        }
                    }

                    const el = document.createElement('div');
                    el.className = 'map-popup';
                    el.innerHTML = `<div class="map-popup__actions">${action}</div>`;

                    htmx.process(el);

                    const popup = new maplibregl.Popup().setDOMContent(el);
                    new maplibregl.Marker({
                            color: "#365691"
                        })
                        .setLngLat(parada.coord)
                        .setPopup(popup)
                        .addTo(map);
                }
            }

            document.addEventListener('atualizarRota', syncTrajeto);

            map.on('load', syncTrajeto);

            document.addEventListener('hidratarSugestoesCarona', async () => {
                parent = document.getElementById('sugestoes-carona');
                for (const el of parent.children) {
                    const popup = new maplibregl.Popup().setDOMContent(el);
                    const marker = new maplibregl.Marker({
                            color: "#be602d"
                        }).setLngLat(JSON.parse(el.dataset.coord))
                        .setPopup(popup).addTo(map);
                }
            })
        </script>
    </x-slot:content>
</x-map>
