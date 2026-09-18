<x-home-layout :mode="($perfilMotorista && $perfilMotorista->aprovado_em) ? 'motorista' : 'passageiro'">
    <x-slot:title>Grupos de Carona</x-slot:title>
    <x-slot:content>
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

        @if($perfilMotorista && $perfilMotorista->aprovado_em)
            {{-- Visão do Motorista --}}
            <section class="card card--home">
                <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                    <h3 class="card__heading card__heading--small text-blue">Seus Grupos Criados</h3>
                    <a href="{{ route('motorista.grupos.criar') }}" class="btn btn--blue" style="max-width: 250px;">
                        + Criar Grupo
                    </a>
                </div>

                <div class="trip-list" style="margin-top: 15px;">
                    @forelse($grupos as $grupo)
                        <div class="trip-card">
                            <div class="trip-card__header">
                                <span class="trip-card__time text-orange">{{ $grupo->frequenciaFormatada() }}</span>
                                <span class="badge badge--blue">{{ $grupo->passageiros_count }} / {{ $grupo->vagas }} Vagas</span>
                            </div>
                            <div class="trip-card__path">
                                <span class="trip-card__location" style="font-weight:bold;">{{ $grupo->nome }}</span>
                            </div>
                            <div class="trip-card__actions" style="display:flex; gap:8px;">
                                <button class="btn btn--outline trip-card__btn-msg" aria-label="Mensagear passageiros">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2m-2 12H6v-2h12zm0-3H6V9h12zm0-3H6V6h12z"/></svg>
                                </button>
                                <button class="btn btn--green trip-card__btn-confirm" onclick="document.getElementById('detalhes-grupo-{{ $grupo->id }}').style.display = document.getElementById('detalhes-grupo-{{ $grupo->id }}').style.display === 'none' ? 'block' : 'none';">
                                    Ver Detalhes
                                </button>
                                <form action="{{ route('motorista.grupos.destroy', $grupo->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este grupo de carona?');" style="margin: 0; padding: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn--outline" aria-label="Excluir grupo" style="color: red; border-color: red; padding: 10px; display:flex; align-items:center; justify-content:center;" title="Excluir Grupo">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path fill="currentColor" d="M19 4h-3.5l-1-1h-5l-1 1H5v2h14zM6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6z"/></svg>
                                    </button>
                                </form>
                            </div>

                            <div id="detalhes-grupo-{{ $grupo->id }}" class="trip-card__details" style="display: none; width: 100%; margin-top: 15px; border-top: 1px solid #e5e7eb; padding-top: 10px;">
                                <h4 style="font-size: 14px; font-weight: bold; margin-bottom: 5px; color: #111827;">Passageiros:</h4>
                                @if($grupo->passageiros->isEmpty())
                                    <p style="font-size: 14px; color: #6b7280;">Nenhum passageiro inscrito ainda.</p>
                                @else
                                    <ul style="list-style: none; padding: 0; margin: 0;">
                                        @foreach($grupo->passageiros as $passageiro)
                                            <li style="font-size: 14px; padding: 6px 0; border-bottom: 1px dashed #e5e7eb; display: flex; justify-content: space-between;">
                                                <span style="font-weight: 500; color: #374151;">{{ $passageiro->name }}</span>
                                                <span style="color: #6b7280; font-size: 12px;">{{ $passageiro->email }}</span>
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

        @else
            {{-- Visão do Passageiro --}}
            <section class="card card--home">
                <h3 class="card__heading card__heading--small text-blue">Grupos Inscritos</h3>
                <p class="card__text card__text--left card__text--secondary">Sua agenda de mobilidade coletiva.</p>

                <div class="trip-list" style="margin-top: 15px;">
                    @forelse($meusGrupos as $grupo)
                        <div class="trip-card">
                            <div class="trip-card__header">
                                <span class="trip-card__time text-blue">{{ $grupo->frequenciaFormatada() }}</span>
                                <span class="badge badge--green">Inscrito</span>
                            </div>
                            <div class="trip-card__path">
                                <span class="trip-card__location" style="font-weight:bold;">{{ $grupo->nome }}</span>
                            </div>
                            <div class="trip-card__actions" style="margin-top:10px; display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:14px; color:#6b7280;">Motorista: {{ $grupo->motorista->user->name }}</span>
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
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"><path fill="currentColor" d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5s1.5.67 1.5 1.5s-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/></svg>
                                </div>
                                <p>Nenhum grupo programado. Inscreva-se em um abaixo!</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </section>

            <section class="card card--home">
                <h3 class="card__heading card__heading--small text-orange">Grupos Disponíveis</h3>
                <p class="card__text card__text--left card__text--secondary">Junte-se a grupos da sua organização.</p>
                <div class="trip-list" style="margin-top: 15px;">
                    @forelse($gruposDisponiveis as $grupo)
                        <div class="trip-card">
                            <div class="trip-card__header">
                                <span class="trip-card__time text-text-muted">{{ $grupo->frequenciaFormatada() }}</span>
                                <span class="badge badge--blue">{{ $grupo->vagas - $grupo->passageiros_count }} Vagas Livres</span>
                            </div>
                            <div class="trip-card__path">
                                <span class="trip-card__location" style="font-weight:bold;">{{ $grupo->nome }}</span>
                            </div>
                            <div class="trip-card__actions" style="margin-top:10px; display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:14px; color:#6b7280;">{{ $grupo->motorista->user->name }}</span>

                                <form action="{{ route('grupos.entrar', $grupo->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn--green" style="padding: 6px 12px; font-size:14px;">
                                        Participar
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state-wrapper empty-state-wrapper--compact">
                            <div class="card card--empty">
                                <p>Nenhum grupo com vagas disponível no momento.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </section>
        @endif

    </x-slot:content>
</x-home-layout>
