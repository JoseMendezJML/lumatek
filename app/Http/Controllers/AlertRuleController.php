<?php

namespace App\Http\Controllers;

use App\Models\AlertRule;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertRuleController extends Controller
{
    public function index(): View
    {
        $rules = AlertRule::query()
            ->with('greenhouse')
            ->orderBy('variable')
            ->orderBy('severity')
            ->get();

        return view('alert-rules.index', compact('rules'));
    }

    public function update(
        Request $request,
        AlertRule $alertRule,
        ActivityLogger $logger
    ): RedirectResponse {
        $data = $request->validate([
            'severity' => ['required', 'in:info,warning,critical'],
            'operator' => ['required', 'in:lt,lte,gt,gte,between,outside,equals'],
            'min_value' => ['nullable', 'numeric'],
            'max_value' => ['nullable', 'numeric'],
            'comparison_value' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
            'active' => ['nullable', 'boolean'],
        ]);

        $alertRule->update([
            ...$data,
            'active' => $request->boolean('active'),
        ]);

        $logger->log(
            'alert_rule.updated',
            'Se actualizó una regla de alerta.',
            $alertRule,
            [],
            $request->user()
        );

        return back()->with('success', 'Regla actualizada.');
    }
}
