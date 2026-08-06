@extends('layouts.auth')

@section('title', 'Recuperar contraseña')

@section('content')
<section class="auth-card">
    <div class="auth-form-panel">
        <div class="auth-brand">
            <span class="brand-mark" aria-hidden="true">🍃</span>
            <span>
                <strong>LUMATEK</strong>
                <small>Recuperación de acceso</small>
            </span>
        </div>

        <h1 class="auth-title">Recuperar contraseña</h1>
        <p class="auth-subtitle">
            Ingresa tu correo. En este prototipo, el enlace se escribe en
            <code>storage/logs/laravel.log</code>.
        </p>

        @if(session('status'))
            <div class="alert-flash success"><div>{{ session('status') }}</div></div>
        @endif

        @if($errors->any())
            <div class="alert-flash error"><div>{{ $errors->first() }}</div></div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="field field-full">
                <label for="email">Correo electrónico</label>
                <input class="input" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>

            <button class="btn btn-primary" style="width:100%" type="submit">
                Generar enlace de recuperación
            </button>
        </form>

        <p style="margin-top:20px">
            <a href="{{ route('login') }}" style="color:var(--green-800);font-weight:700">← Volver al inicio de sesión</a>
        </p>
    </div>

    <div class="auth-image-panel" aria-hidden="true">
        <div class="auth-image-overlay">
            <div class="leaf">🔐</div>
            <h2>LUMATEK</h2>
            <p>Acceso seguro al sistema.</p>
        </div>
    </div>
</section>
@endsection
