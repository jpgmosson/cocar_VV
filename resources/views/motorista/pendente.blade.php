<x-home-layout mode="motorista">
    <x-slot:title>Aprovação Pendente</x-slot:title>
    <x-slot:content>
        <div class="status-wrapper">
            <div class="card">
                <div class="card__header">
                    <div class="status-icon text-orange">
                        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8s8 3.58 8 8s-3.58 8-8 8zm.5-13H11v6l5.25 3.15l.75-1.23l-4.5-2.67z" />
                        </svg>
                    </div>
                    <h2 class="card__heading card__heading--small text-blue">Cadastro em Análise</h2>
                    <p class="card__text">Recebemos seus dados! Nossa equipe está avaliando seu perfil de motorista.
                        Esse processo pode levar algumas horas.</p>
                    <p class="card__text card__text--secondary">Enquanto isso, você pode continuar aproveitando o
                        aplicativo no modo Passageiro.</p>
                </div>
            </div>
        </div>
    </x-slot:content>
</x-home-layout>
