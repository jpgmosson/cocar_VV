<x-dashboard-layout>
    <x-slot:title>Configurações Organização</x-slot:title>
    <x-slot:content>
        @php
            $user = auth()->user();
            $organizacao = $user->organizacao;
        @endphp
        <section class="card card--settings">
            <div class="card__header--left">
                <h2 class="card__heading card__heading--small text-blue">Dados da Organização</h2>
                <p class="card__text card__text--secondary">Informações de registro da sua empresa na CoCar.</p>
            </div>
            <form class="form form--split" action="{{ route('organizacoes.alterar', ['organizacao' => $organizacao]) }}"
                method="post">
                @csrf
                @method('PUT')
                <div class="field">
                    <label class="field__label">Nome da Organização</label>
                    <div class="field__input-wrapper">
                        <input type="text" value="{{ $organizacao->nome }}" name="nome" required>
                    </div>
                </div>
                <div class="field">
                    <label class="field__label">Domínio de E-mail</label>
                    <div class="field__input-wrapper">
                        <input type="text" value="{{ $organizacao->dominio_email }}" disabled>
                    </div>
                    <p class="field__help">Utilizado para validar a entrada de novos colaboradores.</p>
                </div>
                <div class="field">
                    <label class="field__label">CNPJ</label>
                    <div class="field__input-wrapper">
                        <x-masked-input mask="cnpj" name="cnpj" value="{{ $organizacao->cnpj }}" disabled />
                    </div>
                </div>
                <button type="submit" class="btn btn--blue btn--submit form__submit-full">Salvar
                    Alterações</button>
            </form>
        </section>

        <section class="card card--settings">
            <div class="card__header--left">
                <h2 class="card__heading card__heading--small text-blue">Administrador Responsável</h2>
                <p class="card__text card__text--secondary">O ponto de contato legal e técnico da organização.</p>
            </div>
            <form class="form form--split" action="{{ route('user-profile-information.update') }}" method="post">
                @csrf
                @method('PUT')
                <div class="field">
                    <label class="field__label">Nome Completo</label>
                    <div class="field__input-wrapper">
                        <input type="text" value="{{ $user->name }}" name="name" required>
                    </div>
                </div>
                <div class="field">
                    <label class="field__label">E-mail do Responsável</label>
                    <div class="field__input-wrapper">
                        <input type="email" value="{{ $user->email }}" name="email" required>
                    </div>
                </div>
                <div class="field">
                    <label class="field__label">CPF</label>
                    <div class="field__input-wrapper">
                        <x-masked-input mask="cpf" value="{{ $user->cpf }}" name="cpf" disabled />
                    </div>
                </div>
                <button type="submit" class="btn btn--blue btn--submit form__submit-full">Atualizar
                    Administrador</button>
            </form>
        </section>

        <section class="card card--settings">
            <div class="card__header--left">
                <h2 class="card__heading card__heading--small text-blue">Senha de Acesso</h2>
                <p class="card__text card__text--secondary">A senha utilizada para ter acessor ao paínel do
                    Administrador</p>
            </div>
            <form class="form form--split" action="{{ route('user-password.update') }}" method="post">
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
                <button type="submit" class="btn btn--blue btn--submit form__submit-full">Atualizar Senha</button>
            </form>
        </section>


        <section class="card card--settings card--settings-action card--danger">
            <div class="card__header--left">
                <h2 class="card__heading card__heading--small text-alert">Encerrar Organização</h2>
                <p class="card__text card__text--secondary card__text--left">
                    Ao excluir a organização, todas as contas de funcionários vinculadas perderão o acesso
                    corporativo.
                    O
                    histórico de caronas e o impacto de CO₂ coletivo serão permanentemente apagados. Esta ação é
                    irreversível.
                </p>
            </div>
            <form action="{{ route('admin.meu-cadastro.deletar') }}" method="POST" class="action-form">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn--red">Excluir Permanentemente</button>
            </form>
        </section>
    </x-slot:content>
</x-dashboard-layout>
