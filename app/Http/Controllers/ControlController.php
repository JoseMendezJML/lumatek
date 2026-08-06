<?php

namespace App\Http\Controllers;

use App\Contracts\TelemetryProvider;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ControlController extends Controller
{
    public function index(Request $request, TelemetryProvider $provider): View
    {
        $greenhouse = $this->greenhouse($request);
        $reading = $provider->current($greenhouse);
        $history = $greenhouse->irrigationEvents()
            ->latest('started_at')
            ->limit(8)
            ->get();
        $schedules = $greenhouse->irrigationSchedules()
            ->orderBy('time')
            ->get();
        $running = $greenhouse->irrigationEvents()
            ->where('status', 'running')
            ->latest('started_at')
            ->first();

        return view('control.index', compact(
            'greenhouse',
            'reading',
            'history',
            'schedules',
            'running'
        ));
    }

    public function toggleAutomatic(Request $request, ActivityLogger $logger): RedirectResponse
    {
        $greenhouse = $this->greenhouse($request);
        $greenhouse->update([
            'automatic_irrigation' => ! $greenhouse->automatic_irrigation,
        ]);

        $logger->log(
            'irrigation.automatic_toggled',
            'Se cambió el estado del riego automático.',
            $greenhouse,
            ['enabled' => $greenhouse->automatic_irrigation],
            $request->user()
        );

        return back()->with('success', 'Estado del riego automático actualizado.');
    }
}
