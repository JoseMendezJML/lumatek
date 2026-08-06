<?php

namespace App\Http\Controllers;

use App\Models\IrrigationSchedule;
use App\Services\ActivityLogger;
use App\Services\IrrigationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IrrigationController extends Controller
{
    public function index(Request $request): View
    {
        $greenhouse = $this->greenhouse($request);
        $reading = $greenhouse->readings()->latest('recorded_at')->first();
        $running = $greenhouse->irrigationEvents()
            ->where('status', 'running')
            ->latest('started_at')
            ->first();
        $schedules = $greenhouse->irrigationSchedules()
            ->orderBy('time')
            ->get();
        $history = $greenhouse->irrigationEvents()
            ->latest('started_at')
            ->limit(12)
            ->get();

        return view('irrigation.index', compact(
            'greenhouse',
            'reading',
            'running',
            'schedules',
            'history'
        ));
    }

    public function start(Request $request, IrrigationService $service): RedirectResponse
    {
        $data = $request->validate([
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:180'],
        ]);

        $service->start(
            $this->greenhouse($request),
            (int) $data['duration_minutes'],
            $request->user()
        );

        return back()->with('success', 'Riego simulado iniciado.');
    }

    public function stop(Request $request, IrrigationService $service): RedirectResponse
    {
        $event = $service->stop(
            $this->greenhouse($request),
            $request->user()
        );

        return back()->with(
            $event ? 'success' : 'warning',
            $event ? 'Riego simulado detenido.' : 'No existe un riego en ejecución.'
        );
    }

    public function storeSchedule(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $data = $request->validate([
            'time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:180'],
            'days' => ['nullable', 'array'],
            'days.*' => ['string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
        ]);

        $schedule = IrrigationSchedule::query()->create([
            'greenhouse_id' => $this->greenhouse($request)->id,
            'time' => $data['time'],
            'duration_minutes' => $data['duration_minutes'],
            'days' => $data['days'] ?? [],
            'active' => true,
        ]);

        $logger->log(
            'irrigation.schedule_created',
            'Se creó un horario de riego.',
            $schedule,
            [],
            $request->user()
        );

        return back()->with('success', 'Horario agregado.');
    }

    public function toggleSchedule(
        Request $request,
        IrrigationSchedule $schedule
    ): RedirectResponse {
        abort_unless($schedule->greenhouse_id === $this->greenhouse($request)->id, 404);
        $schedule->update(['active' => ! $schedule->active]);

        return back()->with('success', 'Horario actualizado.');
    }

    public function destroySchedule(
        Request $request,
        IrrigationSchedule $schedule
    ): RedirectResponse {
        abort_unless($schedule->greenhouse_id === $this->greenhouse($request)->id, 404);
        $schedule->delete();

        return back()->with('success', 'Horario eliminado.');
    }
}
