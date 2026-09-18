<x-home-layout mode="passageiro">
    <x-slot:title>Passageiro</x-slot:title>
    <x-slot:content>
        <section class="card card--home">
            <form class="search-bar" action="{{ route('pedido-carona.store') }}" method="post" autocomplete="off">
                @csrf
                <div class="route-timeline" hx-get="{{ route('pedido-carona.estimativa') }}"
                    hx-trigger="endereco-selected[this.querySelectorAll('[data-coord-input]:not([value=\'\'])').length === this.querySelectorAll('[data-coord-input]').length]"
                    hx-target="#estimate-container" hx-include="[data-coord-input]" >
                    <div class="field search-bar__field">
                        <div class="field__input-wrapper search-bar__input-wrapper">
                            <div class="field__input-icon text-blue">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r=" 6" fill="currentColor" />
                </svg>
                </div>
                <x-endereco-autocomplete placeholder="Ponto de Partida" name="origem" />
                </div>
                </div>

                <div class="field search-bar__field">
                    <div class="field__input-wrapper search-bar__input-wrapper">
                        <div class="field__input-icon text-orange">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                <path fill="currentColor"
                                    d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5s2.5 1.12 2.5 2.5s-1.12 2.5-2.5 2.5z" />
                            </svg>
                        </div>
                        <x-endereco-autocomplete placeholder="Para onde deseja ir?" name="destino" />
                    </div>
                </div>
                </div>

                @if ($errors->has('erro'))
                    <div class="alert alert-danger" style="margin-top:15px; color:red;">
                        {{ $errors->first('erro') }}
                    </div>
                @endif

                <div id="estimate-container">
                </div>
            </form>

            @if (session('sucesso'))
                <div class="alert alert-success" style="margin-top:15px; color:green;">
                    {{ session('sucesso') }}
                </div>
            @endif
            @if (session('erro'))
                <div class="alert alert-danger" style="margin-top:15px; color:red;">
                    {{ session('erro') }}
                </div>
            @endif

            <div class="shortcuts">
                <button class="shortcuts__item">
                    <div class="shortcuts__icon text-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M10 20v-6h4v6h5v-8h3L12 3L2 12h3v8z" />
                        </svg>
                    </div>
                    <span class="shortcuts__label">Casa</span>
                </button>
                <button class="shortcuts__item" ty>
                    <div class="shortcuts__icon text-orange">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z" />
                        </svg>
                    </div>
                    <span class="shortcuts__label">Trabalho</span>
                </button>
                <button class="shortcuts__item">
                    <div class="shortcuts__icon text-green">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05c1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                        </svg>
                    </div>
                    <span class="shortcuts__label">Escritório RH</span>
                </button>
                <button class="shortcuts__item">
                    <div class="shortcuts__icon text-green">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M19 12.998h-6v6h-2v-6H5v-2h6v-6h2v6h6z" />
                        </svg>
                    </div>
                    <span class="shortcuts__label">Adicionar</span>
                </button>

            </div>
        </section>

        <section class="card card--home">
            <h3 class="card__heading card__heading--small text-blue">Próximas Caronas</h3>
            <p class="card__text card__text--left card__text--secondary">Sua agenda de mobilidade coletiva.</p>

            <!-- MODIFICADO: Mostrando grupos que o passageiro participa -->
            <div class="trip-list" style="margin-top: 15px;">
                @forelse($meusGrupos as $grupo)
                    <div class="trip-card">
                        <div class="trip-card__header">
                            <!-- MODIFICADO: Usando o helper formatado em vez de ucfirst -->
                            <span class="trip-card__time text-blue">{{ $grupo->frequenciaFormatada() }}</span>
                            <span class="badge badge--green">Inscrito</span>
                        </div>
                        <div class="trip-card__path">
                            <span class="trip-card__location" style="font-weight:bold;">{{ $grupo->nome }}</span>
                        </div>
                        <div class="trip-card__actions" style="margin-top:10px; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:14px; color:#6b7280;">Motorista:
                                {{ $grupo->motorista->user->name }}</span>
                            <form action="{{ route('grupos.sair', $grupo->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja sair deste grupo?');" style="margin: 0; padding: 0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn--outline" style="color:red; border-color:red; padding: 4px 10px; font-size: 13px;">
                                    Sair
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="empty-state-wrapper empty-state-wrapper--compact">
                        <div class="card card--empty">
                            <div class="card__icon text-blue">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                    viewBox="0 0 24 24">
                                    <path fill="currentColor"
                                        d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5s1.5.67 1.5 1.5s-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z" />
                                </svg>
                            </div>
                            <p>Nenhuma rota programada. Inscreva-se numa carona abaixo!</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- MODIFICADO: Nova section para caronas disponíveis para aplicar -->
        <section class="card card--home">
            <h3 class="card__heading card__heading--small text-orange">Caronas Disponíveis</h3>
            <p class="card__text card__text--left card__text--secondary">Junte-se a grupos da sua organização.</p>
            <div class="trip-list" style="margin-top: 15px;">
                @forelse($gruposDisponiveis as $grupo)
                    <div class="trip-card">
                        <div class="trip-card__header">
                            <!-- MODIFICADO: Usando o helper formatado em vez de ucfirst -->
                            <span class="trip-card__time text-text-muted">{{ $grupo->frequenciaFormatada() }}</span>
                            <span class="badge badge--blue">{{ $grupo->vagas - $grupo->passageiros_count }} Vagas
                                Livres</span>
                        </div>
                        <div class="trip-card__path">
                            <span class="trip-card__location" style="font-weight:bold;">{{ $grupo->nome }}</span>
                        </div>
                        <div class="trip-card__actions"
                            style="margin-top:10px; display:flex; justify-content:space-between; align-items:center;">
                            <span style="font-size:14px; color:#6b7280;">{{ $grupo->motorista->user->name }}</span>

                            <form action="{{ route('grupos.entrar', $grupo->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn--green"
                                    style="padding: 6px 12px; font-size:14px;">
                                    Participar
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="empty-state-wrapper empty-state-wrapper--compact">
                        <div class="card card--empty">
                            <p>Nenhuma carona com vagas disponível no momento.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="card card--home">
            <h3 class="card__heading card__heading--small text-green">Nosso Impacto</h3>
            <div class="stats-grid stats-grid--compact">
                <div class="stats-grid__item">
                    <span class="stats-grid__value text-green">34kg</span>
                    <span class="stats-grid__label">CO₂ Poupado</span>
                </div>
                <div class="stats-grid__item">
                    <span class="stats-grid__value text-orange">12</span>
                    <span class="stats-grid__label">Conexões Feitas</span>
                </div>
            </div>
        </section>
    </x-slot:content>
</x-home-layout>
