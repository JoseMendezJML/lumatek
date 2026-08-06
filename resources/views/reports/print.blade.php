<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Lumatek · {{ $greenhouse->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body { background:#fff; padding:28px; }
        .report-document { max-width:1100px; margin:0 auto; }
        .report-heading { display:flex; align-items:flex-start; justify-content:space-between; gap:20px; margin-bottom:26px; }
        .report-logo { color:var(--green-800); font-size:28px; font-weight:900; letter-spacing:.04em; }
        @page { size:A4 landscape; margin:12mm; }
    </style>
</head>
<body>
    <main class="report-document">
        <div class="report-heading">
            <div>
                <div class="report-logo">🍃 LUMATEK</div>
                <h1>Reporte de telemetría</h1>
                <p>{{ $greenhouse->name }} · {{ $greenhouse->code }}</p>
                <p>Periodo: {{ $from->format('d/m/Y H:i') }} — {{ $to->format('d/m/Y H:i') }}</p>
            </div>
            <div class="no-print">
                <button class="btn btn-primary" type="button" data-print>Imprimir / Guardar PDF</button>
            </div>
        </div>

        <div class="alert-flash warning">
            <div>
                <strong>Nota:</strong> este reporte utiliza telemetría simulada; no representa lecturas de sensores físicos.
            </div>
        </div>

        <div class="report-metrics">
            <div class="report-stat">Lecturas<strong>{{ (int) ($stats->readings_count ?? 0) }}</strong></div>
            <div class="report-stat">Temperatura promedio<strong>{{ number_format((float) ($stats->temperature_avg ?? 0), 1) }} °C</strong></div>
            <div class="report-stat">Humedad promedio<strong>{{ number_format((float) ($stats->soil_humidity_avg ?? 0), 1) }} %</strong></div>
            <div class="report-stat">Riegos<strong>{{ $irrigationSummary['count'] }}</strong></div>
        </div>

        <div class="dashboard-grid">
            <section class="card span-6">
                <h2 class="card-title">Resumen</h2>
                <table class="table">
                    <tbody>
                        <tr><th>Temperatura mínima</th><td>{{ number_format((float) ($stats->temperature_min ?? 0), 1) }} °C</td></tr>
                        <tr><th>Temperatura máxima</th><td>{{ number_format((float) ($stats->temperature_max ?? 0), 1) }} °C</td></tr>
                        <tr><th>Humedad mínima</th><td>{{ number_format((float) ($stats->soil_humidity_min ?? 0), 1) }} %</td></tr>
                        <tr><th>Humedad máxima</th><td>{{ number_format((float) ($stats->soil_humidity_max ?? 0), 1) }} %</td></tr>
                        <tr><th>Humedad ambiental promedio</th><td>{{ number_format((float) ($stats->ambient_humidity_avg ?? 0), 1) }} %</td></tr>
                        <tr><th>Luminosidad promedio</th><td>{{ number_format((float) ($stats->luminosity_avg ?? 0), 0) }} lux</td></tr>
                    </tbody>
                </table>
            </section>

            <section class="card span-6">
                <h2 class="card-title">Actividad</h2>
                <table class="table">
                    <tbody>
                        <tr><th>Alertas generadas</th><td>{{ $alertSummary['generated'] }}</td></tr>
                        <tr><th>Alertas críticas</th><td>{{ $alertSummary['critical'] }}</td></tr>
                        <tr><th>Alertas resueltas</th><td>{{ $alertSummary['resolved'] }}</td></tr>
                        <tr><th>Minutos de riego</th><td>{{ $irrigationSummary['minutes'] }}</td></tr>
                        <tr><th>Lecturas manuales</th><td>{{ (int) ($stats->manual_count ?? 0) }}</td></tr>
                        <tr><th>Lecturas automáticas</th><td>{{ (int) ($stats->auto_count ?? 0) }}</td></tr>
                    </tbody>
                </table>
            </section>

            <section class="card span-12">
                <h2 class="card-title">Detalle</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Temp.</th>
                            <th>Suelo</th>
                            <th>Ambiente</th>
                            <th>Luz</th>
                            <th>Agua</th>
                            <th>Origen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($readings as $reading)
                            <tr>
                                <td>{{ $reading->recorded_at->format('d/m/Y H:i:s') }}</td>
                                <td>{{ $reading->temperature }} °C</td>
                                <td>{{ $reading->soil_humidity }} %</td>
                                <td>{{ $reading->ambient_humidity }} %</td>
                                <td>{{ $reading->luminosity }} lux</td>
                                <td>{{ $reading->water_level }} %</td>
                                <td>{{ str_replace('_', ' ', $reading->source) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7">Sin registros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        </div>
    </main>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
