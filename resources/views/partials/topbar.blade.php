@php
    $layoutGreenhouses = \App\Models\Greenhouse::query()->orderBy('name')->get();
    $layoutGreenhouseId = session('greenhouse_id') ?: $layoutGreenhouses->first()?->id;
    $activeAlertCount = \App\Models\Alert::query()
        ->when($layoutGreenhouseId, fn ($query) => $query->where('greenhouse_id', $layoutGreenhouseId))
        ->whereIn('status', ['new', 'viewed'])
        ->count();
@endphp

<header class="topbar">
    <div class="topbar-left">
        <button type="button" class="menu-button" data-menu-toggle aria-label="Abrir menú">☰</button>

        @if($layoutGreenhouses->isNotEmpty())
            <form class="greenhouse-selector" method="GET" action="{{ route('dashboard') }}">
                <label for="top-greenhouse">Invernadero:</label>
                <select id="top-greenhouse" name="greenhouse_id">
                    @foreach($layoutGreenhouses as $item)
                        <option value="{{ $item->id }}" @selected((int) $layoutGreenhouseId === $item->id)>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
                <button class="btn btn-ghost btn-sm" type="submit">Cambiar</button>
            </form>
        @endif
    </div>

    <div class="topbar-right">
        <a class="notification-link" href="{{ route('alerts.index') }}" aria-label="Alertas activas">
            🔔
            <span class="notification-count" data-alert-count @hidden($activeAlertCount < 1)>
                {{ $activeAlertCount }}
            </span>
        </a>

        <div class="user-chip">
            <span class="user-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <span class="user-meta">
                <strong>{{ auth()->user()->name }}</strong>
                <span>{{ auth()->user()->role?->name }}</span>
            </span>
        </div>
    </div>
</header>
