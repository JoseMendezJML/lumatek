<?php

namespace App\Http\Controllers;

use App\Services\IrrigationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IrrigationApiController extends Controller
{
    public function status(Request $request): JsonResponse
    {
        $greenhouse = $this->greenhouse($request);
        $event = $greenhouse->irrigationEvents()
            ->where('status', 'running')
            ->latest('started_at')
            ->first();

        return response()->json([
            'active' => (bool) $event,
            'event' => $event,
        ]);
    }

    public function start(Request $request, IrrigationService $service): JsonResponse
    {
        $data = $request->validate([
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:180'],
        ]);

        $event = $service->start(
            $this->greenhouse($request),
            (int) $data['duration_minutes'],
            $request->user()
        );

        return response()->json(['message' => 'Riego iniciado.', 'event' => $event], 201);
    }

    public function stop(Request $request, IrrigationService $service): JsonResponse
    {
        $event = $service->stop(
            $this->greenhouse($request),
            $request->user()
        );

        return response()->json([
            'message' => $event ? 'Riego detenido.' : 'No había un riego activo.',
            'event' => $event,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $greenhouse = $this->greenhouse($request);

        return response()->json(
            $greenhouse->irrigationEvents()
                ->latest('started_at')
                ->paginate(min(100, max(5, $request->integer('per_page', 25))))
        );
    }
}
