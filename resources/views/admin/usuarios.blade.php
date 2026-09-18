<x-dashboard-layout>
    <x-slot:title>Usuários</x-slot:title>
    <x-slot:content>
        <div class="index-layout">
            <div class="index-layout__main">
                <div class="card card--stretch card--flush-bottom">
                    <div class="field">
                        <div class="field__input-wrapper">
                            <div class="field__input-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path fill="currentColor"
                                        d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5A6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5S14 7.01 14 9.5S11.99 14 9.5 14z" />
                                </svg>
                            </div>
                            <input type="text" name="pesquisa" placeholder="Buscar por nome ou email..."
                                value="{{ request('pesquisa') }}" form="user-filter">
                        </div>
                    </div>
                </div>

                <div class="card card--stretch card--no-pad">
                    <table class="table">
                        <thead>
                            <tr class="table__row table__row--head">
                                <th class="table__cell table__cell--head">Nome</th>
                                <th class="table__cell table__cell--head">Email</th>
                                @if (auth()->user()->eAdminSistema)
                                    <th class="table__cell table__cell--head">Nome Empresa</th>
                                @else
                                    <th class="table__cell table__cell--head">CPF</th>
                                @endif
                                <th class="table__cell table__cell--head">Tipo</th>
                                <th class="table__cell table__cell--head">Status do Motorista</th>
                                <th class="table__cell table__cell--head"> Simulado </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr class="table__row">
                                    <td class="table__cell">{{ $user->name }}</td>
                                    <td class="table__cell">{{ $user->email }}</td>

                                    @if (auth()->user()->eAdminSistema)
                                        <td class="table__cell">{{ $user->organizacao->nome ?? '-' }}</td>
                                    @else
                                        <td class="table__cell">{{ $user->cpf }}</td>
                                    @endif
                                    <td class="table__cell">
                                        @if ($user->eAdminSistema)
                                            <span class="badge badge--orange">Administrador Sistema</span>
                                        @elseif ($user->eAdminOrganizacao)
                                            <span class="badge badge--green">Administrador Organização</span>
                                        @else
                                            @if ($user->e_motorista)
                                                <span class="badge badge--blue">Motorista</span>
                                            @else
                                                <span class="badge badge--gray">Passageiro</span>
                                            @endif
                                        @endif

                                    </td>
                                    <td class="table__cell">
                                        @if ($user->e_motorista)
                                            @if ($user->foi_aprovado)
                                                <span class="badge badge--green">Aprovado</span>
                                            @else
                                                <span class="badge badge--orange">Pendente</span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="table__cell">{{ $user->observacao}}</td>
                                </tr>
                            @empty
                                <tr class="table__row">
                                    <td class="table__cell table__cell--empty" colspan="5">Nenhum usuário encontrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <aside class="users-layout__aside">
                <form action="{{ route('admin.usuarios') }}" class="card card--stretch filter-panel" id="user-filter">
                    <header class="filter-panel__header">
                        <h3 class="card__heading card__heading--small text-blue">Filtros</h3>
                        <a href="{{ route('admin.usuarios') }}" class="filter-panel__clear">Limpar Tudo</a>
                    </header>

                    <div class="filter-widget">
                        <h4 class="filter-widget__title">Tipo Usuário</h4>
                        <div class="field filter-group">
                            <label class="radio-label">
                                <input type="radio" name="tipo" value="todos"
                                    {{ request('tipo', 'todos') == 'todos' ? 'checked' : '' }}>
                                <span>Todos</span>
                            </label>
                            @if (auth()->user()->eAdminSistema)
                                <label class="radio-label">
                                    <input type="radio" name="tipo" value="admin-sistema"
                                        {{ request('tipo') == 'admin-sistema' ? 'checked' : '' }}>
                                    <span>Administrador Sistema</span>
                                </label>
                            @endif
                            <label class="radio-label">
                                <input type="radio" name="tipo" value="admin-organizacao"
                                    {{ request('tipo') == 'admin-organizacao' ? 'checked' : '' }}>
                                <span>Administrador Organização</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="tipo" value="passageiro"
                                    {{ request('tipo') == 'passageiro' ? 'checked' : '' }}>
                                <span>Passageiro</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="tipo" value="motorista"
                                    {{ request('tipo') == 'motorista' ? 'checked' : '' }}>
                                <span>Motorista</span>
                            </label>
                        </div>
                    </div>

                    <div class="filter-widget">
                        <h4 class="filter-widget__title">Status Motorista</h4>
                        <div class="field filter-group">
                            <label class="radio-label">
                                <input type="radio" name="status-motorista" value="todos"
                                    {{ request('status-motorista', 'todos') == 'todos' ? 'checked' : '' }}>
                                <span>Todos</span>
                            </label>

                            <label class="radio-label">
                                <input type="radio" name="status-motorista" value="aprovado"
                                    {{ request('status-motorista') == 'aprovado' ? 'checked' : '' }}>
                                <span>Aprovado</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="status-motorista" value="pendente"
                                    {{ request('status-motorista') == 'pendente' ? 'checked' : '' }}>
                                <span>Pendente</span>
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn--blue">Aplicar Filtros</button>
                </form>

            </aside>
        </div>
    </x-slot:content>
</x-dashboard-layout>
