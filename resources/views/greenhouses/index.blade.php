@extends('layouts.app')

@section('title', 'Invernaderos')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Invernaderos</h1>
        <p class="page-subtitle">Administración de áreas, cultivos y rangos operativos.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('greenhouses.create') }}">+ Registrar invernadero</a>
</div>

<div class="dashboard-grid">
    @forelse($greenhouses as $greenhouse)
        <article class="card span-6">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">{{ $greenhouse->name }}</h2>
                    <p class="card-subtitle">{{ $greenhouse->code }} · {{ $greenhouse->crop_type ?: 'Cultivo no definido' }}</p>
                </div>
                <span class="badge {{
                    $greenhouse->status === 'active'
                        ? 'badge-success'
                        : ($greenhouse->status === 'maintenance' ? 'badge-warning' : 'badge-neutral')
                }}">
                    {{ ucfirst($greenhouse->status) }}
                </span>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <tbody>
                        <tr><th>Ubicación</th><td>{{ $greenhouse->location ?: '—' }}</td></tr>
                        <tr><th>Responsable</th><td>{{ $greenhouse->responsible?->name ?: '—' }}</td></tr>
                        <tr><th>Última temperatura</th><td>{{ $greenhouse->latestReading?->temperature ?? '—' }} °C</td></tr>
                        <tr><th>Última humedad</th><td>{{ $greenhouse->latestReading?->soil_humidity ?? '—' }} %</td></tr>
                        <tr><th>Riego automático</th><td>{{ $greenhouse->automatic_irrigation ? 'Activado' : 'Desactivado' }}</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="form-actions">
                <a class="btn btn-ghost" href="{{ route('dashboard', ['greenhouse_id' => $greenhouse->id]) }}">Abrir</a>
                <a class="btn btn-secondary" href="{{ route('greenhouses.edit', $greenhouse) }}">Editar</a>
                <form method="POST" action="{{ route('greenhouses.destroy', $greenhouse) }}" data-confirm="¿Eliminar {{ $greenhouse->name }} y todos sus datos?">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">Eliminar</button>
                </form>
            </div>
        </article>
    @empty
        <article class="card span-12 empty-state">No hay invernaderos registrados.</article>
    @endforelse
</div>

{{ $greenhouses->links('partials.pagination') }}
@endsection
