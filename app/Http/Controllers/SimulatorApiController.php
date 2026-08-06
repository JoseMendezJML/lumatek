<?php

namespace App\Http\Controllers;

use App\Models\SimulationScenario;
use App\Services\SimulationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SimulatorApiController extends Controller
{
    public function reading(Request $request, SimulationService $service): JsonResponse
    {
        $data = $request->validate([
            'temperature' => ['required', 'numeric', 'between:-20,80'],
            'soil_humidity' => ['required', 'numeric', 'between:0,100'],
            'ambient_humidity' => ['required', 'numeric', 'between:0,100'],
            'luminosity' => ['required', 'numeric', 'between:0,100000'],
            'water_level' => ['required', 'numeric', 'between:0,100'],
            'irrigation_status' => ['required', 'in:inactive,active,fault'],
            'device_status' => ['required', 'in:connected,disconnected'],
        ]);

        $reading = $service->manual(
            $this->greenhouse($request),
            $data,
            $request->user()
        );

        return response()->json(['message' => 'Lectura procesada.', 'reading' => $reading], 201);
    }

    public function scenario(
        Request $request,
        SimulationScenario $scenario,
        SimulationService $service
    ): JsonResponse {
        $reading = $service->applyScenario(
            $this->greenhouse($request),
            $scenario,
            $request->user()
        );

        return response()->json(['message' => 'Escenario aplicado.', 'reading' => $reading]);
    }

    public function start(Request $request, SimulationService $service): JsonResponse
    {
        $data = $request->validate([
            'interval_seconds' => ['required', 'integer', 'min:5', 'max:3600'],
            'variation_intensity' => ['required', 'numeric', 'min:0.1', 'max:10'],
        ]);

        $control = $service->start(
            $this->greenhouse($request),
            (int) $data['interval_seconds'],
            (float) $data['variation_intensity'],
            $request->user()
        );

        return response()->json(['message' => 'Simulación iniciada.', 'control' => $control]);
    }

    public function stop(Request $request, SimulationService $service): JsonResponse
    {
        $control = $service->stop($this->greenhouse($request), $request->user());

        return response()->json(['message' => 'Simulación detenida.', 'control' => $control]);
    }
}
