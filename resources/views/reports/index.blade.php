@extends('layouts.app')

@section('title', 'Reportes')

@section('content')
@php
    $period = request('period', 'week');
@endphp

<div class="page-header">
    <div>
        <h1 class="page-title">Reportes</h1>
        <p class="page-subtitle">{{ $greenhouse->name }} · información almacenada en la base de datos</p>
    </div>

    <div class="header-actions">
        <a
            class="btn btn-primary"
            href="{{ route('reports.print', request()->query()) }}"
            target="_blank"
        >
            Imprimir / Guardar PDF
        </a>
    </div>
</div>

<article class="card" style="margin-bottom:20px">
    <form method="GET" action="{{ route('reports.index') }}">
        <div class="form-grid">
            <div class="field field-quarter">
                <label for="period">Periodo</label>
                <select class="select" id="period" name="period">
                    <option value="day" @selected($period === 'day')>Hoy</option>
                    <option value="week" @selected($period === 'week')>Últimos 7 días</option>
                    <option value="month" @selected($period === 'month')>Mes actual</option>
                    <option value="custom" @selected($period === 'custom')>Personalizado</option>
                </select>
            </div>

            <div class="field field-quarter">
                <label for="from">Desde</label>
                <input class="input" id="from" type="date" name="from" value="{{ request('from', $from->toDateString()) }}">
            </div>

            <div class="field field-quarter">
                <label for="to">Hasta</label>
                <input class="input" id="to" type="date" name="to" value="{{ request('to', $to->toDateString()) }}">
            </div>

            <div class="field field-quarter">
                <label for="source">Origen</label>
                <select class="select" id="source" name="source">
                    <option value="">Todos</option>
                    <option value="simulation_manual" @selected($source === 'simulation_manual')>Simulación manual</option>
                    <option value="simulation_auto" @selected($source === 'simulation_auto')>Simulación automática</option>
                    <option value="simulation_scenario" @selected($source === 'simulation_scenario')>Escenario</option>
                    <option value="iot" @selected($source === 'iot')>IoT</option>
                </select>
            </div>

            <div class="field field-quarter">
                <label for="variable">Variable de alerta</label>
                <select class="select" id="variable" name="variable">
                    <option value="">Todas</option>
                    <option value="temperature" @selected($variable === 'temperature')>Temperatura</option>
                    <option value="soil_humidity" @selected($variable === 'soil_humidity')>Humedad del suelo</option>
                    <option value="ambient_humidity" @selected($variable === 'ambient_humidity')>Humedad ambiental</option>
                    <option value="water_level" @selected($variable === 'water_level')>Nivel de agua</option>
                    <option value="irrigation_status" @selected($variable === 'irrigation_status')>Estado del riego</option>
                    <option value="device_status" @selected($variable === 'device_status')>Conexión</option>
                </select>
            </div>

            <div class="field field-quarter">
                <label for="severity">Tipo de alerta</label>
                <select class="select" id="severity" name="severity">
                    <option value="">Todas</option>
                    <option value="info" @selected($severity === 'info')>Información</option>
                    <option value="warning" @selected($severity === 'warning')>Advertencia</option>
                    <option value="critical" @selected($severity === 'critical')>Crítica</option>
                </select>
            </div>

            <div class="field field-quarter" style="display:flex;align-items:end">
                <button class="btn btn-primary" style="width:100%" type="submit">Generar reporte</button>
            </div>
        </div>
    </form>
</article>

<div class="report-metrics">
    <div class="report-stat">
        Lecturas procesadas
        <strong>{{ number_format((int) ($stats->readings_count ?? 0)) }}</strong>
    </div>
    <div class="report-stat">
        Temperatura promedio
        <strong>{{ number_format((float) ($stats->temperature_avg ?? 0), 1) }} °C</strong>
    </div>
    <div class="report-stat">
        Humedad del suelo promedio
        <strong>{{ number_format((float) ($stats->soil_humidity_avg ?? 0), 1) }} %</strong>
    </div>
    <div class="report-stat">
        Riegos simulados
        <strong>{{ number_format($irrigationSummary['count']) }}</strong>
    </div>
</div>

<div class="dashboard-grid">
    <article class="card span-6">
        <div class="card-title-row">
            <div>
                <h2 class="card-title">Resumen de telemetría</h2>
                <p class="card-subtitle">{{ $from->format('d/m/Y') }} al {{ $to->format('d/m/Y') }}</p>
            </div>
            <span class="data-source-badge">Datos simulados</span>
        </div>

        <div class="table-wrap">
            <table class="table">
                <tbody>
                    <tr>
                        <th>Temperatura mínima</th>
                        <td>{{ number_format((float) ($stats->temperature_min ?? 0), 1) }} °C</td>
                    </tr>
                    <tr>
                        <th>Temperatura máxima</th>
                        <td>{{ number_format((float) ($stats->temperature_max ?? 0), 1) }} °C</td>
                    </tr>
                    <tr>
                        <th>Humedad del suelo mínima</th>
                        <td>{{ number_format((float) ($stats->soil_humidity_min ?? 0), 1) }} %</td>
                    </tr>
                    <tr>
                        <th>Humedad del suelo máxima</th>
                        <td>{{ number_format((float) ($stats->soil_humidity_max ?? 0), 1) }} %</td>
                    </tr>
                    <tr>
                        <th>Humedad ambiental promedio</th>
                        <td>{{ number_format((float) ($stats->ambient_humidity_avg ?? 0), 1) }} %</td>
                    </tr>
                    <tr>
                        <th>Luminosidad promedio</th>
                        <td>{{ number_format((float) ($stats->luminosity_avg ?? 0), 0) }} lux</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </article>

    <article class="card span-6">
        <div class="card-title-row">
            <div>
                <h2 class="card-title">Actividad del periodo</h2>
                <p class="card-subtitle">Alertas, riegos y origen de lecturas</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="table">
                <tbody>
                    <tr>
                        <th>Alertas generadas</th>
                        <td>{{ $alertSummary['generated'] }}</td>
                    </tr>
                    <tr>
                        <th>Alertas críticas</th>
                        <td>{{ $alertSummary['critical'] }}</td>
                    </tr>
                    <tr>
                        <th>Alertas resueltas</th>
                        <td>{{ $alertSummary['resolved'] }}</td>
                    </tr>
                    <tr>
                        <th>Minutos de riego</th>
                        <td>{{ $irrigationSummary['minutes'] }} min</td>
                    </tr>
                    <tr>
                        <th>Lecturas manuales</th>
                        <td>{{ (int) ($stats->manual_count ?? 0) }}</td>
                    </tr>
                    <tr>
                        <th>Lecturas automáticas</th>
                        <td>{{ (int) ($stats->auto_count ?? 0) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </article>

    <article class="card span-12">
        <div class="card-title-row">
            <div>
                <h2 class="card-title">Detalle de lecturas</h2>
                <p class="card-subtitle">Se muestran hasta 100 registros del periodo.</p>
            </div>
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
                    @forelse($readings as $reading)
                        <tr>
                            <td>{{ $reading->recorded_at->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $reading->temperature }} °C</td>
                            <td>{{ $reading->soil_humidity }} %</td>
                            <td>{{ $reading->ambient_humidity }} %</td>
                            <td>{{ $reading->luminosity }} lux</td>
                            <td>{{ $reading->water_level }} %</td>
                            <td>{{ ucfirst($reading->irrigation_status) }}</td>
                            <td>{{ str_replace('_', ' ', $reading->source) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty-state">No existen lecturas en este periodo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>
</div>
@endsection
