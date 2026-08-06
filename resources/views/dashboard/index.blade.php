@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
@php
    $statuses = app(\App\Services\TelemetryStatusService::class)->metricStatuses($greenhouse, $reading);
    $sourceLabels = [
        'simulation_auto' => 'Simulación automática',
        'simulation_manual' => 'Simulación manual',
        'simulation_scenario' => 'Escenario simulado',
        'iot' => 'IoT',
    ];
    $statusLabels = [
        'normal' => 'Normal',
        'warning' => 'Advertencia',
        'critical' => 'Crítico',
        'unknown' => 'Sin datos',
    ];
    $overallText = match($statuses['overall']) {
        'normal' => 'Todo está funcionando correctamente',
        'warning' => 'Se detectaron condiciones fuera del rango',
        'critical' => 'Se requiere atención inmediata',
        default => 'No hay lecturas disponibles',
    };
@endphp

<section
    data-telemetry-dashboard
    data-telemetry-url="{{ route('api.telemetry.current') }}"
    data-poll-seconds="{{ config('telemetry.poll_seconds', 5) }}"
    data-reading-id="{{ $reading?->id ?? 0 }}"
>
    <div class="page-header">
        <div>
            <h1 class="page-title">Inicio</h1>
            <p class="page-subtitle">Bienvenido, {{ auth()->user()->name }} · {{ $greenhouse->name }}</p>
        </div>

        <div class="header-actions">
            <span class="data-source-badge">
                ◉ <span data-reading-source>{{ $sourceLabels[$reading?->source] ?? 'Sin datos' }}</span>
            </span>
            <span class="badge badge-neutral">
                Actualizado: <span data-reading-time>{{ $reading?->recorded_at?->format('d/m/Y H:i:s') ?? '—' }}</span>
            </span>
        </div>
    </div>

    <div class="metric-grid">
        <article class="metric-card">
            <div class="metric-label">Temperatura</div>
            <div class="metric-value" data-reading="temperature">
                {{ $reading ? number_format((float) $reading->temperature, 1) : '—' }} °C
            </div>
            <div class="metric-meta">
                <span class="status-badge status-{{ $statuses['temperature'] }}" data-status="temperature">
                    {{ $statusLabels[$statuses['temperature']] ?? $statuses['temperature'] }}
                </span>
                <span>Óptimo {{ $greenhouse->temperature_min }}–{{ $greenhouse->temperature_max }} °C</span>
            </div>
        </article>

        <article class="metric-card">
            <div class="metric-label">Humedad del suelo</div>
            <div class="metric-value" data-reading="soil_humidity">
                {{ $reading ? number_format((float) $reading->soil_humidity) : '—' }} %
            </div>
            <div class="metric-meta">
                <span class="status-badge status-{{ $statuses['soil_humidity'] }}" data-status="soil_humidity">
                    {{ $statusLabels[$statuses['soil_humidity']] ?? $statuses['soil_humidity'] }}
                </span>
                <span>Óptimo {{ $greenhouse->soil_humidity_min }}–{{ $greenhouse->soil_humidity_max }} %</span>
            </div>
        </article>

        <article class="metric-card">
            <div class="metric-label">Humedad ambiental</div>
            <div class="metric-value" data-reading="ambient_humidity">
                {{ $reading ? number_format((float) $reading->ambient_humidity) : '—' }} %
            </div>
            <div class="metric-meta">
                <span class="status-badge status-{{ $statuses['ambient_humidity'] }}" data-status="ambient_humidity">
                    {{ $statusLabels[$statuses['ambient_humidity']] ?? $statuses['ambient_humidity'] }}
                </span>
                <span>Óptimo {{ $greenhouse->ambient_humidity_min }}–{{ $greenhouse->ambient_humidity_max }} %</span>
            </div>
        </article>

        <article class="metric-card">
            <div class="metric-label">Luminosidad</div>
            <div class="metric-value" data-reading="luminosity">
                {{ $reading ? number_format((float) $reading->luminosity) : '—' }} lux
            </div>
            <div class="metric-meta">
                <span class="status-badge status-{{ $statuses['luminosity'] }}" data-status="luminosity">
                    {{ $statusLabels[$statuses['luminosity']] ?? $statuses['luminosity'] }}
                </span>
                <span>Lectura simulada</span>
            </div>
        </article>

        <article class="metric-card">
            <div class="metric-label">Nivel de agua</div>
            <div class="metric-value" data-reading="water_level">
                {{ $reading ? number_format((float) $reading->water_level) : '—' }} %
            </div>
            <div class="metric-meta">
                <span class="status-badge status-{{ $statuses['water_level'] }}" data-status="water_level">
                    {{ $statusLabels[$statuses['water_level']] ?? $statuses['water_level'] }}
                </span>
                <span>Mínimo {{ $greenhouse->water_level_min }} %</span>
            </div>
        </article>
    </div>

    <div class="dashboard-grid">
        <article class="card span-6">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Estado del invernadero</h2>
                    <p class="card-subtitle">Evaluación de la última telemetría recibida</p>
                </div>
                <span class="data-source-badge">Datos simulados</span>
            </div>

            <div class="state-panel {{ $statuses['overall'] }}" data-overall-state>
                <div class="state-icon">
                    {{ $statuses['overall'] === 'normal' ? '✓' : '!' }}
                </div>
                <div>
                    <h3 data-overall-title>{{ $overallText }}</h3>
                    <p>
                        Dispositivo:
                        <strong data-reading="device_status">
                            {{ $reading?->device_status === 'connected' ? 'Conectado' : 'Desconectado' }}
                        </strong>
                    </p>
                </div>
            </div>
        </article>

        <article class="card span-6">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Riego automático</h2>
                    <p class="card-subtitle">Control lógico sin dispositivo físico</p>
                </div>
                <span class="badge {{ $greenhouse->automatic_irrigation ? 'badge-success' : 'badge-neutral' }}">
                    {{ $greenhouse->automatic_irrigation ? 'Activo' : 'Desactivado' }}
                </span>
            </div>

            <div class="state-panel normal">
                <div class="state-icon">💧</div>
                <div>
                    <h3 data-reading="irrigation_status">
                        {{ $activeIrrigation ? 'Regando' : 'Inactivo' }}
                    </h3>
                    <p>
                        @if($activeIrrigation)
                            Iniciado {{ $activeIrrigation->started_at->diffForHumans() }} ·
                            {{ $activeIrrigation->duration_minutes }} minutos
                        @elseif($nextSchedule)
                            Próximo horario programado: {{ \Carbon\Carbon::parse($nextSchedule->time)->format('H:i') }}
                        @else
                            No hay horarios programados.
                        @endif
                    </p>
                    <a class="btn btn-secondary btn-sm" href="{{ route('irrigation.index') }}" style="margin-top:12px">
                        Abrir control de riego
                    </a>
                </div>
            </div>
        </article>

        <article class="card span-7">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Últimas alertas</h2>
                    <p class="card-subtitle">Condiciones detectadas por las reglas configurables</p>
                </div>
                <a class="btn btn-ghost btn-sm" href="{{ route('alerts.index') }}">Ver todas</a>
            </div>

            @forelse($recentAlerts as $alert)
                <div class="alert-row">
                    <span class="alert-dot {{ $alert->severity }}"></span>
                    <div>
                        <strong>{{ $alert->title }}</strong>
                        <small>{{ $alert->description }}</small>
                    </div>
                    <time>{{ $alert->last_triggered_at->format('H:i') }}</time>
                </div>
            @empty
                <div class="empty-state">No se han generado alertas.</div>
            @endforelse
        </article>

        <article class="card span-5">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Pronóstico del clima</h2>
                    <p class="card-subtitle">Integración opcional para una fase posterior</p>
                </div>
                <span class="badge badge-neutral">No conectado</span>
            </div>

            <div class="weather-panel">
                <div class="weather-main">
                    <span class="weather-icon">☁</span>
                    <div>
                        <div class="weather-temp">— °C</div>
                        <div>API meteorológica pendiente</div>
                    </div>
                </div>
                <div class="weather-details">
                    <div>Humedad: —</div>
                    <div>Viento: —</div>
                </div>
            </div>
        </article>
    </div>
</section>
@endsection
