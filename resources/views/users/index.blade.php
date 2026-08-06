@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Usuarios</h1>
        <p class="page-subtitle">Administración de cuentas, roles y estado de acceso.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('users.create') }}">+ Registrar usuario</a>
</div>

<article class="card">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?: '—' }}</td>
                        <td><span class="badge badge-neutral">{{ $user->role?->name }}</span></td>
                        <td>
                            <span class="badge {{ $user->active ? 'badge-success' : 'badge-critical' }}">
                                {{ $user->active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-ghost btn-sm" href="{{ route('users.edit', $user) }}">Editar</a>

                                @unless(auth()->user()->is($user))
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" data-confirm="¿Eliminar a {{ $user->name }}?">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger btn-sm" type="submit">Eliminar</button>
                                    </form>
                                @endunless
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-state">No hay usuarios registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $users->links('partials.pagination') }}
</article>

<article class="card" style="margin-top:20px">
    <div class="card-title-row">
        <div>
            <h2 class="card-title">Actividad reciente</h2>
            <p class="card-subtitle">Acciones administrativas y eventos importantes.</p>
        </div>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Descripción</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activity as $item)
                    <tr>
                        <td>{{ $item->created_at?->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $item->user?->name ?: 'Sistema' }}</td>
                        <td><span class="badge badge-neutral">{{ $item->action }}</span></td>
                        <td>{{ $item->description }}</td>
                        <td>{{ $item->ip_address ?: '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No hay actividad registrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</article>
@endsection
