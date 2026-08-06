@extends('layouts.app')

@section('title', 'Reglas de alerta')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Reglas de alerta</h1>
        <p class="page-subtitle">Umbrales configurables usados al procesar cada lectura.</p>
    </div>
    <span class="data-source-badge">Motor de reglas</span>
</div>

<div class="dashboard-grid">
    @foreach($rules as $rule)
        <article class="card span-6">
            <div class="card-title-row">
                <div>
                    <h2 class="card-title">{{ $rule->name }}</h2>
                    <p class="card-subtitle">
                        Variable: {{ str_replace('_', ' ', $rule->variable) }}
                        {{ $rule->greenhouse ? '· '.$rule->greenhouse->name : '· Regla global' }}
                    </p>
                </div>
                <span class="badge {{
                    $rule->severity === 'critical'
                        ? 'badge-critical'
                        : ($rule->severity === 'warning' ? 'badge-warning' : 'badge-info')
                }}">
                    {{ ucfirst($rule->severity) }}
                </span>
            </div>

            <form method="POST" action="{{ route('alert-rules.update', $rule) }}">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="field field-third">
                        <label for="severity-{{ $rule->id }}">Severidad</label>
                        <select class="select" id="severity-{{ $rule->id }}" name="severity">
                            <option value="info" @selected($rule->severity === 'info')>Información</option>
                            <option value="warning" @selected($rule->severity === 'warning')>Advertencia</option>
                            <option value="critical" @selected($rule->severity === 'critical')>Crítica</option>
                        </select>
                    </div>

                    <div class="field field-third">
                        <label for="operator-{{ $rule->id }}">Operador</label>
                        <select class="select" id="operator-{{ $rule->id }}" name="operator">
                            @foreach([
                                'lt' => 'Menor que',
                                'lte' => 'Menor o igual',
                                'gt' => 'Mayor que',
                                'gte' => 'Mayor o igual',
                                'between' => 'Entre',
                                'outside' => 'Fuera de rango',
                                'equals' => 'Igual a texto',
                            ] as $value => $label)
                                <option value="{{ $value }}" @selected($rule->operator === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field field-third">
                        <label for="min-{{ $rule->id }}">Valor mínimo / comparación</label>
                        <input class="input" id="min-{{ $rule->id }}" type="number" step="0.01" name="min_value" value="{{ $rule->min_value }}">
                    </div>

                    <div class="field field-third">
                        <label for="max-{{ $rule->id }}">Valor máximo</label>
                        <input class="input" id="max-{{ $rule->id }}" type="number" step="0.01" name="max_value" value="{{ $rule->max_value }}">
                    </div>

                    <div class="field field-third">
                        <label for="comparison-{{ $rule->id }}">Valor de texto</label>
                        <input class="input" id="comparison-{{ $rule->id }}" type="text" name="comparison_value" value="{{ $rule->comparison_value }}">
                    </div>

                    <div class="field field-full">
                        <label for="title-{{ $rule->id }}">Título</label>
                        <input class="input" id="title-{{ $rule->id }}" type="text" name="title" value="{{ $rule->title }}" required>
                    </div>

                    <div class="field field-full">
                        <label for="description-{{ $rule->id }}">Descripción</label>
                        <textarea class="textarea" id="description-{{ $rule->id }}" name="description" required>{{ $rule->description }}</textarea>
                    </div>

                    <div class="field field-full">
                        <input type="hidden" name="active" value="0">
                        <label class="checkbox-row">
                            <input type="checkbox" name="active" value="1" @checked($rule->active)>
                            <span>Regla activa</span>
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">Guardar regla</button>
                </div>
            </form>
        </article>
    @endforeach
</div>
@endsection
