<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\AlertRule;
use App\Models\TelemetryReading;
use Illuminate\Support\Collection;

class AlertEvaluationService
{
    public function evaluate(TelemetryReading $reading): Collection
    {
        $rules = AlertRule::query()
            ->where('active', true)
            ->where(function ($query) use ($reading): void {
                $query->whereNull('greenhouse_id')
                    ->orWhere('greenhouse_id', $reading->greenhouse_id);
            })
            ->get();

        $triggeredFingerprints = [];
        $triggeredAlerts = collect();

        foreach ($rules as $rule) {
            $value = $reading->getAttribute($rule->variable);

            if ($value === null || ! $this->matches($rule, $value)) {
                continue;
            }

            $fingerprint = implode(':', [
                $reading->greenhouse_id,
                $rule->id,
                $rule->variable,
            ]);

            $triggeredFingerprints[] = $fingerprint;

            $alert = Alert::query()
                ->where('fingerprint', $fingerprint)
                ->whereIn('status', ['new', 'viewed'])
                ->first();

            $payload = [
                'greenhouse_id' => $reading->greenhouse_id,
                'telemetry_reading_id' => $reading->id,
                'alert_rule_id' => $rule->id,
                'variable' => $rule->variable,
                'title' => $rule->title,
                'description' => $rule->description,
                'severity' => $rule->severity,
                'value' => is_numeric($value) ? $value : null,
                'source' => $reading->source,
                'fingerprint' => $fingerprint,
                'last_triggered_at' => $reading->recorded_at,
                'resolved_at' => null,
            ];

            if ($alert) {
                $alert->update($payload);
            } else {
                $alert = Alert::query()->create(array_merge($payload, [
                    'status' => 'new',
                ]));
            }

            $triggeredAlerts->push($alert);
        }

        Alert::query()
            ->where('greenhouse_id', $reading->greenhouse_id)
            ->whereIn('status', ['new', 'viewed'])
            ->when(
                $triggeredFingerprints,
                fn ($query) => $query->whereNotIn('fingerprint', $triggeredFingerprints)
            )
            ->when(
                empty($triggeredFingerprints),
                fn ($query) => $query
            )
            ->update([
                'status' => 'resolved',
                'resolved_at' => now(),
            ]);

        return $triggeredAlerts;
    }

    private function matches(AlertRule $rule, mixed $value): bool
    {
        if ($rule->operator === 'equals') {
            return (string) $value === (string) $rule->comparison_value;
        }

        if (! is_numeric($value)) {
            return false;
        }

        $numericValue = (float) $value;
        $min = $rule->min_value !== null ? (float) $rule->min_value : null;
        $max = $rule->max_value !== null ? (float) $rule->max_value : null;

        return match ($rule->operator) {
            'lt' => $min !== null && $numericValue < $min,
            'lte' => $min !== null && $numericValue <= $min,
            'gt' => $min !== null && $numericValue > $min,
            'gte' => $min !== null && $numericValue >= $min,
            'between' => $min !== null && $max !== null
                && $numericValue >= $min && $numericValue <= $max,
            'outside' => $min !== null && $max !== null
                && ($numericValue < $min || $numericValue > $max),
            default => false,
        };
    }
}
