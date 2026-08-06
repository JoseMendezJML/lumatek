<?php

namespace App\Services;

use App\Models\Greenhouse;
use App\Models\TelemetryReading;

class TelemetryStatusService
{
    public function metricStatuses(Greenhouse $greenhouse, ?TelemetryReading $reading): array
    {
        if (! $reading) {
            return [
                'temperature' => 'unknown',
                'soil_humidity' => 'unknown',
                'ambient_humidity' => 'unknown',
                'luminosity' => 'unknown',
                'water_level' => 'unknown',
                'overall' => 'unknown',
            ];
        }

        $statuses = [
            'temperature' => $this->rangeStatus(
                (float) $reading->temperature,
                (float) $greenhouse->temperature_min,
                (float) $greenhouse->temperature_max,
                5
            ),
            'soil_humidity' => $this->rangeStatus(
                (float) $reading->soil_humidity,
                (float) $greenhouse->soil_humidity_min,
                (float) $greenhouse->soil_humidity_max,
                10
            ),
            'ambient_humidity' => $this->rangeStatus(
                (float) $reading->ambient_humidity,
                (float) $greenhouse->ambient_humidity_min,
                (float) $greenhouse->ambient_humidity_max,
                10
            ),
            'luminosity' => $this->rangeStatus(
                (float) $reading->luminosity,
                (float) $greenhouse->luminosity_min,
                (float) $greenhouse->luminosity_max,
                1000
            ),
            'water_level' => (float) $reading->water_level < 10
                ? 'critical'
                : ((float) $reading->water_level < (float) $greenhouse->water_level_min ? 'warning' : 'normal'),
        ];

        if ($reading->device_status !== 'connected' || in_array('critical', $statuses, true)) {
            $statuses['overall'] = 'critical';
        } elseif (in_array('warning', $statuses, true)) {
            $statuses['overall'] = 'warning';
        } else {
            $statuses['overall'] = 'normal';
        }

        return $statuses;
    }

    private function rangeStatus(
        float $value,
        float $min,
        float $max,
        float $criticalMargin
    ): string {
        if ($value < ($min - $criticalMargin) || $value > ($max + $criticalMargin)) {
            return 'critical';
        }

        if ($value < $min || $value > $max) {
            return 'warning';
        }

        return 'normal';
    }
}
