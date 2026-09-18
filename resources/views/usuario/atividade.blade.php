@use('App\Enums\TipoTransacao')
@use('App\Enums\DirecaoTransacao')
@use('App\Enums\FaseCarona')
@use('App\Enums\ContextoTransacao')
<x-app-layout>
    <x-slot:title>Atividade</x-slot>
    <x-slot:content>
        <div class="home-content">
            <h1 class="card__heading card__heading--small">Histórico</h1>
            <div class="tx-list">
                <div class="wallet-panel">
                    <details class="wallet-deposit">
                        <summary class="wallet-panel__top">
                            <div class="wallet-summary">
                                <div class="wallet-summary__icon text-blue">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                        <path fill="currentColor"
                                            d="M95.5 104h320a88 88 0 0 1 11.18.71a66 66 0 0 0-77.51-55.56L86 94.08h-.3a66 66 0 0 0-41.07 26.13A87.57 87.57 0 0 1 95.5 104m320 24h-320a64.07 64.07 0 0 0-64 64v192a64.07 64.07 0 0 0 64 64h320a64.07 64.07 0 0 0 64-64V192a64.07 64.07 0 0 0-64-64M368 320a32 32 0 1 1 32-32a32 32 0 0 1-32 32" />
                                        <path fill="currentColor"
                                            d="M32 259.5V160c0-21.67 12-58 53.65-65.87C121 87.5 156 87.5 156 87.5s23 16 4 16s-18.5 24.5 0 24.5s0 23.5 0 23.5L85.5 236Z" />
                                    </svg>
                                </div>
                                <div class="wallet-summary__text">
                                    <span class="wallet-summary__title">Saldo Atual</span>
                                    <span class="wallet-summary__value text-green">R$ {{ $carteira->saldo }}</span>
                                </div>
                            </div>
                            <div class="btn btn--outline btn--blue wallet-deposit__toggle">Adicionar Saldo</div>
                        </summary>

                        <form action="{{ route('carteira.depositar') }}" method="post" class="wallet-deposit__form">
                            @csrf
                            @method('PATCH')
                            <h2 class="wallet__section-title">Valor do Depósito</h2>

                            <div class="wallet__presets" id="valores-preset">
                                <button type="button" class="btn btn--outline btn--green" data-valor="10">R$
                                    10</button>
                                <button type="button" class="btn btn--outline btn--green" data-valor="25">R$
                                    25</button>
                                <button type="button" class="btn btn--outline btn--green" data-valor="50">R$
                                    50</button>
                            </div>

                            <div class="stripe-container">
                                <span>ou</span>
                            </div>

                            <div class="field">
                                <div class="field__input-wrapper">
                                    <div class="field__input-icon field__input-icon--text">R$</div>
                                    <input type="number" name="valor" id="valor-input" step="0.01" min="0.01"
                                        placeholder="Valor customizado">
                                </div>
                            </div>

                            <button type="submit" class="btn btn--blue btn--submit">Confirmar Pagamento</button>
                        </form>
                    </details>
                </div>

                @foreach ($transacoes as $transacao)
                    <div class="tx-item">
                        @if (!$loop->first)
                            <div class="tx-balance">Saldo atual: R$ {{ $transacao->saldo_historico }}</div>
                        @endif
                        <details class="tx-card">
                            <summary class="tx-card__summary">
                                <div class="tx-card__main">
                                    <div @class([
                                        'tx-card__icon',
                                        'tx-card__icon--green' => $transacao->tipo === TipoTransacao::DEPOSITO,
                                        'tx-card__icon--orange' => $transacao->tipo === TipoTransacao::RETENCAO,
                                        'tx-card__icon--gray' => $transacao->tipo === TipoTransacao::LIQUIDACAO,
                                        'tx-card__icon--blue' => $transacao->tipo === TipoTransacao::AJUDA_CUSTO,
                                        'tx-card__icon--purple' => $transacao->tipo === TipoTransacao::ESTORNO,
                                    ])>
                                        @if ($transacao->tipo == TipoTransacao::DEPOSITO)
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                <path fill="currentColor"
                                                    d="M11 20V7.825l-5.6 5.6L4 12l8-8l8 8l-1.4 1.425l-5.6-5.6V20z" />
                                            </svg>
                                        @elseif ($transacao->tipo == TipoTransacao::RETENCAO)
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                <path fill="currentColor"
                                                    d="M12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m.275-3q.425 0 .713-.288T13 16v-3.3l2.8-2.8q.275-.275.275-.7t-.275-.7t-.7-.275t-.7.275L11.3 11.6q-.15.15-.225.338T11 12.35v3.65q0 .425.288.713t.712.287M12 12" />
                                            </svg>
                                        @elseif ($transacao->tipo == TipoTransacao::LIQUIDACAO)
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                <path fill="currentColor"
                                                    d="M21 7L9 19l-5.5-5.5l1.41-1.41L9 16.17L19.59 5.59z" />
                                            </svg>
                                        @elseif ($transacao->tipo == TipoTransacao::AJUDA_CUSTO)
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                <path fill="currentColor"
                                                    d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16m11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5s1.5.67 1.5 1.5s-.67 1.5-1.5 1.5M5 11l1.5-4.5h11L19 11z" />
                                            </svg>
                                        @elseif ($transacao->tipo == TipoTransacao::ESTORNO)
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                                                <path fill="currentColor"
                                                    d="M19 7v4H7.83l3.59-3.59L10 6l-6 6l6 6l1.41-1.41L7.83 13H21V7z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="tx-card__info">
                                        <span class="tx-card__title">{{ $transacao->tipo->label() }}</span>
                                        <span
                                            class="tx-card__date">{{ $transacao->updated_at->translatedFormat('d M Y, H:i') }}</span>
                                    </div>
                                </div>
                                <div class="tx-card__meta">
                                    <span @class([
                                        'tx-card__value',
                                        'text-green' => $transacao->tipo->direcao() === DirecaoTransacao::ENTRADA,
                                        'text-orange' => $transacao->tipo->direcao() === DirecaoTransacao::SAIDA,
                                    ])>
                                        @if ($transacao->tipo->direcao() == DirecaoTransacao::ENTRADA)
                                            +
                                        @elseif ($transacao->tipo->direcao() == DirecaoTransacao::SAIDA)
                                            -
                                        @endif
                                        R$ {{ $transacao->valor }}
                                    </span>
                                    <span class="badge badge--green">{{ $transacao->status }}</span>
                                </div>
                            </summary>
                            <div class="tx-card__body">
                                <div class="tx-card__details-grid">
                                    @if ($transacao->tipo->contexto() == ContextoTransacao::NENHUM)
                                        <div class="tx-card__detail-item">
                                            <span class="tx-card__detail-label">Método</span>
                                            <span class="tx-card__detail-value">PIX / Transferência</span>
                                        </div>
                                        <div class="tx-card__detail-item">
                                            <span class="tx-card__detail-label">ID Transação</span>
                                            <span class="tx-card__detail-value">#992831</span>
                                        </div>
                                    @elseif ($transacao->tipo->contexto() == ContextoTransacao::PEDIDO_CARONA)
                                        <div class="tx-card__detail-item">
                                            <span class="tx-card__detail-label">Origem</span>
                                            <span
                                                class="tx-card__detail-value">{{ $transacao->pedidoCarona->origem_endereco }}</span>
                                        </div>
                                        <div class="tx-card__detail-item">
                                            <span class="tx-card__detail-label">Destino</span>
                                            <span
                                                class="tx-card__detail-value">{{ $transacao->pedidoCarona->destino_endereco }}</span>
                                        </div>
                                        <div class="tx-card__detail-item">
                                            <span class="tx-card__detail-label">Status Pedido</span>
                                            @php
                                                $status = $transacao->pedidoCarona->status();
                                                $fase = $status->fase();
                                            @endphp
                                            <span @class([
                                                'badge',
                                                'badge--orange' => $fase == FaseCarona::DESCOBERTA,
                                                'badge--blue' => $fase == FaseCarona::ATIVA,
                                                'badge--green' => $fase == FaseCarona::SUCESSO,
                                                'badge--red' => $fase == FaseCarona::FALHA,
                                            ])>{{ $status->label() }}</span>
                                        </div>
                                    @elseif ($transacao->tipo->contexto() == ContextoTransacao::TRAJETO)
                                        <div class="tx-card__detail-item">
                                            <span class="tx-card__detail-label">Origem do Trajeto</span>
                                            <span
                                                class="tx-card__detail-value">{{ $transacao->trajeto->origem_endereco }}</span>
                                        </div>
                                        <div class="tx-card__detail-item">
                                            <span class="tx-card__detail-label">Destino do Trajeto</span>
                                            <span
                                                class="tx-card__detail-value">{{ $transacao->trajeto->destino_endereco }}</span>
                                        </div>
                                        <div class="tx-card__detail-item">
                                            <span class="tx-card__detail-label">Distância Total</span>
                                            <span
                                                class="tx-card__detail-value">{{ $transacao->trajeto->distanciaPercorridaKM() }} KM</span>
                                        </div>
                                    @endif
                                </div>
                        </details>
                    </div>
                @endforeach
            </div>
        </div>
        <script>
            const valoresPreset = document.getElementById('valores-preset');
            const valorInput = document.getElementById('valor-input');

            if (valoresPreset && valorInput) {
                valoresPreset.addEventListener('click', (event) => {
                    const botao = event.target.closest('button')
                    if (!botao) return;
                    const valorAtual = parseFloat(valorInput.value) || 0;
                    valorInput.value = valorAtual + parseFloat(botao.dataset.valor);
                });
            }
        </script>
    </x-slot>
</x-app-layout>
