@extends('layouts.app')

@section('title', 'Simulador')

@section('content')
@php
    $reading ??= null;
    $sourceLabels = [
        'simulation_auto' => 'Automática',
        'simulation_manual' => 'Manual',
        'simulation_scenario' => 'Escenario',
        'iot' => 'IoT',
    ];
@endphp

<section
    data-telemetry-dashboard
    data-telemetry-url="{{ route('api.telemetry.current') }}"
    data-poll-seconds="{{ config('telemetry.poll_seconds', 5) }}"
    data-reading-id="{{ $reading?->id ?? 0 }}"
>
    <div class="page-header">
        <div>
            <h1 class="page-title">Simulador de telemetría</h1>
            <p class="page-subtitle">
                Modifica lecturas sintéticas para probar dashboard, alertas, riego e informes.
            </p>
        </div>

        <div class="header-actions">
            <span class="data-source-badge">Datos simulados</span>
            <span class="badge badge-neutral">
                Sin sensores físicos
            </span>
        </div>
    </div>

    <div class="dashboard-grid">
        <article class="card span-8">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Simulación manual</h2>
                    <p class="card-subtitle">Los valores enviados se guardan como una lectura real y activan las reglas de negocio.</p>
                </div>
                <span class="badge badge-info">simulation_manual</span>
            </div>

            <form method="POST" action="{{ route('simulator.manual') }}">
                @csrf

                <div class="form-grid">
                    <div class="field">
                        <label for="temperature">Temperatura (°C)</label>
                        <div class="range-pair">
                            <input
                                type="range"
                                min="-20"
                                max="80"
                                step="0.1"
                                value="{{ old('temperature', $reading?->temperature ?? 28.5) }}"
                                data-range-target="temperature"
                            >
                            <input
                                class="input"
                                id="temperature"
                                type="number"
                                name="temperature"
                                min="-20"
                                max="80"
                                step="0.1"
                                value="{{ old('temperature', $reading?->temperature ?? 28.5) }}"
                                required
                            >
                        </div>
                    </div>

                    <div class="field">
                        <label for="soil_humidity">Humedad del suelo (%)</label>
                        <div class="range-pair">
                            <input
                                type="range"
                                min="0"
                                max="100"
                                step="1"
                                value="{{ old('soil_humidity', $reading?->soil_humidity ?? 45) }}"
                                data-range-target="soil_humidity"
                            >
                            <input
                                class="input"
                                id="soil_humidity"
                                type="number"
                                name="soil_humidity"
                                min="0"
                                max="100"
                                step="0.1"
                                value="{{ old('soil_humidity', $reading?->soil_humidity ?? 45) }}"
                                required
                            >
                        </div>
                    </div>

                    <div class="field">
                        <label for="ambient_humidity">Humedad ambiental (%)</label>
                        <div class="range-pair">
                            <input
                                type="range"
                                min="0"
                                max="100"
                                step="1"
                                value="{{ old('ambient_humidity', $reading?->ambient_humidity ?? 65) }}"
                                data-range-target="ambient_humidity"
                            >
                            <input
                                class="input"
                                id="ambient_humidity"
                                type="number"
                                name="ambient_humidity"
                                min="0"
                                max="100"
                                step="0.1"
                                value="{{ old('ambient_humidity', $reading?->ambient_humidity ?? 65) }}"
                                required
                            >
                        </div>
                    </div>

                    <div class="field">
                        <label for="luminosity">Luminosidad (lux)</label>
                        <div class="range-pair">
                            <input
                                type="range"
                                min="0"
                                max="100000"
                                step="100"
                                value="{{ old('luminosity', $reading?->luminosity ?? 1200) }}"
                                data-range-target="luminosity"
                            >
                            <input
                                class="input"
                                id="luminosity"
                                type="number"
                                name="luminosity"
                                min="0"
                                max="100000"
                                step="1"
                                value="{{ old('luminosity', $reading?->luminosity ?? 1200) }}"
                                required
                            >
                        </div>
                    </div>

                    <div class="field">
                        <label for="water_level">Nivel del depósito (%)</label>
                        <div class="range-pair">
                            <input
                                type="range"
                                min="0"
                                max="100"
                                step="1"
                                value="{{ old('water_level', $reading?->water_level ?? 80) }}"
                                data-range-target="water_level"
                            >
                            <input
                                class="input"
                                id="water_level"
                                type="number"
                                name="water_level"
                                min="0"
                                max="100"
                                step="0.1"
                                value="{{ old('water_level', $reading?->water_level ?? 80) }}"
                                required
                            >
                        </div>
                    </div>

                    <div class="field">
                        <label for="irrigation_status">Estado del riego</label>
                        <select class="select" id="irrigation_status" name="irrigation_status" required>
                            <option value="inactive" @selected(old('irrigation_status', $reading?->irrigation_status) === 'inactive')>Inactivo</option>
                            <option value="active" @selected(old('irrigation_status', $reading?->irrigation_status) === 'active')>Activo</option>
                            <option value="fault" @selected(old('irrigation_status', $reading?->irrigation_status) === 'fault')>Falla</option>
                        </select>
                    </div>

                    <div class="field">
                        <label for="device_status">Estado de conexión</label>
                        <select class="select" id="device_status" name="device_status" required>
                            <option value="connected" @selected(old('device_status', $reading?->device_status) === 'connected')>Conectado</option>
                            <option value="disconnected" @selected(old('device_status', $reading?->device_status) === 'disconnected')>Desconectado</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-ghost" type="submit" form="simulator-reset-form">Restaurar valores normales</button>
                    <button class="btn btn-primary" type="submit">Enviar lectura</button>
                </div>
            </form>

            <form id="simulator-reset-form" method="POST" action="{{ route('simulator.reset') }}">
                @csrf
            </form>
        </article>

        <article class="card span-4">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Simulación automática</h2>
                    <p class="card-subtitle">Genera pequeñas variaciones con cada ciclo de actualización.</p>
                </div>
            </div>

            <div class="simulation-status" style="margin-bottom:18px">
                <span class="pulse {{ $control->status }}"></span>
                <div>
                    <strong>{{ ucfirst($control->status) }}</strong>
                    <div class="help-text">
                        Intervalo: {{ $control->interval_seconds }} s · Intensidad: {{ $control->variation_intensity }}
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('simulator.start') }}">
                @csrf

                <div class="field field-full">
                    <label for="interval_seconds">Intervalo de generación</label>
                    <select class="select" id="interval_seconds" name="interval_seconds">
                        <option value="5" @selected($control->interval_seconds === 5)>5 segundos</option>
                        <option value="10" @selected($control->interval_seconds === 10)>10 segundos</option>
                        <option value="30" @selected($control->interval_seconds === 30)>30 segundos</option>
                        <option value="60" @selected($control->interval_seconds === 60)>1 minuto</option>
                    </select>
                </div>

                <div class="field field-full" style="margin-top:14px">
                    <label for="variation_intensity">Intensidad de variación</label>
                    <select class="select" id="variation_intensity" name="variation_intensity">
                        <option value="0.5" @selected((float) $control->variation_intensity === 0.5)>Baja</option>
                        <option value="1" @selected((float) $control->variation_intensity === 1.0)>Normal</option>
                        <option value="2" @selected((float) $control->variation_intensity === 2.0)>Alta</option>
                        <option value="4" @selected((float) $control->variation_intensity === 4.0)>Extrema</option>
                    </select>
                </div>

                <button class="btn btn-primary" style="width:100%;margin-top:16px" type="submit">
                    Iniciar o reanudar
                </button>
            </form>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:10px">
                <form method="POST" action="{{ route('simulator.pause') }}">
                    @csrf
                    <button class="btn btn-warning" style="width:100%" type="submit">Pausar</button>
                </form>
                <form method="POST" action="{{ route('simulator.stop') }}">
                    @csrf
                    <button class="btn btn-danger" style="width:100%" type="submit">Detener</button>
                </form>
            </div>

            <div class="alert-flash warning" style="margin:18px 0 0">
                <div>
                    El motor automático avanza cuando una vista consulta
                    <code>/api/telemetry/current</code>. No requiere cron para esta demostración.
                </div>
            </div>
        </article>

        <article class="card span-12">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Escenarios de prueba</h2>
                    <p class="card-subtitle">Casos predefinidos para ejecutar pruebas de aceptación rápidamente.</p>
                </div>
                <span class="badge badge-info">simulation_scenario</span>
            </div>

            <div class="scenario-grid">
                @foreach($scenarios as $scenario)
                    <article class="scenario-card">
                        <div>
                            <h3>{{ $scenario->name }}</h3>
                            <p>{{ $scenario->description }}</p>
                        </div>
                        <form method="POST" action="{{ route('simulator.scenario', $scenario) }}">
                            @csrf
                            <button class="btn btn-secondary" style="width:100%" type="submit">
                                Ejecutar escenario
                            </button>
                        </form>
                    </article>
                @endforeach
            </div>
        </article>

        <article class="card span-12">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Últimas lecturas</h2>
                    <p class="card-subtitle">Registros que reciben las demás vistas del sistema.</p>
                </div>
                <span class="badge badge-neutral">
                    Actual: <span data-reading-source>{{ $sourceLabels[$reading?->source] ?? '—' }}</span>
                </span>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Temperatura</th>
                            <th>Suelo</th>
                            <th>Ambiente</th>
                            <th>Luz</th>
                            <th>Agua</th>
                            <th>Riego</th>
                            <th>Origen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $item)
                            <tr>
                                <td>{{ $item->recorded_at->format('d/m/Y H:i:s') }}</td>
                                <td>{{ $item->temperature }} °C</td>
                                <td>{{ $item->soil_humidity }} %</td>
                                <td>{{ $item->ambient_humidity }} %</td>
                                <td>{{ $item->luminosity }} lux</td>
                                <td>{{ $item->water_level }} %</td>
                                <td>{{ ucfirst($item->irrigation_status) }}</td>
                                <td><span class="badge badge-neutral">{{ $sourceLabels[$item->source] ?? $item->source }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="empty-state">No existen lecturas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </div>
</section>
@endsection
