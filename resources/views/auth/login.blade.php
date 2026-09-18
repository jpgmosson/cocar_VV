<x-layout>
    <x-slot:title>Login</x-slot:title>
    <x-slot:content>
        <div class="center-wrapper bg-grafismo">
            <div class="card card--auth">
                <div class="brand-stripe brand-stripe--top"></div>

                <header class="card__header">
                    <div class="logo">
                        <img src="{{ asset('favicons/favicon.svg') }}" alt="Logo CoCar">
                    </div>

                    <h1 class="card__heading text-blue">Co<span class="text-green">Car</span></h1>
                    <p class="card__subtitle text-green">Sua carona, nossa tribo.</p>
                </header>

                @if (session('status'))
                    <div>{{ session('status') }}</div>
                @endif

                <form action="{{ route('login') }}" method="POST" class="form">
                    @csrf

                    <div class="field">
                        <label for="email">E-mail</label>
                        <div class="field__input-wrapper">
                            <div class="field__input-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                                placeholder="cacique@tribo.com">
                        </div>
                        @error('email')
                            <p class="field__error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">Senha</label>
                        <div class="field__input-wrapper">
                            <div class="field__input-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" id="password" name="password" required placeholder="Digite sua senha">
                        </div>
                        @error('password')
                            <p class="field__error">{{ $message }}</p>
                        @enderror
                    </div>

                    <a href="{{ route('password.request') }}" class="text-orange">Esqueceu a senha?</a>

                    <button type="submit" class="btn btn--orange btn--submit">
                        Entrar na tribo
                    </button>
                </form>

                <div class="stripe-container">
                    <span>Novo por aqui?</span>
                </div>

                <a href="{{ route('register') }}" class="text-blue">
                    Criar uma conta
                </a>
                <div class="stripe-container"><span>Quer adicionar a CoCar à sua organizacao?</span></div>
                <a class="text-blue" href="{{ route('cadastro-organizacao') }}">Clique aqui</a>

                <div class="brand-stripe brand-stripe--bottom"></div>
            </div>
        </div>
    </x-slot:content>
</x-layout>
