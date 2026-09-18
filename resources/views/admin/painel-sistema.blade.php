<x-dashboard-layout>
    <x-slot:title>Dashboard</x-slot:title>
    <x-slot:content>
        <div class="card card--dashboard">
            <div class="card__header">
                <h1 class="card__heading text-blue">Painel Sistema</h1>
                <p class="card__text">Lucro Diário: 99999999999999</p>
            </div>

            <div class="stats-grid">
                <div class="stats-grid__item">
                    <span class="stats-grid__value text-blue">{{ $totalOrganizacoes }}</span>
                    <span class="stats-grid__label">Total Organizações</span>
                </div>
                <div class="stats-grid__item">
                    <span class="stats-grid__value text-green">{{ $totalUsuarios }}</span>
                    <span class="stats-grid__label">Total Usuários</span>
                </div>
                <div class="stats-grid__item">
                    <span class="stats-grid__value text-orange">{{ $totalMotoristasAtivos }}</span>
                    <span class="stats-grid__label">Total Motoristas Ativos</span>
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
        </div>
    </x-slot:content>
</x-dashboard-layout>
