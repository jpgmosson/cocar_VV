<x-app-layout>
    <x-slot:title>Meu Perfil</x-slot:title>
    <x-slot:content>
        <div class="settings-content">
            <section class="card card--settings">
                <div class="card__header--left">
                    <h2 class="card__heading card__heading--small text-blue">Dados Pessoais</h2>
                </div>
                <form class="form" action="{{ route('user-profile-information.update') }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="field">
                        <label class="field__label">Nome Completo</label>
                        <div class="field__input-wrapper">
                            <input type="text" value="{{ auth()->user()->name }}" name="name" required>
                        </div>
                    </div>
                    <div class="field">
                        <label class="field__label">E-mail</label>
                        <div class="field__input-wrapper">
                            <input type="email" value="{{ auth()->user()->email }}" name="email" disabled>
                        </div>
                        <p class="field__help">O e-mail é vinculado à sua rede e não pode ser alterado.
                        </p>
                    </div>

                    <div class="field">
                        <label class="field__label">CPF</label>
                        <div class="field__input-wrapper">
                            <x-masked-input value="{{ auth()->user()->cpf }}" name="cpf" mask="cpf" disabled />
                        </div>
                    </div>
                    <button type="submit" class="btn btn--blue btn--submit">Salvar Alterações</button>
                </form>
            </section>

            <section class="card card--settings">
                <div class="card__header--left">
                    <h2 class="card__heading card__heading--small text-blue">Segurança</h2>
                </div>
                <form class="form" action="{{ route('user-password.update') }}" method="post">
                    @csrf
                    @method('PUT')

                    <div class="field">
                        <label class="field__label">Senha Atual</label>
                        <div class="field__input-wrapper">
                            <input type="password" name="current_password" required>
                        </div>
                        @error('current_password', 'updatePassword')
                            <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="field__label">Nova Senha</label>
                        <div class="field__input-wrapper">
                            <input type="password" name="password" required>
                        </div>
                        @error('password', 'updatePassword')
                            <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label class="field__label">Confirmar Nova Senha</label>
                        <div class="field__input-wrapper">
                            <input type="password" name="password_confirmation" required>
                        </div>
                        @error('password_confirmation', 'updatePassword')
                            <span class="field__error">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn--blue btn--submit">Atualizar Senha</button>
                </form>
            </section>

            <section class="card card--settings card--settings-action">
                <div class="card__header--left">
                    <h2 class="card__heading card__heading--small text-blue">Sair da Conta</h2>
                    <p class="card__text card__text--secondary">Desconectar seu dispositivo atual da plataforma.</p>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="action-form">
                    <button type="submit" class="btn btn--outline btn--blue">Fazer Logout</button>
                </form>
            </section>

            <section class="card card--settings card--settings-action card--danger">
                <div class="card__header--left">
                    <h2 class="card__heading card__heading--small text-alert">Excluir Conta</h2>
                    <p class="card__text card__text--secondary card__text--left">
                        Ao excluir sua conta, todo o seu histórico de caronas, impacto de CO₂ e conexões na tribo serão
                        permanentemente apagados. Esta ação é irreversível.
                    </p>
                </div>
                <form action="{{ route('usuario.deletar') }}" method="POST" class="action-form">
                    @method('DELETE')
                    @csrf
                    <button type="submit" class="btn btn--red">Excluir Permanentemente</button>
                </form>
            </section>
        </div>
    </x-slot:content>
</x-app-layout>
