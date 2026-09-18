<x-layout>
    <x-slot:title>Redefinir senha</x-slot:title>
    <x-slot:content>
        <div class="center-wrapper bg-grafismo">
            <div class="card card--auth">
                <div class="brand-stripe brand-stripe--top"></div>

                <header class="card__header">
                    <h1 class="card__heading text-blue">Redefinir senha</h1>
                </header>

                <form method="POST" action="{{ route('password.update') }}" class="form">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <div class="field">
                        <label for="email">E-mail</label>
                        <div class="field__input-wrapper">
                            <input type="email" id="email" name="email" value="{{ old('email', $request->email) }}"
                                required autofocus>
                        </div>
                        @error('email')
                            <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">Nova senha</label>
                        <div class="field__input-wrapper">
                            <input type="password" id="password" name="password" required>
                        </div>
                        @error('password')
                            <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirmar senha</label>
                        <div class="field__input-wrapper">
                            <input type="password" id="password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn--orange btn--submit">Redefinir senha</button>
                </form>

                <div class="brand-stripe brand-stripe--bottom"></div>
            </div>
        </div>
    </x-slot:content>
</x-layout>
