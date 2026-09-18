<x-layout>
    <x-slot:title>Recuperar senha</x-slot:title>
    <x-slot:content>
        <div class="center-wrapper bg-grafismo">
            <div class="card card--auth">
                <div class="brand-stripe brand-stripe--top"></div>

                <header class="card__header">
                    <h1 class="card__heading text-blue">Recuperar senha</h1>
                    <p class="card__subtitle text-green">Informe seu e-mail para receber o link de redefinição.</p>
                </header>

                @if (session('status'))
                    <div style="color: var(--color-brand-green); margin-bottom: 20px; font-weight: bold;">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="form">
                    @csrf

                    <div class="field">
                        <label for="email">E-mail</label>
                        <div class="field__input-wrapper">
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                        </div>
                        @error('email')
                            <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn--orange btn--submit">Enviar link</button>
                </form>

                <div class="brand-stripe brand-stripe--bottom"></div>
            </div>
        </div>
    </x-slot:content>
</x-layout>
