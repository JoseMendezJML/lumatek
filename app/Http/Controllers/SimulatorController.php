<?php

namespace App\Http\Controllers;

use App\Models\SimulationScenario;
use App\Services\SimulationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SimulatorController extends Controller
{
    public function index(Request $request): View
    {
        $greenhouse = $this->greenhouse($request);
        $reading = $greenhouse->readings()->latest('recorded_at')->first();
        $control = $greenhouse->simulationControl()->firstOrCreate([
            'greenhouse_id' => $greenhouse->id,
        ], [
            'status' => 'stopped',
            'interval_seconds' => 10,
            'variation_intensity' => 1,
        ]);
        $scenarios = SimulationScenario::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();
        $history = $greenhouse->readings()
            ->latest('recorded_at')
            ->limit(10)
            ->get();

        return view('simulator.index', compact(
            'greenhouse',
            'reading',
            'control',
            'scenarios',
            'history'
        ));
    }

    public function manual(Request $request, SimulationService $service): RedirectResponse
    {
        $data = $request->validate($this->readingRules());

        $service->manual(
            $this->greenhouse($request),
            $data,
            $request->user()
        );

        return back()->with('success', 'Lectura manual enviada y procesada.');
    }

    public function scenario(
        Request $request,
        SimulationScenario $scenario,
        SimulationService $service
    ): RedirectResponse {
        $service->applyScenario(
            $this->greenhouse($request),
            $scenario,
            $request->user()
        );

        return back()->with('success', "Escenario «{$scenario->name}» aplicado.");
    }

    public function start(Request $request, SimulationService $service): RedirectResponse
    {
        $data = $request->validate([
            'interval_seconds' => ['required', 'integer', 'min:5', 'max:3600'],
            'variation_intensity' => ['required', 'numeric', 'min:0.1', 'max:10'],
        ]);

        $service->start(
            $this->greenhouse($request),
            (int) $data['interval_seconds'],
            (float) $data['variation_intensity'],
            $request->user()
        );

        return back()->with('success', 'Simulación automática iniciada.');
    }

    public function pause(Request $request, SimulationService $service): RedirectResponse
    {
        $service->pause($this->greenhouse($request), $request->user());

        return back()->with('success', 'Simulación automática pausada.');
    }

    public function stop(Request $request, SimulationService $service): RedirectResponse
    {
        $service->stop($this->greenhouse($request), $request->user());

        return back()->with('success', 'Simulación automática detenida.');
    }

    public function reset(Request $request, SimulationService $service): RedirectResponse
    {
        $scenario = SimulationScenario::query()->where('slug', 'normal')->firstOrFail();

        $service->applyScenario(
            $this->greenhouse($request),
            $scenario,
            $request->user()
        );

        return back()->with('success', 'Valores normales restaurados.');
    }

    private function readingRules(): array
    {
        return [
            'temperature' => ['required', 'numeric', 'between:-20,80'],
            'soil_humidity' => ['required', 'numeric', 'between:0,100'],
            'ambient_humidity' => ['required', 'numeric', 'between:0,100'],
            'luminosity' => ['required', 'numeric', 'between:0,100000'],
            'water_level' => ['required', 'numeric', 'between:0,100'],
            'irrigation_status' => ['required', 'in:inactive,active,fault'],
            'device_status' => ['required', 'in:connected,disconnected'],
        ];
    }
}
