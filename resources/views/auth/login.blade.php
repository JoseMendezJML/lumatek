@extends('layouts.auth')

@section('title', 'Iniciar sesión')

@section('content')
<section class="auth-card">

    {{-- Panel izquierdo: formulario --}}
    <div class="auth-form-panel">

        <div class="auth-brand">
            <span class="brand-mark" aria-hidden="true">🍃</span>

            <span>
                <strong>LUMATEK</strong>
                <small>
                    Tecnología inteligente para un cultivo eficiente.
                </small>
            </span>
        </div>

        <h1 class="auth-title">Iniciar sesión</h1>

        <p class="auth-subtitle">
            Bienvenido de nuevo
        </p>

        {{-- Mensaje de éxito --}}
        @if(session('status'))
            <div class="alert-flash success">
                <div>{{ session('status') }}</div>
            </div>
        @endif

        {{-- Mensaje de error --}}
        @if($errors->any())
            <div class="alert-flash error">
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        <form method="POST" action="{{ route('login.store') }}">
            @csrf

            <div class="field field-full">
                <label for="email">
                    Correo electrónico
                </label>

                <input
                    class="input @error('email') input-error @enderror"
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="ejemplo@lumatek.com"
                    autocomplete="email"
                    required
                    autofocus
                >

                @error('email')
                    <span class="error-text">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div class="field field-full">
                <label for="password">
                    Contraseña
                </label>

                <input
                    class="input @error('password') input-error @enderror"
                    id="password"
                    type="password"
                    name="password"
                    placeholder="Ingresa tu contraseña"
                    autocomplete="current-password"
                    required
                >

                @error('password')
                    <span class="error-text">
                        {{ $message }}
                    </span>
                @enderror
            </div>

            <div class="auth-links">

                <label class="checkbox-row">
                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                    >

                    <span>Recordarme</span>
                </label>

                <a href="{{ route('password.request') }}">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <button
                class="btn btn-primary"
                style="width: 100%"
                type="submit"
            >
                Iniciar sesión
            </button>
        </form>

        {{--
        El acceso de demostración queda oculto.

        <div class="auth-demo">
            <strong>Acceso de demostración</strong><br>
            Administrador: admin@lumatek.test · Lumatek123!<br>
            Usuario: usuario@lumatek.test · Lumatek123!
        </div>
        --}}

    </div>

    {{-- Panel derecho: la imagen ya contiene el logo y el texto --}}
    <div
        class="auth-image-panel"
        role="img"
        aria-label="Invernadero de Lumatek"
    ></div>

</section>
@endsection