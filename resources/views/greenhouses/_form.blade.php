<div class="form-grid">
    <div class="field">
        <label for="name">Nombre</label>
        <input class="input" id="name" type="text" name="name" value="{{ old('name', $greenhouse->name ?? '') }}" required>
    </div>

    <div class="field">
        <label for="code">Código</label>
        <input class="input" id="code" type="text" name="code" value="{{ old('code', $greenhouse->code ?? '') }}" placeholder="INV-001" required>
    </div>

    <div class="field">
        <label for="location">Ubicación descriptiva</label>
        <input class="input" id="location" type="text" name="location" value="{{ old('location', $greenhouse->location ?? '') }}">
    </div>

    <div class="field">
        <label for="crop_type">Tipo de cultivo</label>
        <input class="input" id="crop_type" type="text" name="crop_type" value="{{ old('crop_type', $greenhouse->crop_type ?? '') }}">
    </div>

    <div class="field">
        <label for="responsible_user_id">Responsable</label>
        <select class="select" id="responsible_user_id" name="responsible_user_id">
            <option value="">Sin asignar</option>
            @foreach($users as $item)
                <option value="{{ $item->id }}" @selected((int) old('responsible_user_id', $greenhouse->responsible_user_id ?? 0) === $item->id)>
                    {{ $item->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="field">
        <label for="status">Estado</label>
        <select class="select" id="status" name="status" required>
            <option value="active" @selected(old('status', $greenhouse->status ?? 'active') === 'active')>Activo</option>
            <option value="inactive" @selected(old('status', $greenhouse->status ?? '') === 'inactive')>Inactivo</option>
            <option value="maintenance" @selected(old('status', $greenhouse->status ?? '') === 'maintenance')>Mantenimiento</option>
        </select>
    </div>

    <div class="field field-full">
        <h2 class="card-title">Rangos de operación</h2>
        <p class="card-subtitle">Estos valores se usan para evaluar el dashboard y el riego automático.</p>
    </div>

    <div class="field field-quarter">
        <label for="temperature_min">Temperatura mínima</label>
        <input class="input" id="temperature_min" type="number" step="0.1" name="temperature_min" value="{{ old('temperature_min', $greenhouse->temperature_min ?? 16) }}" required>
    </div>
    <div class="field field-quarter">
        <label for="temperature_max">Temperatura máxima</label>
        <input class="input" id="temperature_max" type="number" step="0.1" name="temperature_max" value="{{ old('temperature_max', $greenhouse->temperature_max ?? 34) }}" required>
    </div>
    <div class="field field-quarter">
        <label for="soil_humidity_min">Suelo mínimo</label>
        <input class="input" id="soil_humidity_min" type="number" step="0.1" name="soil_humidity_min" value="{{ old('soil_humidity_min', $greenhouse->soil_humidity_min ?? 30) }}" required>
    </div>
    <div class="field field-quarter">
        <label for="soil_humidity_max">Suelo máximo</label>
        <input class="input" id="soil_humidity_max" type="number" step="0.1" name="soil_humidity_max" value="{{ old('soil_humidity_max', $greenhouse->soil_humidity_max ?? 60) }}" required>
    </div>

    <div class="field field-quarter">
        <label for="ambient_humidity_min">Ambiente mínimo</label>
        <input class="input" id="ambient_humidity_min" type="number" step="0.1" name="ambient_humidity_min" value="{{ old('ambient_humidity_min', $greenhouse->ambient_humidity_min ?? 40) }}" required>
    </div>
    <div class="field field-quarter">
        <label for="ambient_humidity_max">Ambiente máximo</label>
        <input class="input" id="ambient_humidity_max" type="number" step="0.1" name="ambient_humidity_max" value="{{ old('ambient_humidity_max', $greenhouse->ambient_humidity_max ?? 80) }}" required>
    </div>
    <div class="field field-quarter">
        <label for="luminosity_min">Luminosidad mínima</label>
        <input class="input" id="luminosity_min" type="number" step="1" name="luminosity_min" value="{{ old('luminosity_min', $greenhouse->luminosity_min ?? 500) }}" required>
    </div>
    <div class="field field-quarter">
        <label for="luminosity_max">Luminosidad máxima</label>
        <input class="input" id="luminosity_max" type="number" step="1" name="luminosity_max" value="{{ old('luminosity_max', $greenhouse->luminosity_max ?? 20000) }}" required>
    </div>

    <div class="field field-quarter">
        <label for="water_level_min">Nivel mínimo de agua</label>
        <input class="input" id="water_level_min" type="number" step="0.1" name="water_level_min" value="{{ old('water_level_min', $greenhouse->water_level_min ?? 25) }}" required>
    </div>

    <div class="field field-full">
        <input type="hidden" name="automatic_irrigation" value="0">
        <label class="checkbox-row">
            <input type="checkbox" name="automatic_irrigation" value="1" @checked((bool) old('automatic_irrigation', $greenhouse->automatic_irrigation ?? true))>
            <span>Activar riego automático simulado</span>
        </label>
    </div>
</div>

<div class="form-actions">
    <a class="btn btn-ghost" href="{{ route('greenhouses.index') }}">Cancelar</a>
    <button class="btn btn-primary" type="submit">{{ isset($greenhouse) ? 'Guardar cambios' : 'Registrar invernadero' }}</button>
</div>
