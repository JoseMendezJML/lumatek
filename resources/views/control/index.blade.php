@extends('layouts.app')

@section('title', 'Control')

@section('content')
@php
    $soil = $reading ? (float) $reading->soil_humidity : 0;
@endphp

<section
    data-telemetry-dashboard
    data-telemetry-url="{{ route('api.telemetry.current') }}"
    data-poll-seconds="{{ config('telemetry.poll_seconds', 5) }}"
    data-reading-id="{{ $reading?->id ?? 0 }}"
>
    <div class="page-header">
        <div>
            <h1 class="page-title">Control</h1>
            <p class="page-subtitle">{{ $greenhouse->name }} · operación simulada</p>
        </div>
        <span class="data-source-badge">Datos simulados</span>
    </div>

    <div class="control-hero">
        <article class="card">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Riego automático</h2>
                    <p class="card-subtitle">
                        El sistema inicia un riego simulado cuando la humedad está debajo de
                        {{ $greenhouse->soil_humidity_min }} % y hay suficiente agua.
                    </p>
                </div>

                <form method="POST" action="{{ route('control.automatic') }}">
                    @csrf
                    @method('PATCH')
                    <button class="btn {{ $greenhouse->automatic_irrigation ? 'btn-primary' : 'btn-ghost' }}" type="submit">
                        {{ $greenhouse->automatic_irrigation ? 'Activado' : 'Desactivado' }}
                    </button>
                </form>
            </div>

            <hr style="border:0;border-top:1px solid var(--line);margin:20px 0">

            <h3 style="margin:0">Humedad del suelo actual</h3>
            <div class="big-value" data-reading="soil_humidity">{{ number_format($soil) }} %</div>
            <div class="progress" aria-label="Humedad del suelo">
                <span data-progress="soil_humidity" style="width:{{ min(100, max(0, $soil)) }}%"></span>
            </div>
            <p class="page-subtitle">Rango ideal: {{ $greenhouse->soil_humidity_min }} % – {{ $greenhouse->soil_humidity_max }} %</p>
        </article>

        <article class="card">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Estado del riego</h2>
                    <p class="card-subtitle">Ejecución lógica, sin activar una bomba física</p>
                </div>
                <span class="badge {{ $running ? 'badge-success' : 'badge-neutral' }}">
                    {{ $running ? 'Regando' : 'Inactivo' }}
                </span>
            </div>

            @if($running)
                <p><strong>Inicio:</strong> {{ $running->started_at->format('d/m/Y H:i:s') }}</p>
                <p><strong>Duración programada:</strong> {{ $running->duration_minutes }} min</p>
                <p><strong>Origen:</strong> {{ $running->type === 'automatic' ? 'Automático' : 'Manual' }}</p>

                <form method="POST" action="{{ route('irrigation.stop') }}">
                    @csrf
                    <button class="btn btn-danger" type="submit">Detener riego simulado</button>
                </form>
            @else
                <form method="POST" action="{{ route('irrigation.start') }}">
                    @csrf
                    <div class="field field-full">
                        <label for="control-duration">Duración del riego</label>
                        <select class="select" id="control-duration" name="duration_minutes">
                            <option value="5">5 minutos</option>
                            <option value="10">10 minutos</option>
                            <option value="20" selected>20 minutos</option>
                            <option value="30">30 minutos</option>
                        </select>
                    </div>
                    <button class="btn btn-primary" type="submit">Regar ahora manualmente</button>
                </form>
            @endif
        </article>
    </div>

    <div class="dashboard-grid" style="margin-top:18px">
        <article class="card span-5">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Programa de riego</h2>
                    <p class="card-subtitle">Horarios configurados para el invernadero</p>
                </div>
                <a class="btn btn-ghost btn-sm" href="{{ route('irrigation.index') }}">Administrar</a>
            </div>

            @forelse($schedules as $schedule)
                <div class="alert-row">
                    <span class="alert-dot {{ $schedule->active ? 'info' : '' }}" style="{{ !$schedule->active ? 'background:#9ca3af' : '' }}"></span>
                    <div>
                        <strong>{{ \Carbon\Carbon::parse($schedule->time)->format('H:i') }}</strong>
                        <small>Duración estimada: {{ $schedule->duration_minutes }} min</small>
                    </div>
                    <span class="badge {{ $schedule->active ? 'badge-success' : 'badge-neutral' }}">
                        {{ $schedule->active ? 'Activo' : 'Pausado' }}
                    </span>
                </div>
            @empty
                <div class="empty-state">No hay horarios programados.</div>
            @endforelse
        </article>

        <article class="card span-7">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Historial reciente</h2>
                    <p class="card-subtitle">Eventos de riego simulados</p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Inicio</th>
                            <th>Duración</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $event)
                            <tr>
                                <td>{{ $event->started_at->format('d/m/Y') }}</td>
                                <td>{{ $event->started_at->format('H:i') }}</td>
                                <td>{{ $event->duration_minutes }} min</td>
                                <td>{{ ucfirst($event->type) }}</td>
                                <td>
                                    <span class="badge {{
                                        $event->status === 'completed'
                                            ? 'badge-success'
                                            : ($event->status === 'running' ? 'badge-info' : 'badge-neutral')
                                    }}">
                                        {{ ucfirst($event->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-state">No hay eventos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </div>
</section>
@endsection
