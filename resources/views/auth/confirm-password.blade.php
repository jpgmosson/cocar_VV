<x-layout>
    <x-slot:title>Confirmar senha</x-slot:title>
    <x-slot:content>
        <div class="center-wrapper bg-grafismo">
            <div class="card card--auth">
                <div class="brand-stripe brand-stripe--top"></div>

                <header class="card__header">
                    <h1 class="card__heading text-blue">Confirmar senha</h1>
                    <p class="card__subtitle text-green">Digite sua senha para continuar.</p>
                </header>

                <form method="POST" action="{{ route('password.confirm') }}" class="form">
                    @csrf

                    <div class="field">
                        <label for="password">Senha</label>
                        <div class="field__input-wrapper">
                            <input type="password" id="password" name="password" required autofocus>
                        </div>
                        @error('password')
                            <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn--orange btn--submit">Confirmar</button>
                </form>

                <div class="brand-stripe brand-stripe--bottom"></div>
            </div>
        </div>
    </x-slot:content>
</x-layout>
