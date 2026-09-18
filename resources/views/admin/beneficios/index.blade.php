<x-dashboard-layout>
    <x-slot:title>Benefícios</x-slot:title>
    <x-slot:content>
        <div class="index-layout index-layout--single">
            <div class="index-layout__main">

                @if (session('sucesso'))
                    <div class="feedback feedback--success">
                        {{ session('sucesso') }}
                    </div>
                @endif

                <div class="card card--stretch card--flush-bottom">
                    <div class="toolbar">
                        <form action="{{ route('admin.beneficios.index') }}" class="toolbar__main field">
                            <div class="field__input-wrapper">
                                <div class="field__input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24">
                                        <path fill="currentColor"
                                            d="M15.5 14h-.79l-.28-.27A6.47 6.47 0 0 0 16 9.5A6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5S14 7.01 14 9.5S11.99 14 9.5 14z" />
                                    </svg>
                                </div>
                                <input type="text" name="pesquisa" placeholder="Buscar por nome..."
                                    value="{{ request('pesquisa') }}">
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card card--stretch card--no-pad">
                    <div class="card__header-bar">
                        <h3 class="card__heading card__heading--small text-blue">Lista de Benefícios</h3>
                        <a href="{{ route('admin.beneficios.criar') }}" class="btn btn--green btn--icon"
                            title="Novo Benefício">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                <path fill="currentColor" d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z" />
                            </svg>
                        </a>
                    </div>

                    <table class="table">
                        <thead>
                            <tr class="table__row table__row--head">
                                <th class="table__cell table__cell--head">Nome</th>
                                <th class="table__cell table__cell--head">Descrição</th>
                                <th class="table__cell table__cell--head">Meta de KM</th>
                                <th class="table__cell table__cell--head table__cell--actions">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($beneficios as $beneficio)
                                <tr class="table__row">
                                    <td class="table__cell">
                                        <a href="{{ route('admin.beneficios.editar', $beneficio->id) }}"
                                            class="table__link">
                                            {{ $beneficio->nome }}
                                        </a>
                                    </td>
                                    <td class="table__cell">{{ $beneficio->descricao }}</td>
                                    <td class="table__cell">{{ $beneficio->meta_km }} km</td>
                                    <td class="table__cell table__cell--actions">
                                        <form action="{{ route('admin.beneficios.remover', $beneficio->id) }}"
                                            method="POST" class="action-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn--red btn--small"
                                                onclick="return confirm('Tem certeza que deseja excluir este benefício?')">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr class="table__row">
                                    <td class="table__cell table__cell--empty" colspan="4">Nenhum benefício
                                        encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-slot:content>
</x-dashboard-layout>
