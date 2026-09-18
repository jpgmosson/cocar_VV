<x-dashboard-layout>
    <x-slot:title>Dashboard</x-slot:title>
    <x-slot:content>
        <div class="card card--dashboard">
            <div class="card__header">
                <h1 class="card__heading text-blue">{{ $organizacao->nome }}</h1>
                <p class="card__text">CNPJ: {{ $organizacao->cnpj }}</p>
            </div>

            <div class="stats-grid">
                <div class="stats-grid__item">
                    <span class="stats-grid__value text-blue">{{ $totalUsuarios }}</span>
                    <span class="stats-grid__label">Total de Usuários Cadastrados</span>
                </div>
                <div class="stats-grid__item">
                    <span class="stats-grid__value text-green">{{ $totalMotoristasAtivos }}</span>
                    <span class="stats-grid__label">Motoristas ativos</span>
                </div>
                <div class="stats-grid__item">
                    <span class="stats-grid__value text-orange">{{ $totalMotoristasPendentes }}</span>
                    <span class="stats-grid__label">Motorista pendentes</span>
                </div>
                <div class="stats-grid__item">
                    <span class="stats-grid__value text-orange">{{ $caronasRealizadas }}</span>
                    <span class="stats-grid__label">Caronas Realizadas</span>
                </div>
                <div class="stats-grid__item">
                    <span class="stats-grid__value text-purple">{{ $distanciaTotal }}km</span>
                    <span class="stats-grid__label">Distância Compartilhada</span>
                </div>
                <div class="stats-grid__item">
                    <span class="stats-grid__value text-green">{{ $emissoesEvitadas }}kg</span>
                    <span class="stats-grid__label">CO₂ Evitado</span>
                </div>
            </div>
    </x-slot:content>
</x-dashboard-layout>
