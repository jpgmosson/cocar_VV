<x-layout>
    <x-slot:title>Carteira</x-slot:title>
    <x-slot:body>
        <div class="app-layout bg-grafismo">
            <main class="app-layout__content center-wrapper">

                <div class="card card--wallet">
                    <div class="brand-stripe brand-stripe--top"></div>

                    <a href="{{ route('home') }}" class="btn btn--orange" style="margin-right: auto"><svg
                            xmlns="http://www.w3.org/2000/svg" width="40" height="24" viewBox="0 0 1024 1024">
                            <path fill="currentColor" d="M224 480h640a32 32 0 1 1 0 64H224a32 32 0 0 1 0-64" />
                            <path fill="currentColor"
                                d="m237.2 512l265.5 265.3a32 32 0 0 1-45.4 45.4l-288-288a32 32 0 0 1 0-45.4l288-288a32 32 0 1 1 45.4 45.4z" />
                        </svg></a>

                    <div class="card__header card__header--wallet">
                        <h1 class="card__heading card__heading--small text-blue">Saldo Atual</h1>
                        <div class="stats-grid__value text-green wallet__balance">R$ {{ $carteira->saldo }}</div>
                    </div>

                    <form action="{{ route('carteira.depositar') }}" method="post" class="form">
                        @csrf
                        @method('PATCH')
                        <h2 class="wallet__section-title">Adicionar Saldo</h2>

                        <div class="wallet__presets" id="valores-preset">
                            <button type="button" class="btn btn--outline btn--green" data-valor = '10'>R$ 10</button>
                            <button type="button" class="btn btn--outline btn--green" data-valor = '25'>R$ 25</button>
                            <button type="button" class="btn btn--outline btn--green" data-valor = '50'>R$ 50</button>
                        </div>

                        <div class="stripe-container">
                            <span>ou</span>
                        </div>

                        <div class="field">
                            <div class="field__input-wrapper">
                                <div class="field__input-icon field__input-icon--text">R$</div>
                                <input type="number" name="valor" id="valor-input" step="0.01" min="0.01"
                                    placeholder="Valor">
                            </div>
                        </div>

                        <button type="submit" class="btn btn--blue btn--submit">Inserir Saldo</button>
                    </form>
                    <div class="brand-stripe brand-stripe--bottom"></div>
                </div>

            </main>

        </div>
        <script>
            const valoresPreset = document.getElementById('valores-preset');
            const valorInput = document.getElementById('valor-input');

            valoresPreset.addEventListener('click', (event) => {
                const botao = event.target.closest('button')
                const valorAtual = parseFloat(valorInput.value) || 0;
                valorInput.value = valorAtual + parseFloat(botao.dataset.valor);
            });
        </script>
    </x-slot:body>
</x-layout>
