<?php

namespace App\Services;

use App\Models\Greenhouse;
use App\Models\TelemetryReading;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TelemetryService
{
    public function __construct(
        private readonly AlertEvaluationService $alerts,
        private readonly IrrigationService $irrigation,
        private readonly ActivityLogger $logger,
    ) {
    }

    public function store(
        Greenhouse $greenhouse,
        array $data,
        string $source,
        ?User $user = null
    ): TelemetryReading {
        return DB::transaction(function () use ($greenhouse, $data, $source, $user): TelemetryReading {
            $reading = TelemetryReading::query()->create([
                'greenhouse_id' => $greenhouse->id,
                'temperature' => $data['temperature'],
                'soil_humidity' => $data['soil_humidity'],
                'ambient_humidity' => $data['ambient_humidity'],
                'luminosity' => $data['luminosity'],
                'water_level' => $data['water_level'],
                'irrigation_status' => $data['irrigation_status'] ?? 'inactive',
                'device_status' => $data['device_status'] ?? 'connected',
                'source' => $source,
                'recorded_at' => $data['recorded_at'] ?? now(),
            ]);

            $this->alerts->evaluate($reading);
            $this->logger->log(
                'telemetry.created',
                'Se registró una lectura de telemetría.',
                $reading,
                [
                    'greenhouse_id' => $greenhouse->id,
                    'source' => $source,
                ],
                $user
            );

            $reading->load('greenhouse');
            $this->irrigation->autoStartIfNeeded($reading);

            return $reading->fresh();
        });
    }
}
