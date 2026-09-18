<x-home-layout mode="motorista">
    <x-slot:title>Cadastro Motorista</x-slot:title>
    <x-slot:content>
        <div class="status-wrapper">
            <div class="card driver-onboarding">
                <div class="card__header driver-onboarding__header">
                    <div class="driver-onboarding__icon text-blue">
                        <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.85 7h10.28l1.08 3.11H5.77L6.85 7zM19 17H5v-5h14v5z" />
                            <circle cx="7.5" cy="14.5" r="1.5" fill="currentColor" />
                            <circle cx="16.5" cy="14.5" r="1.5" fill="currentColor" />
                        </svg>
                    </div>
                    <h2 class="card__heading card__heading--small text-blue">Seja um Motorista</h2>
                    <p class="card__text">Sua conta já está ativa para uso como passageiro! Para começar a dirigir,
                        precisamos de algumas informações adicionais.</p>
                </div>

                <form action="{{ route('motorista.cadastro') }}" method="POST" class="form driver-onboarding__form">
                    @csrf
                    <div class="form__fields">
                        <div class="field">
                            <label class="field__label" for="cnh">Número da CNH</label>
                            <div class="field__input-wrapper">
                                <x-masked-input id="cnh" name="cnh" mask="cnh" required />
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn--blue btn--submit">Enviar para Avaliação</button>
                </form>
            </div>
        </div>
    </x-slot:content>
</x-home-layout>
