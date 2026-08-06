<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $greenhouse = $this->greenhouse($request);

        return response()->json(
            $greenhouse->alerts()
                ->latest('last_triggered_at')
                ->paginate(min(100, max(5, $request->integer('per_page', 25))))
        );
    }

    public function resolve(Request $request, Alert $alert): JsonResponse
    {
        abort_unless($alert->greenhouse_id === $this->greenhouse($request)->id, 404);

        $alert->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        return response()->json(['message' => 'Alerta resuelta.', 'alert' => $alert]);
    }
}
