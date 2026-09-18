<?php

namespace App\Http\Controllers;

use App\Contracts\TelemetryProvider;
use App\Http\Controllers\Concerns\AuthorizesMobileGreenhouse;
use App\Models\Alert;
use App\Models\Greenhouse;
use App\Models\IrrigationEvent;
use App\Models\IrrigationSchedule;
use App\Services\TelemetryStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Endpoint agregado pensado para la pantalla de inicio de la app móvil:
 * en una sola llamada entrega lo mismo que la web arma en varias
 * consultas (DashboardController), evitando múltiples round-trips
 * desde el dispositivo.
 */
class MobileDashboardController extends Controller
{
    use AuthorizesMobileGreenhouse;

    public function __invoke(
        Request $request,
        Greenhouse $greenhouse,
        TelemetryProvider $provider,
        TelemetryStatusService $statusService
    ): JsonResponse {
        $this->authorizeGreenhouseAccess($request, $greenhouse);

        $reading = $provider->current($greenhouse);

        $recentAlerts = Alert::query()
            ->where('greenhouse_id', $greenhouse->id)
            ->latest('last_triggered_at')
            ->limit(4)
            ->get();

        $activeIrrigation = IrrigationEvent::query()
            ->where('greenhouse_id', $greenhouse->id)
            ->where('status', 'running')
            ->latest('started_at')
            ->first();

        $nextSchedule = IrrigationSchedule::query()
            ->where('greenhouse_id', $greenhouse->id)
            ->where('active', true)
            ->orderBy('time')
            ->first();

        return response()->json([
            'greenhouse' => $greenhouse->only([
                'id', 'name', 'code', 'location', 'crop_type', 'status', 'automatic_irrigation',
            ]),
            'reading' => $reading,
            'statuses' => $statusService->metricStatuses($greenhouse, $reading),
            'recent_alerts' => $recentAlerts,
            'active_irrigation' => $activeIrrigation,
            'next_schedule' => $nextSchedule,
        ]);
    }
}
