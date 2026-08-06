<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function index(Request $request): View
    {
        $greenhouse = $this->greenhouse($request);
        $severity = $request->string('severity')->toString();
        $status = $request->string('status')->toString();

        $alerts = Alert::query()
            ->with('greenhouse')
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
            ->paginate(12)
            ->withQueryString();

        return view('alerts.index', compact('greenhouse', 'alerts', 'severity', 'status'));
    }

    public function viewed(Request $request, Alert $alert): RedirectResponse
    {
        abort_unless($alert->greenhouse_id === $this->greenhouse($request)->id, 404);

        if ($alert->status === 'new') {
            $alert->update(['status' => 'viewed']);
        }

        return back()->with('success', 'Alerta marcada como vista.');
    }

    public function resolve(
        Request $request,
        Alert $alert,
        ActivityLogger $logger
    ): RedirectResponse {
        abort_unless($alert->greenhouse_id === $this->greenhouse($request)->id, 404);

        $alert->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        $logger->log(
            'alert.resolved',
            'Se resolvió una alerta.',
            $alert,
            [],
            $request->user()
        );

        return back()->with('success', 'Alerta resuelta.');
    }
}
