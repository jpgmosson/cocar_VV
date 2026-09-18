<x-layout>
    <x-slot:title>{{ $title ?? 'CoCar' }}</x-slot:title>
    <x-slot:body>
        <div class="app-layout bg-grafismo">
            <main class="app-layout__content">
                {{ $content }}
            </main>

            <nav class="bottom-nav">
                <ul class="bottom-nav__list">
                    <li class="bottom-nav__item">
                        <a href="{{ route('home') }}"
                            class="bottom-nav__link {{ request()->RouteIs(['home', 'motorista.home']) ? 'bottom-nav__link--active' : '' }}">
                            <div class="bottom-nav__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                                    <path fill="currentColor" d="M10 20v-6h4v6h5v-8h3L12 3L2 12h3v8z" />
                                </svg>
                            </div>
                            <span class="bottom-nav__text">Home</span>
                        </a>
                    </li>
                    <li class="bottom-nav__item">
                        <a href="{{ route('grupos.index') }}"
                            class="bottom-nav__link {{ request()->routeIs('grupos.index') ? 'bottom-nav__link--active' : '' }}">
                            <div class="bottom-nav__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24">
                                    <path fill="currentColor"
                                        d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05c1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                                </svg>
                            </div>
                            <span class="bottom-nav__text">Grupos</span>
                        </a>
                    </li>
                    <li class="bottom-nav__item">
                        <a href="{{ route('transacao.index') }}"
                            class="bottom-nav__link {{ request()->is('atividade*') ? 'bottom-nav__link--active' : '' }}">
                            <div class="bottom-nav__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24">
                                    <path fill="currentColor"
                                        d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8s8 3.58 8 8s-3.58 8-8 8zm.5-13H11v6l5.25 3.15l.75-1.23l-4.5-2.67z" />
                                </svg>
                            </div>
                            <span class="bottom-nav__text">Atividade</span>
                        </a>
                    </li>
                    <li class="bottom-nav__item">
                        <a href="{{ route('usuario.perfil') }}"
                            class="bottom-nav__link {{ request()->is('perfil*') ? 'bottom-nav__link--active' : '' }}">
                            <div class="bottom-nav__icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24">
                                    <path fill="currentColor"
                                        d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                                </svg>
                            </div>
                            <span class="bottom-nav__text">Perfil</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <x-viagem-ativa />
    </x-slot:body>
</x-layout>
