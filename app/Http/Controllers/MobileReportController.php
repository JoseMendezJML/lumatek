<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesMobileGreenhouse;
use App\Models\Alert;
use App\Models\Greenhouse;
use App\Models\IrrigationEvent;
use App\Models\TelemetryReading;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileReportController extends Controller
{
    use AuthorizesMobileGreenhouse;

    public function __invoke(Request $request, Greenhouse $greenhouse): JsonResponse
    {
        $this->authorizeGreenhouseAccess($request, $greenhouse);

        $from = now()->subDays(max(1, min(365, $request->integer('days', 7))))->startOfDay();
        $to = now()->endOfDay();

        $stats = TelemetryReading::query()
            ->where('greenhouse_id', $greenhouse->id)
            ->whereBetween('recorded_at', [$from, $to])
            ->selectRaw('COUNT(*) as readings')
            ->selectRaw('AVG(temperature) as temperature_avg')
            ->selectRaw('AVG(soil_humidity) as soil_humidity_avg')
            ->selectRaw('AVG(ambient_humidity) as ambient_humidity_avg')
            ->selectRaw('AVG(luminosity) as luminosity_avg')
            ->first();

        return response()->json([
            'greenhouse' => $greenhouse->only(['id', 'name', 'code']),
            'range' => ['from' => $from->toIso8601String(), 'to' => $to->toIso8601String()],
            'telemetry' => $stats,
            'irrigations' => IrrigationEvent::query()
                ->where('greenhouse_id', $greenhouse->id)
                ->whereBetween('started_at', [$from, $to])
                ->count(),
            'alerts' => Alert::query()
                ->where('greenhouse_id', $greenhouse->id)
                ->whereBetween('last_triggered_at', [$from, $to])
                ->count(),
        ]);
    }
}
