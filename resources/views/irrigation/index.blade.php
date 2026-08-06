@extends('layouts.app')

@section('title', 'Riego')

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
            <h1 class="page-title">Riego</h1>
            <p class="page-subtitle">Programación y ejecución simulada · {{ $greenhouse->name }}</p>
        </div>
        <span class="data-source-badge">Riego simulado</span>
    </div>

    <div class="dashboard-grid">
        <article class="card span-6">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Estado del riego automático</h2>
                    <p class="card-subtitle">Se activa según humedad, nivel de agua y reglas del invernadero.</p>
                </div>
                <span class="badge {{ $greenhouse->automatic_irrigation ? 'badge-success' : 'badge-neutral' }}">
                    {{ $greenhouse->automatic_irrigation ? 'Activo' : 'Desactivado' }}
                </span>
            </div>

            <p>
                El riego automático inicia cuando la humedad cae debajo de
                <strong>{{ $greenhouse->soil_humidity_min }} %</strong> y el depósito conserva más de
                <strong>{{ $greenhouse->water_level_min }} %</strong>.
            </p>

            <a class="btn btn-secondary" href="{{ route('control.index') }}">Cambiar configuración</a>
        </article>

        <article class="card span-6">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Humedad del suelo actual</h2>
                    <p class="card-subtitle">Última lectura procesada</p>
                </div>
                <span class="data-source-badge">Datos simulados</span>
            </div>

            <div class="big-value" data-reading="soil_humidity">{{ number_format($soil) }} %</div>
            <div class="progress">
                <span data-progress="soil_humidity" style="width:{{ min(100, max(0, $soil)) }}%"></span>
            </div>
            <p class="page-subtitle">Nivel recomendado: {{ $greenhouse->soil_humidity_min }}–{{ $greenhouse->soil_humidity_max }} %</p>
        </article>

        <article class="card span-7">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Programación de riego</h2>
                    <p class="card-subtitle">Horarios de referencia para las pruebas</p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Horario</th>
                            <th>Duración</th>
                            <th>Días</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schedules as $schedule)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($schedule->time)->format('H:i') }}</td>
                                <td>{{ $schedule->duration_minutes }} min</td>
                                <td>{{ count($schedule->days ?? []) === 7 ? 'Todos' : count($schedule->days ?? []).' días' }}</td>
                                <td>
                                    <span class="badge {{ $schedule->active ? 'badge-success' : 'badge-neutral' }}">
                                        {{ $schedule->active ? 'Activo' : 'Pausado' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <form method="POST" action="{{ route('irrigation.schedules.toggle', $schedule) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn btn-ghost btn-sm" type="submit">
                                                {{ $schedule->active ? 'Pausar' : 'Activar' }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('irrigation.schedules.destroy', $schedule) }}" data-confirm="¿Eliminar este horario?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm" type="submit">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-state">No hay horarios.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <hr style="border:0;border-top:1px solid var(--line);margin:22px 0">

            <form method="POST" action="{{ route('irrigation.schedules.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="field field-third">
                        <label for="schedule-time">Hora</label>
                        <input class="input" id="schedule-time" type="time" name="time" value="{{ old('time', '08:00') }}" required>
                    </div>
                    <div class="field field-third">
                        <label for="schedule-duration">Duración (minutos)</label>
                        <input class="input" id="schedule-duration" type="number" name="duration_minutes" min="1" max="180" value="{{ old('duration_minutes', 20) }}" required>
                    </div>
                    <div class="field field-full">
                        <span class="field-label">Días</span>
                        <div style="display:flex;flex-wrap:wrap;gap:10px 18px">
                            @foreach([
                                'monday' => 'Lun',
                                'tuesday' => 'Mar',
                                'wednesday' => 'Mié',
                                'thursday' => 'Jue',
                                'friday' => 'Vie',
                                'saturday' => 'Sáb',
                                'sunday' => 'Dom',
                            ] as $value => $label)
                                <label class="checkbox-row">
                                    <input type="checkbox" name="days[]" value="{{ $value }}" checked>
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-secondary" type="submit">+ Agregar horario</button>
                </div>
            </form>
        </article>

        <article class="card span-5">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Riego manual</h2>
                    <p class="card-subtitle">Prueba controlada sin dispositivo físico</p>
                </div>
                <span class="badge {{ $running ? 'badge-info' : 'badge-neutral' }}">
                    {{ $running ? 'En ejecución' : 'Disponible' }}
                </span>
            </div>

            @if($running)
                <p><strong>Iniciado:</strong> {{ $running->started_at->format('H:i:s') }}</p>
                <p><strong>Duración:</strong> {{ $running->duration_minutes }} minutos</p>
                <p><strong>Humedad inicial:</strong> {{ $running->humidity_before }} %</p>

                <form method="POST" action="{{ route('irrigation.stop') }}">
                    @csrf
                    <button class="btn btn-danger" style="width:100%" type="submit">Detener riego ahora</button>
                </form>
            @else
                <form method="POST" action="{{ route('irrigation.start') }}">
                    @csrf
                    <div class="field field-full">
                        <label for="manual-duration">Duración (minutos)</label>
                        <input class="input" id="manual-duration" type="number" name="duration_minutes" min="1" max="180" value="20" required>
                    </div>
                    <button class="btn btn-primary" style="width:100%" type="submit">Iniciar riego ahora</button>
                </form>
            @endif
        </article>

        <article class="card span-12">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">Historial de riego</h2>
                    <p class="card-subtitle">Evidencia de pruebas y cambios en la humedad</p>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Duración</th>
                            <th>Humedad inicial</th>
                            <th>Humedad final</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $event)
                            <tr>
                                <td>{{ $event->started_at->format('d/m/Y') }}</td>
                                <td>{{ ucfirst($event->type) }}</td>
                                <td>{{ $event->started_at->format('H:i:s') }}</td>
                                <td>{{ $event->ended_at?->format('H:i:s') ?? '—' }}</td>
                                <td>{{ $event->duration_minutes }} min</td>
                                <td>{{ $event->humidity_before ?? '—' }} %</td>
                                <td>{{ $event->humidity_after ?? '—' }} %</td>
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
                            <tr><td colspan="8" class="empty-state">No hay riegos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </div>
</section>
@endsection
