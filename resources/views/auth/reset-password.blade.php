@extends('layouts.auth')

@section('title', 'Restablecer contraseña')

@section('content')
<section class="auth-card">
    <div class="auth-form-panel">
        <div class="auth-brand">
            <span class="brand-mark" aria-hidden="true">🍃</span>
            <span>
                <strong>LUMATEK</strong>
                <small>Restablecimiento de acceso</small>
            </span>
        </div>

        <h1 class="auth-title">Nueva contraseña</h1>
        <p class="auth-subtitle">Define una contraseña segura para continuar.</p>

        @if($errors->any())
            <div class="alert-flash error"><div>{{ $errors->first() }}</div></div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="field field-full">
                <label for="email">Correo electrónico</label>
                <input class="input" id="email" type="email" name="email" value="{{ old('email', $email) }}" required>
            </div>

            <div class="field field-full">
                <label for="password">Nueva contraseña</label>
                <input class="input" id="password" type="password" name="password" required>
            </div>

            <div class="field field-full">
                <label for="password_confirmation">Confirmar contraseña</label>
                <input class="input" id="password_confirmation" type="password" name="password_confirmation" required>
            </div>

            <button class="btn btn-primary" style="width:100%" type="submit">
                Guardar contraseña
            </button>
        </form>
    </div>

    <div class="auth-image-panel" aria-hidden="true">
        <div class="auth-image-overlay">
            <div class="leaf">🔑</div>
            <h2>LUMATEK</h2>
            <p>Protección de cuentas y datos.</p>
        </div>
    </div>
</section>
@endsection
