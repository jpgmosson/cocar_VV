<x-dashboard-layout>
    <x-slot:title>{{ isset($beneficio) ? 'Editar' : 'Cadastrar' }} Benefício</x-slot:title>

    <x-slot:content>
        <div class="card card--settings">
            <header class="card__header card__header--left">
                <h2 class="card__heading card__heading--small text-blue">
                    {{ isset($beneficio) ? 'Editar Benefício' : 'Cadastrar Novo Benefício' }}
                </h2>
            </header>

            <form
                action="{{ isset($beneficio) ? route('admin.beneficios.atualizar', $beneficio->id) : route('admin.beneficios.store') }}"
                method="POST" class="form">
                @csrf
                @if (isset($beneficio))
                    @method('PUT')
                @endif

                <div class="field">
                    <label class="field__label" for="nome">Nome do Benefício (Ex: Vale Combustível R$50)</label>
                    <div class="field__input-wrapper">
                        <input type="text" name="nome" id="nome"
                            value="{{ old('nome', $beneficio->nome ?? '') }}" required>
                    </div>
                    @error('nome')
                        <span class="field__error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="meta_km">Meta de Quilometragem Acumulada (KM)</label>
                    <div class="field__input-wrapper">
                        <input type="number" step="0.01" name="meta_km" id="meta_km"
                            value="{{ old('meta_km', $beneficio->meta_km ?? '') }}" required>
                    </div>
                    @error('meta_km')
                        <span class="field__error">{{ $message }}</span>
                    @enderror
                </div>


                <div class="field">
                    <label class="field__label" for="descricao">Descrição / Regras do Prêmio</label>
                    <div class="field__input-wrapper">
                        <textarea name="descricao" id="descricao" rows="7" required>{{ old('descricao', $beneficio->descricao ?? '') }}</textarea>
                    </div>
                    @error('descricao')
                        <span class="field__error">{{ $message }}</span>
                    @enderror
                </div>


                <div class="form__actions">
                    <button type="submit" class="btn btn--green">Salvar Benefício</button>
                    <a href="{{ route('admin.beneficios.index') }}" class="btn btn--outline btn--gray">Cancelar</a>
                </div>
            </form>
        </div>
    </x-slot:content>
</x-dashboard-layout>
