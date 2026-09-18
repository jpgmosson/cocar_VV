<x-layout>
    <x-slot:title>{{ $title ?? 'CoCar' }}</x-slot:title>
    <x-slot:body>
        <div class="dashboard-layout">
            <nav class="sidebar">
                <a href="{{ route('admin.painel') }}" class="sidebar__header">
                    <div class="logo sidebar__logo">
                        <img src="{{ asset('favicons/favicon.svg') }}" alt="logo CoCar" />
                    </div>
                    <h1 class="sidebar__heading text-blue">
                        Co<span class="text-orange">Car</span>
                    </h1>
                </a>

                @if (auth()->user()->eAdminOrganizacao)
                    <ul class="sidebar__list">
                        <li class="sidebar__list-item">
                            <a href="{{ route('admin.triagem-motoristas') }}"
                                class="sidebar__link {{ request()->routeIs('admin.triagem-motoristas') ? 'sidebar__link--active' : '' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" class="sidebar-link__icon">
                                    <path fill="currentColor"
                                        d="M22 3H2c-1.09.04-1.96.91-2 2v14c.04 1.09.91 1.96 2 2h20c1.09-.04 1.96-.91 2-2V5a2.074 2.074 0 0 0-2-2m0 16H2V5h20zm-8-2v-1.25c0-1.66-3.34-2.5-5-2.5s-5 .84-5 2.5V17zM9 7a2.5 2.5 0 0 0-2.5 2.5A2.5 2.5 0 0 0 9 12a2.5 2.5 0 0 0 2.5-2.5A2.5 2.5 0 0 0 9 7m5 0v1h6V7zm0 2v1h6V9zm0 2v1h4v-1z" />
                                </svg>
                                <span class="sidebar-link__text">Triagem Motoristas</span>
                            </a>
                        </li>
                    @else
                        <ul class="sidebar__list">
                            <li class="sidebar__list-item">
                                <a href="{{ route('admin.organizacoes') }}"
                                    class="sidebar__link {{ request()->routeIs('admin.organizacoes') ? 'sidebar__link--active' : '' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" class="sidebar-link__icon">
                                        <path fill="currentColor"
                                            d="M12 7V3H2v18h20V7zM6 19H4v-2h2zm0-4H4v-2h2zm0-4H4V9h2zm0-4H4V5h2zm4 12H8v-2h2zm0-4H8v-2h2zm0-4H8V9h2zm0-4H8V5h2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8zm-2-8h-2v2h2zm0 4h-2v2h2z" />
                                    </svg>
                                    <span class="sidebar-link__text">Organizações</span>
                                </a>
                            </li>
                @endif
                <li class="sidebar__list-item">
                    <a href="{{ route('admin.usuarios') }}"
                        class="sidebar__link {{ request()->routeIs('admin.usuarios') ? 'sidebar__link--active' : '' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            class="sidebar-link__icon">
                            <path fill="currentColor"
                                d="M24 14.6c0 .6-1.2 1-2.6 1.2c-.9-1.7-2.7-3-4.8-3.9c.2-.3.4-.5.6-.8h.8c3.1-.1 6 1.8 6 3.5M6.8 11H6c-3.1 0-6 1.9-6 3.6c0 .6 1.2 1 2.6 1.2c.9-1.7 2.7-3 4.8-3.9zm5.2 1c2.2 0 4-1.8 4-4s-1.8-4-4-4s-4 1.8-4 4s1.8 4 4 4m0 1c-4.1 0-8 2.6-8 5c0 2 8 2 8 2s8 0 8-2c0-2.4-3.9-5-8-5m5.7-3h.3c1.7 0 3-1.3 3-3s-1.3-3-3-3c-.5 0-.9.1-1.3.3c.8 1 1.3 2.3 1.3 3.7c0 .7-.1 1.4-.3 2M6 10h.3C6.1 9.4 6 8.7 6 8c0-1.4.5-2.7 1.3-3.7C6.9 4.1 6.5 4 6 4C4.3 4 3 5.3 3 7s1.3 3 3 3" />
                        </svg>
                        <span class="sidebar-link__text">Usuários</span>
                    </a>
                </li>

                @if (auth()->user()->eAdminOrganizacao)
                    <li class="sidebar__list-item">
                        <a href="{{ route('admin.beneficios.index') }}"
                            class="sidebar__link {{ request()->routeIs('admin.beneficios') ? 'sidebar__link--active' : '' }}">

                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                class="sidebar-link__icon">
                                <path fill="currentColor"
                                    d="M13.413 10.413Q14 9.825 14 9t-.587-1.412T12 7t-1.412.588T10 9t.588 1.413T12 11t1.413-.587M7 21v-2h4v-3.1q-1.225-.275-2.187-1.037T7.4 12.95q-1.875-.225-3.137-1.637T3 8V7q0-.825.588-1.412T5 5h2V3h10v2h2q.825 0 1.413.588T21 7v1q0 1.9-1.263 3.313T16.6 12.95q-.45 1.15-1.412 1.913T13 15.9V19h4v2zm0-10.2V7H5v1q0 .95.55 1.713T7 10.8m7.125 2.325Q15 12.25 15 11V5H9v6q0 1.25.875 2.125T12 14t2.125-.875M17 10.8q.9-.325 1.45-1.088T19 8V7h-2zm-5-1.3" />
                            </svg>
                            <span class="sidebar-link__text">Benefícios</span>
                        </a>
                    </li>
                    <li class="sidebar__list-item">
                        <a href="{{ route('admin.meu-cadastro') }}"
                            class="sidebar__link {{ request()->routeIs('admin.meu-cadastro') ? 'sidebar__link--active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                class="sidebar-link__icon">
                                <path fill="currentColor"
                                    d="M12 7V3H2v18h20V7zM6 19H4v-2h2zm0-4H4v-2h2zm0-4H4V9h2zm0-4H4V5h2zm4 12H8v-2h2zm0-4H8v-2h2zm0-4H8V9h2zm0-4H8V5h2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8zm-2-8h-2v2h2zm0 4h-2v2h2z" />
                            </svg>
                            <span class="sidebar-link__text">Minha Organização</span>
                        </a>
                    </li>
                @endif

                <li class="sidebar__list-item sidebar__list-item--logout">
                    <form action="{{ route('logout') }}" method="post">
                        @csrf
                        <button type="submit" class="sidebar__link sidebar__link--logout">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                class="sidebar-link__icon">
                                <g fill="currentColor" fill-rule="evenodd" clip-rule="evenodd"
                                    transform="translate(3.25 0)">
                                    <path
                                        d="M15.99 7.823a.75.75 0 0 1 1.061.021l3.49 3.637a.75.75 0 0 1 0 1.038l-3.49 3.637a.75.75 0 0 1-1.082-1.039l2.271-2.367h-6.967a.75.75 0 0 1 0-1.5h6.968l-2.272-2.367a.75.75 0 0 1 .022-1.06" />
                                    <path
                                        d="M3.25 4A.75.75 0 0 1 4 3.25h9.455a.75.75 0 0 1 .75.75v3a.75.75 0 1 1-1.5 0V4.75H4.75v14.5h7.954V17a.75.75 0 0 1 1.5 0v3a.75.75 0 0 1-.75.75H4a.75.75 0 0 1-.75-.75z" />
                                </g>
                            </svg>
                            <span class="sidebar-link__text">Sair</span>
                        </button>
                    </form>
                </li>
                </ul>
            </nav>
            <main class="dashboard-content">{{ $content }}</main>
        </div>
    </x-slot:body>
</x-layout>
