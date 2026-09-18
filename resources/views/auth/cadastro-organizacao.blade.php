<x-layout>
    <x-slot:title>Cadastro Organização</x-slot:title>
    <x-slot:content>
        <div class="center-wrapper bg-grafismo">
            <div class="card card--register">
                <div class="brand-stripe brand-stripe--top"></div>

                <header class="card__header">
                    <div class="logo text-blue">
                        <img src="{{ asset('favicons/favicon.svg') }}" alt="logo CoCar">
                    </div>
                    <h1 class="card__heading text-blue">
                        Traga sua <span class="text-orange">Organização</span> para a <span
                            class="text-green">CoCar</span>
                    </h1>
                    <p class="card__subtitle card__text--secondary">
                        Fortaleça nossa tribo urbana. Menos trânsito, mais conexão e redução de emissões.
                    </p>
                </header>

                <form action="{{ route('organizacao.criar') }}" method="post" class="form form--split" id="formulario">
                    @csrf

                    <fieldset class="form__fieldset">
                        <div class="field">
                            <label for="organizacao-nome">Nome da Organização</label>
                            <div class="field__input-wrapper">
                                <div class="field__input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24">
                                        <path fill="currentColor"
                                            d="M18 15h-2v2h2m0-6h-2v2h2m2 6h-8v-2h2v-2h-2v-2h2v-2h-2V9h8M10 7H8V5h2m0 6H8V9h2m0 6H8v-2h2m0 6H8v-2h2M6 7H4V5h2m0 6H4V9h2m0 6H4v-2h2m0 6H4v-2h2m6-10V3H2v18h20V7z" />
                                    </svg>
                                </div>
                                <input type="text" name="organizacao-nome" id="organizacao-nome"
                                    value="{{ old('organizacao-nome') }}" placeholder="Nome da sua organização"
                                    required>
                            </div>
                            @error('organizacao-nome')
                                <span class="field__error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="cnpj">CNPJ</label>
                            <div class="field__input-wrapper">
                                <div class="field__input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24">
                                        <path fill="currentColor"
                                            d="M12.025 4.475q2.65 0 5 1.138T20.95 8.9q.175.225.113.4t-.213.3t-.35.113t-.35-.213q-1.375-1.95-3.537-2.987t-4.588-1.038t-4.55 1.038T3.95 9.5q-.15.225-.35.25t-.35-.1q-.175-.125-.213-.312t.113-.388q1.55-2.125 3.888-3.3t4.987-1.175m0 2.35q3.375 0 5.8 2.25t2.425 5.575q0 1.25-.887 2.088t-2.163.837t-2.187-.837t-.913-2.088q0-.825-.612-1.388t-1.463-.562t-1.463.563t-.612 1.387q0 2.425 1.438 4.05t3.712 2.275q.225.075.3.25t.025.375q-.05.175-.2.3t-.375.075q-2.6-.65-4.25-2.588T8.95 14.65q0-1.25.9-2.1t2.175-.85t2.175.85t.9 2.1q0 .825.625 1.388t1.475.562t1.45-.562t.6-1.388q0-2.9-2.125-4.875T12.05 7.8T6.975 9.775t-2.125 4.85q0 .6.113 1.5t.537 2.1q.075.225-.012.4t-.288.25t-.387-.012t-.263-.288q-.375-.975-.537-1.937T3.85 14.65q0-3.325 2.413-5.575t5.762-2.25m0-4.8q1.6 0 3.125.387t2.95 1.113q.225.125.263.3t-.038.35t-.25.275t-.425-.025q-1.325-.675-2.738-1.037t-2.887-.363q-1.45 0-2.85.338T6.5 4.425q-.2.125-.4.063t-.3-.263t-.05-.362t.25-.288q1.4-.75 2.925-1.15t3.1-.4m0 7.225q2.325 0 4 1.563T17.7 14.65q0 .225-.137.363t-.363.137q-.2 0-.35-.137t-.15-.363q0-1.875-1.388-3.137t-3.287-1.263t-3.262 1.263T7.4 14.65q0 2.025.7 3.438t2.05 2.837q.15.15.15.35t-.15.35t-.35.15t-.35-.15q-1.475-1.55-2.262-3.162T6.4 14.65q0-2.275 1.65-3.838t3.975-1.562M12 14.15q.225 0 .363.15t.137.35q0 1.875 1.35 3.075t3.15 1.2q.15 0 .425-.025t.575-.075q.225-.05.388.063t.212.337q.05.2-.075.35t-.325.2q-.45.125-.787.138t-.413.012q-2.225 0-3.863-1.5T11.5 14.65q0-.2.138-.35t.362-.15" />
                                    </svg>
                                </div>
                                <x-masked-input mask="cnpj" name="cnpj" id="cnpj" value="{{ old('cnpj') }}"
                                    placeholder="CNPJ da sua organização" required />
                            </div>
                            @error('cnpj')
                                <span class="field__error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="dominio-email">Domínio de e-mail</label>
                            <div class="field__input-wrapper">
                                <div class="field__input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24">
                                        <g fill="none" stroke="currentColor" stroke-linecap="round"
                                            stroke-linejoin="round" stroke-width="1.5">
                                            <path
                                                d="M3.338 17A10 10 0 0 0 12 22a10 10 0 0 0 8.662-5M3.338 7A10 10 0 0 1 12 2a10 10 0 0 1 8.662 5" />
                                            <path
                                                d="M13 21.95s1.408-1.853 2.295-4.95M13 2.05S14.408 3.902 15.295 7M11 21.95S9.592 20.098 8.705 17M11 2.05S9.592 3.902 8.705 7M9 10l1.5 5l1.5-5l1.5 5l1.5-5M1 10l1.5 5L4 10l1.5 5L7 10m10 0l1.5 5l1.5-5l1.5 5l1.5-5" />
                                        </g>
                                    </svg>
                                </div>
                                <input type="text" id="dominio-email" name="dominio_email"
                                    value="{{ old('dominio_email') }}" placeholder="organizacao.com.br" required>
                            </div>
                            @error('dominio-email')
                                <span class="field__error">{{ $message }}</span>
                            @enderror
                        </div>
                    </fieldset>

                    <fieldset class="form__fieldset">
                        <div class="field">
                            <label for="administrador-nome">Nome do responsável</label>
                            <div class="field__input-wrapper">
                                <div class="field__input-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <input type="text" id="administrador-nome" name="administrador-nome"
                                    value="{{ old('administrador-nome') }}" placeholder="Nome do responsável" required>
                            </div>
                            @error('administrador-nome')
                                <span class="field__error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="administrador-email">E-mail do responsável</label>
                            <div class="field__input-wrapper">
                                <div class="field__input-icon">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                    </svg>
                                </div>
                                <input type="email" id="administrador-email" name="administrador-email"
                                    value="{{ old('administrador-email') }}"
                                    placeholder="responsavel@organizacao.com.br" required>
                            </div>
                            @error('administrador-email')
                                <span class="field__error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="administrador-cpf">CPF Responsável</label>
                            <div class="field__input-wrapper">
                                <div class="field__input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24">
                                        <path fill="currentColor"
                                            d="M22 3H2c-1.09.04-1.96.91-2 2v14c.04 1.09.91 1.96 2 2h20c1.09-.04 1.96-.91 2-2V5a2.074 2.074 0 0 0-2-2m0 16H2V5h20zm-8-2v-1.25c0-1.66-3.34-2.5-5-2.5s-5 .84-5 2.5V17zM9 7a2.5 2.5 0 0 0-2.5 2.5A2.5 2.5 0 0 0 9 12a2.5 2.5 0 0 0 2.5-2.5A2.5 2.5 0 0 0 9 7m5 0v1h6V7zm0 2v1h6V9zm0 2v1h4v-1z" />
                                    </svg>
                                </div>
                                <x-masked-input id="administrador-cpf" name="administrador-cpf"
                                    value="{{ old('administrador-cpf') }}" mask="cpf"
                                    placeholder="CPF do responsável" required />
                            </div>
                            @error('administrador-cpf')
                                <span class="field__error">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="password">Senha do responsável</label>
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
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    required placeholder="Repita a senha">
                            </div>
                        </div>

                        <div id="inner"></div>
                    </fieldset>

                    <button class="btn btn--orange form__submit-full" type="submit">Cadastrar</button>
                </form>

                <div class="brand-stripe brand-stripe--bottom"></div>
            </div>
        </div>
    </x-slot:content>
</x-layout>
