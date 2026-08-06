@extends('layouts.app')

@section('title', 'Alertas')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Alertas</h1>
        <p class="page-subtitle">{{ $greenhouse->name }} · detecciones generadas por telemetría</p>
    </div>
    <span class="data-source-badge">Datos simulados</span>
</div>

<div class="tabs">
    <a class="tab {{ $severity === '' ? 'active' : '' }}" href="{{ route('alerts.index', ['status' => $status ?: null]) }}">Todas</a>
    <a class="tab {{ $severity === 'critical' ? 'active' : '' }}" href="{{ route('alerts.index', ['severity' => 'critical', 'status' => $status ?: null]) }}">Críticas</a>
    <a class="tab {{ $severity === 'warning' ? 'active' : '' }}" href="{{ route('alerts.index', ['severity' => 'warning', 'status' => $status ?: null]) }}">Advertencias</a>
    <a class="tab {{ $severity === 'info' ? 'active' : '' }}" href="{{ route('alerts.index', ['severity' => 'info', 'status' => $status ?: null]) }}">Información</a>
</div>

<div class="card" style="margin-bottom:18px">
    <form class="form-grid" method="GET" action="{{ route('alerts.index') }}">
        <input type="hidden" name="severity" value="{{ $severity }}">
        <div class="field field-third">
            <label for="status">Estado</label>
            <select class="select" id="status" name="status">
                <option value="">Todos</option>
                <option value="new" @selected($status === 'new')>Nuevas</option>
                <option value="viewed" @selected($status === 'viewed')>Vistas</option>
                <option value="resolved" @selected($status === 'resolved')>Resueltas</option>
            </select>
        </div>
        <div class="field field-third" style="display:flex;align-items:end;gap:8px">
            <button class="btn btn-primary" type="submit">Filtrar</button>
            <a class="btn btn-ghost" href="{{ route('alerts.index') }}">Limpiar</a>
        </div>
    </form>
</div>

@forelse($alerts as $alert)
    @php
        $icon = match($alert->severity) {
            'critical' => '🌡',
            'warning' => '⚠',
            default => 'ℹ',
        };
    @endphp

    <article class="alert-card {{ $alert->severity }}">
        <div class="alert-symbol">{{ $icon }}</div>

        <div>
            <h3>{{ $alert->title }}</h3>
            <p>{{ $alert->description }}</p>
            <div class="alert-card-meta">
                <span><strong>Invernadero:</strong> {{ $alert->greenhouse->name }}</span>
                <span><strong>Variable:</strong> {{ str_replace('_', ' ', $alert->variable) }}</span>
                @if($alert->value !== null)
                    <span><strong>Valor:</strong> {{ $alert->value }}</span>
                @endif
                <span><strong>Origen:</strong> {{ str_replace('_', ' ', $alert->source) }}</span>
                <span><strong>Actualizada:</strong> {{ $alert->last_triggered_at->format('d/m/Y H:i:s') }}</span>
            </div>
        </div>

        <div class="alert-card-actions">
            <span class="badge {{
                $alert->status === 'resolved'
                    ? 'badge-success'
                    : ($alert->status === 'viewed' ? 'badge-neutral' : 'badge-info')
            }}">
                {{ ucfirst($alert->status) }}
            </span>

            @if($alert->status === 'new')
                <form method="POST" action="{{ route('alerts.viewed', $alert) }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-ghost btn-sm" type="submit">Marcar vista</button>
                </form>
            @endif

            @if($alert->status !== 'resolved')
                <form method="POST" action="{{ route('alerts.resolve', $alert) }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn btn-primary btn-sm" type="submit">Resolver</button>
                </form>
            @endif
        </div>
    </article>
@empty
    <div class="card empty-state">No hay alertas con los filtros seleccionados.</div>
@endforelse

{{ $alerts->links('partials.pagination') }}
@endsection
