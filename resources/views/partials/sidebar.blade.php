<aside class="sidebar" aria-label="Menú principal">
    <a href="{{ route('dashboard') }}" class="brand">
        <span class="brand-mark" aria-hidden="true">🍃</span>
        <span>
            <span class="brand-name">LUMATEK</span>
            <span class="brand-subtitle">Cultivo inteligente</span>
        </span>
    </a>

    <nav class="sidebar-nav">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <span class="nav-icon">⌂</span>
            <span>Inicio</span>
        </a>

        <a class="nav-link {{ request()->routeIs('control.*') ? 'active' : '' }}" href="{{ route('control.index') }}">
            <span class="nav-icon">◷</span>
            <span>Control</span>
        </a>

        <a class="nav-link {{ request()->routeIs('alerts.*') ? 'active' : '' }}" href="{{ route('alerts.index') }}">
            <span class="nav-icon">♢</span>
            <span>Alertas</span>
        </a>

        <a class="nav-link {{ request()->routeIs('irrigation.*') ? 'active' : '' }}" href="{{ route('irrigation.index') }}">
            <span class="nav-icon">💧</span>
            <span>Riego</span>
        </a>

        <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
            <span class="nav-icon">▤</span>
            <span>Reportes</span>
        </a>

        @if(auth()->user()->isAdmin())
            <a class="nav-link {{ request()->routeIs('simulator.*') ? 'active' : '' }}" href="{{ route('simulator.index') }}">
                <span class="nav-icon">⚙</span>
                <span>Simulador</span>
            </a>

            <a class="nav-link {{ request()->routeIs('greenhouses.*') ? 'active' : '' }}" href="{{ route('greenhouses.index') }}">
                <span class="nav-icon">▱</span>
                <span>Invernaderos</span>
            </a>

            <a class="nav-link {{ request()->routeIs('alert-rules.*') ? 'active' : '' }}" href="{{ route('alert-rules.index') }}">
                <span class="nav-icon">≋</span>
                <span>Reglas</span>
            </a>

            <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                <span class="nav-icon">♙</span>
                <span>Usuarios</span>
            </a>
        @endif
    </nav>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="nav-link logout-button" type="submit">
                <span class="nav-icon">⇥</span>
                <span>Cerrar sesión</span>
            </button>
        </form>
    </div>
</aside>
