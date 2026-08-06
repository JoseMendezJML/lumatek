<?php

namespace App\Http\Controllers;

use App\Contracts\TelemetryProvider;
use App\Models\Alert;
use App\Models\IrrigationEvent;
use App\Models\IrrigationSchedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, TelemetryProvider $provider): View
    {
        $greenhouse = $this->greenhouse($request);
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

        return view('dashboard.index', compact(
            'greenhouse',
            'reading',
            'recentAlerts',
            'activeIrrigation',
            'nextSchedule'
        ));
    }
}
