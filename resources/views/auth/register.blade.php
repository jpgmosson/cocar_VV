<x-layout>
    <x-slot:title>Cadastro</x-slot:title>
    <x-slot:content>
        <div class="center-wrapper bg-grafismo">
            <div class="card card--auth">
                <div class="brand-stripe brand-stripe--top"></div>

                <header class="card__header">
                    <div class="logo">
                        <img src="{{ asset('favicons/favicon.svg') }}" alt="Logo CoCar">
                    </div>
                    <h1 class="card__heading text-blue">Junte-se à <span class="text-orange">Tribo</span></h1>
                    <p class="card__subtitle text-green">Crie sua conta no CoCar</p>
                </header>

                <form method="POST" action="{{ route('register') }}" class="form" id="formulario">
                    @csrf

                    <div class="field">
                        <label for="name">Nome</label>
                        <div class="field__input-wrapper">
                            <div class="field__input-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                autofocus placeholder="Seu nome completo">
                        </div>
                        @error('name')
                            <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="email" id="email">E-mail</label>
                        <div class="field__input-wrapper">
                            <div class="field__input-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                </svg>
                            </div>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                placeholder="Seu e-mail da instituição parceira">
                        </div>
                        @error('email')
                            <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="cpf">CPF</label>
                        <div class="field__input-wrapper">
                            <div class="field__input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24">
                                    <path fill="currentColor"
                                        d="M12.025 4.475q2.65 0 5 1.138T20.95 8.9q.175.225.113.4t-.213.3t-.35.113t-.35-.213q-1.375-1.95-3.537-2.987t-4.588-1.038t-4.55 1.038T3.95 9.5q-.15.225-.35.25t-.35-.1q-.175-.125-.213-.312t.113-.388q1.55-2.125 3.888-3.3t4.987-1.175m0 2.35q3.375 0 5.8 2.25t2.425 5.575q0 1.25-.887 2.088t-2.163.837t-2.187-.837t-.913-2.088q0-.825-.612-1.388t-1.463-.562t-1.463.563t-.612 1.387q0 2.425 1.438 4.05t3.712 2.275q.225.075.3.25t.025.375q-.05.175-.2.3t-.375.075q-2.6-.65-4.25-2.588T8.95 14.65q0-1.25.9-2.1t2.175-.85t2.175.85t.9 2.1q0 .825.625 1.388t1.475.562t1.45-.562t.6-1.388q0-2.9-2.125-4.875T12.05 7.8T6.975 9.775t-2.125 4.85q0 .6.113 1.5t.537 2.1q.075.225-.012.4t-.288.25t-.387-.012t-.263-.288q-.375-.975-.537-1.937T3.85 14.65q0-3.325 2.413-5.575t5.762-2.25m0-4.8q1.6 0 3.125.387t2.95 1.113q.225.125.263.3t-.038.35t-.25.275t-.425-.025q-1.325-.675-2.738-1.037t-2.887-.363q-1.45 0-2.85.338T6.5 4.425q-.2.125-.4.063t-.3-.263t-.05-.362t.25-.288q1.4-.75 2.925-1.15t3.1-.4m0 7.225q2.325 0 4 1.563T17.7 14.65q0 .225-.137.363t-.363.137q-.2 0-.35-.137t-.15-.363q0-1.875-1.388-3.137t-3.287-1.263t-3.262 1.263T7.4 14.65q0 2.025.7 3.438t2.05 2.837q.15.15.15.35t-.15.35t-.35.15t-.35-.15q-1.475-1.55-2.262-3.162T6.4 14.65q0-2.275 1.65-3.838t3.975-1.562M12 14.15q.225 0 .363.15t.137.35q0 1.875 1.35 3.075t3.15 1.2q.15 0 .425-.025t.575-.075q.225-.05.388.063t.212.337q.05.2-.075.35t-.325.2q-.45.125-.787.138t-.413.012q-2.225 0-3.863-1.5T11.5 14.65q0-.2.138-.35t.362-.15" />
                                </svg>
                            </div>
                            <x-masked-input id="cpf" name="cpf" value="{{ old('cpf') }}" required
                                mask="cpf" placeholder="Seu CPF"/>
                        </div>
                        @error('cpf')
                            <span class="field__error">{{ $message }}</span>
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
                            <input type="password" id="password" name="password" required
                                placeholder="Crie uma senha forte">
                        </div>
                        @error('password')
                            <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirmar senha</label>
                        <div class="field__input-wrapper">
                            <div class="field__input-icon">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <input type="password" id="password_confirmation" name="password_confirmation" required
                                placeholder="Repita a senha">
                        </div>
                    </div>
                    <button type="submit" class="btn btn--orange btn--submit" id="btn-enviar">
                        Cadastrar
                    </button>

                </form>

                <div class="stripe-container">
                    <span>Já faz parte da nossa tribo?</span>
                </div>

                <a href="{{ route('login') }}" class="text-blue">
                    Faça login
                </a>

                <div class="brand-stripe brand-stripe--bottom"></div>
            </div>
        </div>

    </x-slot:content>

</x-layout>
