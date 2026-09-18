<x-layout>
    <x-slot:title>Verificação em duas etapas</x-slot:title>
    <x-slot:content>
        <div class="center-wrapper bg-grafismo">
            <div class="card card--auth">
                <div class="brand-stripe brand-stripe--top"></div>

                <header class="card__header">
                    <h1 class="card__heading text-blue">Verificação em duas etapas</h1>
                    <p class="card__subtitle text-green">Informe o código do aplicativo autenticador ou de recuperação.</p>
                </header>

                <form method="POST" action="{{ route('two-factor.login') }}" class="form">
                    @csrf

                    <div class="field">
                        <label for="code">Código</label>
                        <div class="field__input-wrapper">
                            <input type="text" id="code" name="code" inputmode="numeric" autocomplete="one-time-code">
                        </div>
                    </div>

                    <div class="field">
                        <label for="recovery_code">Código de recuperação</label>
                        <div class="field__input-wrapper">
                            <input type="text" id="recovery_code" name="recovery_code">
                        </div>
                    </div>

                    @error('code')
                        <span class="field__error">{{ $message }}</span>
                    @enderror

                    @error('recovery_code')
                        <span class="field__error">{{ $message }}</span>
                    @enderror

                    <button type="submit" class="btn btn--orange btn--submit">Validar</button>
                </form>

                <div class="brand-stripe brand-stripe--bottom"></div>
            </div>
        </div>
    </x-slot:content>
</x-layout>
