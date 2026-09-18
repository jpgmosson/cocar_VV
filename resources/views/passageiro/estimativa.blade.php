@if ($estimativa->saldoUsuarioSuficiente)
    <div class="cost-estimate">
        <div class="cost-estimate__info">
            <span class="cost-estimate__label">Custo Estimado</span>
            <span class="cost-estimate__range">R$ {{ $estimativa->min }} - R$
                {{ $estimativa->max }}</span>
        </div>
    </div>

    <button type="submit" class="btn btn--blue search-bar__btn">
        Confirmar Pedido
    </button>
@else
    <div class="cost-estimate cost-estimate--error">
        <div class="cost-estimate__info">
            <span class="cost-estimate__label">Saldo Insuficiente</span>
            <span class="cost-estimate__range">R$ {{ $estimativa->min }} - R$
                {{ $estimativa->max }}</span>
        </div>
    </div>

    <div class="feedback feedback--error" style="margin-bottom: 0;">
        Seu saldo atual de R$ {{ $estimativa->saldoUsuario }} não cobre o valor máximo estimado
        da
        corrida.
    </div>

    <a href="{{ route('transacao.index') }}" class="btn btn--red btn--outline search-bar__btn">
        Adicionar Saldo
    </a>
@endif
