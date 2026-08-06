<?php

namespace App\Http\Controllers;

use App\Contracts\TelemetryProvider;
use App\Services\TelemetryStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelemetryApiController extends Controller
{
    public function current(
        Request $request,
        TelemetryProvider $provider,
        TelemetryStatusService $statusService
    ): JsonResponse {
        $greenhouse = $this->greenhouse($request);
        $reading = $provider->current($greenhouse);

        if (! $reading) {
            return response()->json(['message' => 'No existen lecturas.'], 404);
        }

        return response()->json([
            'greenhouse' => [
                'id' => $greenhouse->id,
                'name' => $greenhouse->name,
                'code' => $greenhouse->code,
            ],
            'reading' => [
                'id' => $reading->id,
                'temperature' => (float) $reading->temperature,
                'soil_humidity' => (float) $reading->soil_humidity,
                'ambient_humidity' => (float) $reading->ambient_humidity,
                'luminosity' => (float) $reading->luminosity,
                'water_level' => (float) $reading->water_level,
                'irrigation_status' => $reading->irrigation_status,
                'device_status' => $reading->device_status,
                'source' => $reading->source,
                'recorded_at' => $reading->recorded_at?->toIso8601String(),
            ],
            'statuses' => $statusService->metricStatuses($greenhouse, $reading),
            'active_alerts' => $greenhouse->alerts()
                ->whereIn('status', ['new', 'viewed'])
                ->count(),
        ]);
    }

    public function history(Request $request, TelemetryProvider $provider): JsonResponse
    {
        $greenhouse = $this->greenhouse($request);
        $history = $provider->history(
            $greenhouse,
            min(100, max(5, $request->integer('per_page', 25)))
        );

        return response()->json($history);
    }
}
