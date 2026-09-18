<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesMobileGreenhouse;
use App\Models\Alert;
use App\Models\Greenhouse;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileAlertController extends Controller
{
    use AuthorizesMobileGreenhouse;

    public function index(Request $request, Greenhouse $greenhouse): JsonResponse
    {
        $this->authorizeGreenhouseAccess($request, $greenhouse);

        $severity = $request->string('severity')->toString();
        $status = $request->string('status')->toString();

        $alerts = Alert::query()
            ->where('greenhouse_id', $greenhouse->id)
            ->when(
                in_array($severity, ['critical', 'warning', 'info'], true),
                fn ($query) => $query->where('severity', $severity)
            )
            ->when(
                in_array($status, ['new', 'viewed', 'resolved'], true),
                fn ($query) => $query->where('status', $status)
            )
            ->latest('last_triggered_at')
            ->paginate(min(50, max(5, $request->integer('per_page', 15))));

        return response()->json($alerts);
    }

    public function viewed(Request $request, Alert $alert): JsonResponse
    {
        $this->authorizeGreenhouseAccess($request, $alert->greenhouse);

        if ($alert->status === 'new') {
            $alert->update(['status' => 'viewed']);
        }

        return response()->json(['message' => 'Alerta marcada como vista.', 'alert' => $alert]);
    }

    public function resolve(Request $request, Alert $alert, ActivityLogger $logger): JsonResponse
    {
        $this->authorizeGreenhouseAccess($request, $alert->greenhouse);

        $alert->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        $logger->log(
            'alert.resolved',
            'Se resolvió una alerta desde la app móvil.',
            $alert,
            [],
            $request->user()
        );

        return response()->json(['message' => 'Alerta resuelta.', 'alert' => $alert]);
    }
}
