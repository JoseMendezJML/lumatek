<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\IrrigationEvent;
use App\Models\TelemetryReading;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        return view('reports.index', $this->reportData($request));
    }

    public function print(Request $request): View
    {
        return view('reports.print', $this->reportData($request));
    }

    private function reportData(Request $request): array
    {
        $greenhouse = $this->greenhouse($request);
        [$from, $to] = $this->dateRange($request);

        $source = $request->string('source')->toString();
        $variable = $request->string('variable')->toString();
        $severity = $request->string('severity')->toString();

        $baseReadings = TelemetryReading::query()
            ->where('greenhouse_id', $greenhouse->id)
            ->whereBetween('recorded_at', [$from, $to])
            ->when(
                in_array($source, TelemetryReading::SOURCES, true),
                fn ($query) => $query->where('source', $source)
            );

        $stats = (clone $baseReadings)
            ->selectRaw('COUNT(*) as readings_count')
            ->selectRaw('AVG(temperature) as temperature_avg')
            ->selectRaw('MIN(temperature) as temperature_min')
            ->selectRaw('MAX(temperature) as temperature_max')
            ->selectRaw('AVG(soil_humidity) as soil_humidity_avg')
            ->selectRaw('MIN(soil_humidity) as soil_humidity_min')
            ->selectRaw('MAX(soil_humidity) as soil_humidity_max')
            ->selectRaw('AVG(ambient_humidity) as ambient_humidity_avg')
            ->selectRaw('AVG(luminosity) as luminosity_avg')
            ->selectRaw("SUM(CASE WHEN source = 'simulation_manual' THEN 1 ELSE 0 END) as manual_count")
            ->selectRaw("SUM(CASE WHEN source = 'simulation_auto' THEN 1 ELSE 0 END) as auto_count")
            ->first();

        $readings = (clone $baseReadings)
            ->latest('recorded_at')
            ->limit(100)
            ->get();

        $irrigationQuery = IrrigationEvent::query()
            ->where('greenhouse_id', $greenhouse->id)
            ->whereBetween('started_at', [$from, $to]);

        $irrigationSummary = [
            'count' => (clone $irrigationQuery)->count(),
            'minutes' => (clone $irrigationQuery)->sum('duration_minutes'),
        ];

        $alertQuery = Alert::query()
            ->where('greenhouse_id', $greenhouse->id)
            ->whereBetween('last_triggered_at', [$from, $to])
            ->when(
                in_array($severity, ['info', 'warning', 'critical'], true),
                fn ($query) => $query->where('severity', $severity)
            )
            ->when(
                in_array($variable, [
                    'temperature',
                    'soil_humidity',
                    'ambient_humidity',
                    'luminosity',
                    'water_level',
                    'irrigation_status',
                    'device_status',
                ], true),
                fn ($query) => $query->where('variable', $variable)
            );

        $alertSummary = [
            'generated' => (clone $alertQuery)->count(),
            'resolved' => (clone $alertQuery)->where('status', 'resolved')->count(),
            'critical' => (clone $alertQuery)->where('severity', 'critical')->count(),
            'warning' => (clone $alertQuery)->where('severity', 'warning')->count(),
        ];

        return compact(
            'greenhouse',
            'from',
            'to',
            'stats',
            'readings',
            'irrigationSummary',
            'alertSummary',
            'source',
            'variable',
            'severity'
        );
    }

    private function dateRange(Request $request): array
    {
        $period = $request->string('period', 'week')->toString();
        $today = now();

        return match ($period) {
            'day' => [$today->copy()->startOfDay(), $today->copy()->endOfDay()],
            'month' => [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()],
            'custom' => [
                Carbon::parse($request->input('from', $today->copy()->subDays(7)->toDateString()))->startOfDay(),
                Carbon::parse($request->input('to', $today->toDateString()))->endOfDay(),
            ],
            default => [$today->copy()->subDays(7)->startOfDay(), $today->copy()->endOfDay()],
        };
    }
}
