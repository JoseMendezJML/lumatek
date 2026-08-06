<div class="form-grid">
    <div class="field">
        <label for="name">Nombre completo</label>
        <input class="input" id="name" type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required>
    </div>

    <div class="field">
        <label for="email">Correo electrónico</label>
        <input class="input" id="email" type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required>
    </div>

    <div class="field">
        <label for="phone">Teléfono</label>
        <input class="input" id="phone" type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}">
    </div>

    <div class="field">
        <label for="role_id">Rol</label>
        <select class="select" id="role_id" name="role_id" required>
            @foreach($roles as $role)
                <option value="{{ $role->id }}" @selected((int) old('role_id', $user->role_id ?? 0) === $role->id)>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label for="password">Contraseña {{ isset($user) ? '(opcional)' : '' }}</label>
        <input class="input" id="password" type="password" name="password" {{ isset($user) ? '' : 'required' }}>
        <span class="help-text">Mínimo 8 caracteres, con letras y números.</span>
    </div>

    <div class="field">
        <label for="password_confirmation">Confirmar contraseña</label>
        <input class="input" id="password_confirmation" type="password" name="password_confirmation" {{ isset($user) ? '' : 'required' }}>
    </div>

    <div class="field field-full">
        <input type="hidden" name="active" value="0">
        <label class="checkbox-row">
            <input type="checkbox" name="active" value="1" @checked((bool) old('active', $user->active ?? true))>
            <span>Cuenta activa</span>
        </label>
    </div>
</div>

<div class="form-actions">
    <a class="btn btn-ghost" href="{{ route('users.index') }}">Cancelar</a>
    <button class="btn btn-primary" type="submit">{{ isset($user) ? 'Guardar cambios' : 'Registrar usuario' }}</button>
</div>
