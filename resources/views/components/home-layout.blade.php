<x-app-layout>
    <x-slot:title>{{ $title ?? 'CoCar'}}</x-slot:title>
    <x-slot:content>
        <div class="home-layout">
            <header class="home-header">
                <div class="tab-switcher">
                    <a href="{{ route('home') }}"
                        class="tab-switcher__tab {{ $mode === 'passageiro' ? 'tab-switcher__tab--active' : '' }}">
                        Passageiro
                    </a>
                    <a href="{{ route('motorista.home') }}"
                        class="tab-switcher__tab {{ $mode === 'motorista' ? 'tab-switcher__tab--active' : '' }}">
                        Motorista
                    </a>
                </div>
            </header>

            <div class="home-content">
                {{ $content }}
            </div>
        </div>
    </x-slot:content>
</x-app-layout>
