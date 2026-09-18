<x-layout>
    <x-slot:title>CoCar</x-slot:title>
    <x-slot:content>
        <div class="center-wrapper bg-grafismo">
            <div class="card">
                <div class="brand-stripe brand-stripe--top"></div>

                <header class="card__header">
                    <div class="logo">
                        <img src="{{ asset('favicons/favicon.svg') }}" alt="Logo CoCar">
                    </div>

                    <h1 class="card__heading text-blue">
                        Bem-vindo à <span class="text-orange">CoCar</span>
                    </h1>
                </header>

                <p class="card__text">
                    A plataforma de caronas da sua tribo. Conecte-se com pessoas da sua instituição, compartilhe
                    viagens, faça amigos e economize no dia a dia.
                </p>

                <div class="card__actions">
                    <a href="{{ route('login') }}" class="btn btn--blue btn--outline">
                        Fazer login
                    </a>

                    <a href="{{ route('register') }}" class="btn btn--orange">
                        Criar uma conta
                    </a>
                </div>

                <div class="stripe-container"><span>Quer adicionar a CoCar à sua organizacao?</span></div>
                <a class="text-blue" href="{{ route('cadastro-organizacao') }}">Clique aqui</a>

                <div class="brand-stripe brand-stripe--bottom"></div>
            </div>
        </div>
    </x-slot:content>
</x-layout>
