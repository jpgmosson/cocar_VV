<x-dashboard-layout>
    <x-slot:title>Triagem Motoristas</x-slot:title>
    <x-slot:content>
        @if ($motoristasPendentes->isEmpty())
            <div class="empty-state-wrapper">
                <div class="card card--empty">
                    <div class="card__icon text-green">
                        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10s10-4.5 10-10S17.5 2 12 2m-2 15l-5-5l1.41-1.41L10 14.17l7.59-7.59L19 8z" />
                        </svg>
                    </div>
                    <h2 class="card__heading card__heading--small text-blue">Tudo certo por aqui!</h2>
                    <p class="card__text">Não há motoristas aguardando aprovação no momento.</p>
                </div>
            </div>
        @else
            <div class="cards-grid">
                @foreach ($motoristasPendentes as $motorista)
                    <div class="card card--stretch">
                        <div class="card__header card__header--left">
                            <h2 class="card__heading card__heading--small text-blue">{{ $motorista->user->name }}</h2>
                        </div>

                        <div class="data-list">
                            <div class="data-list__item">
                                <span class="data-list__label">Email</span>
                                <span class="data-list__value">{{ $motorista->user->email }}</span>
                            </div>
                            <div class="data-list__item">
                                <span class="data-list__label">CPF</span>
                                <span class="data-list__value">{{ $motorista->user->cpf }}</span>
                            </div>
                            <div class="data-list__item">
                                <span class="data-list__label">CNH</span>
                                <span class="data-list__value">{{ $motorista->cnh }}</span>
                            </div>
                        </div>

                        <div class="card__actions">
                            <form action="{{ route('triagem-motoristas.rejeitar', ['perfilMotorista' => $motorista]) }}"
                                method="POST" class="action-form">
                                @csrf
                                <button type="submit" class="btn btn--outline btn--red">Rejeitar</button>
                            </form>
                            <form action="{{ route('triagem-motoristas.aprovar', ['perfilMotorista' => $motorista]) }}"
                                method="POST" class="action-form">
                                @csrf
                                <button type="submit" class="btn btn--green">Aprovar</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-slot:content>
</x-dashboard-layout>
