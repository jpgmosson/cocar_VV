<x-home-layout mode="motorista">
    <x-slot:title>Home Motorista</x-slot:title>
    <x-slot:content>
        <section class="card card--home">
            <form class="route-builder" action="{{ route('trajeto.store') }}" method="post">
                <div class="field">
                    <div class="field__input-wrapper route-builder__input-wrapper">
                        <x-endereco-autocomplete placeholder="Saindo de onde?" name="origem" />
                    </div>
                </div>
                <div class="field">
                    <div class="field__input-wrapper route-builder__input-wrapper">
                        <x-endereco-autocomplete placeholder="Indo para onde?" name="destino" />
                    </div>
                </div>
                <div class="route-builder__row">
                    <button type="submit" class="btn btn--blue route-builder__btn">Publicar Trajeto</button>
                </div>

            </form>
        </section>

        <section class="card card--home">

            @if (session('sucesso'))
                <div class="alert alert-success">
                    {{ session('sucesso') }}
                </div>
            @endif
            @if (session('erro'))
                <div class="alert alert-danger" style="color:red; margin-top:10px;">
                    {{ session('erro') }}
                </div>
            @endif

            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">

                <h3 class="card__heading card__heading--small text-blue">Sua Agenda</h3>

                <a href="{{ route('motorista.grupos.criar') }}" class="btn btn--blue" style="max-width: 250px;">
                    + Criar Grupo de Carona
                </a>
            </div>

            <div class="trip-list">

                <!-- MODIFICADO: Substituídos os trip-cards estáticos pelo loop foreach dinâmico dos $grupos -->
                @forelse($grupos as $grupo)
                    <div class="trip-card">
                        <div class="trip-card__header">
                            <!-- MODIFICADO: Usando o helper formatado em vez de ucfirst -->
                            <span class="trip-card__time text-orange">{{ $grupo->frequenciaFormatada() }}</span>
                            <span class="badge badge--blue">{{ $grupo->passageiros_count }} / {{ $grupo->vagas }}
                                Vagas</span>
                        </div>
                        <div class="trip-card__path">
                            <span class="trip-card__location" style="font-weight:bold;">{{ $grupo->nome }}</span>
                        </div>
                        <div class="trip-card__actions" style="display:flex; gap:8px;">
                            <button class="btn btn--outline trip-card__btn-msg" aria-label="Mensagear passageiros">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24">
                                    <path fill="currentColor"
                                        d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2m-2 12H6v-2h12zm0-3H6V9h12zm0-3H6V6h12z" />
                                </svg>
                            </button>
                            <button class="btn btn--green trip-card__btn-confirm"
                                onclick="document.getElementById('detalhes-grupo-{{ $grupo->id }}').style.display = document.getElementById('detalhes-grupo-{{ $grupo->id }}').style.display === 'none' ? 'block' : 'none';">
                                Ver Detalhes
                            </button>

                            <!-- MODIFICADO: Botão de exclusão -->
                            <form action="{{ route('motorista.grupos.destroy', $grupo->id) }}" method="POST"
                                onsubmit="return confirm('Tem certeza que deseja excluir este grupo de carona?');"
                                style="margin: 0; padding: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--outline" aria-label="Excluir grupo"
                                    style="color: red; border-color: red; padding: 10px; display:flex; align-items:center; justify-content:center;"
                                    title="Excluir Grupo">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 24 24">
                                        <path fill="currentColor"
                                            d="M19 4h-3.5l-1-1h-5l-1 1H5v2h14zM6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6z" />
                                    </svg>
                                </button>
                            </form>
                        </div>

                        <!-- MODIFICADO: Seção de detalhes com os passageiros inscritos -->
                        <div id="detalhes-grupo-{{ $grupo->id }}" class="trip-card__details"
                            style="display: none; width: 100%; margin-top: 15px; border-top: 1px solid #e5e7eb; padding-top: 10px;">
                            <h4 style="font-size: 14px; font-weight: bold; margin-bottom: 5px; color: #111827;">
                                Passageiros:</h4>
                            @if ($grupo->passageiros->isEmpty())
                                <p style="font-size: 14px; color: #6b7280;">Nenhum passageiro inscrito ainda.</p>
                            @else
                                <ul style="list-style: none; padding: 0; margin: 0;">
                                    @foreach ($grupo->passageiros as $passageiro)
                                        <li
                                            style="font-size: 14px; padding: 6px 0; border-bottom: 1px dashed #e5e7eb; display: flex; justify-content: space-between;">
                                            <span
                                                style="font-weight: 500; color: #374151;">{{ $passageiro->name }}</span>
                                            <span
                                                style="color: #6b7280; font-size: 12px;">{{ $passageiro->email }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state-wrapper empty-state-wrapper--compact">
                        <div class="card card--empty">
                            <p>Nenhum grupo de carona criado.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>
    </x-slot:content>
</x-home-layout>
