<?php

namespace App\Services;

use App\Models\Greenhouse;
use App\Models\SimulationControl;
use App\Models\SimulationScenario;
use App\Models\TelemetryReading;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SimulationService
{
    public function __construct(
        private readonly TelemetryService $telemetry,
        private readonly IrrigationService $irrigation,
        private readonly ActivityLogger $logger,
    ) {
    }

    public function manual(Greenhouse $greenhouse, array $data, ?User $user = null): TelemetryReading
    {
        $this->synchronizeIrrigationState($greenhouse, $data, $user, 'simulation_manual');

        return $this->telemetry->store(
            $greenhouse,
            $data,
            'simulation_manual',
            $user
        );
    }

    public function applyScenario(
        Greenhouse $greenhouse,
        SimulationScenario $scenario,
        ?User $user = null
    ): TelemetryReading {
        if (! $scenario->active) {
            throw ValidationException::withMessages([
                'scenario' => 'El escenario seleccionado está inactivo.',
            ]);
        }

        $this->synchronizeIrrigationState(
            $greenhouse,
            $scenario->values,
            $user,
            'simulation_scenario'
        );

        $reading = $this->telemetry->store(
            $greenhouse,
            $scenario->values,
            'simulation_scenario',
            $user
        );

        $this->logger->log(
            'simulation.scenario',
            "Se aplicó el escenario {$scenario->name}.",
            $scenario,
            ['greenhouse_id' => $greenhouse->id],
            $user
        );

        return $reading;
    }

    public function start(
        Greenhouse $greenhouse,
        int $intervalSeconds,
        float $intensity,
        ?User $user = null
    ): SimulationControl {
        $control = SimulationControl::query()->updateOrCreate(
            ['greenhouse_id' => $greenhouse->id],
            [
                'status' => 'running',
                'interval_seconds' => max(5, min(3600, $intervalSeconds)),
                'variation_intensity' => max(0.1, min(10, $intensity)),
                'last_generated_at' => now()->subSeconds($intervalSeconds),
            ]
        );

        $this->logger->log(
            'simulation.started',
            'Se inició la simulación automática.',
            $control,
            ['greenhouse_id' => $greenhouse->id],
            $user
        );

        return $control;
    }

    public function pause(Greenhouse $greenhouse, ?User $user = null): SimulationControl
    {
        return $this->changeStatus($greenhouse, 'paused', $user);
    }

    public function stop(Greenhouse $greenhouse, ?User $user = null): SimulationControl
    {
        return $this->changeStatus($greenhouse, 'stopped', $user);
    }

    private function changeStatus(
        Greenhouse $greenhouse,
        string $status,
        ?User $user
    ): SimulationControl {
        $control = SimulationControl::query()->firstOrCreate(
            ['greenhouse_id' => $greenhouse->id]
        );

        $control->update(['status' => $status]);

        $this->logger->log(
            'simulation.'.$status,
            "La simulación automática cambió a {$status}.",
            $control,
            ['greenhouse_id' => $greenhouse->id],
            $user
        );

        return $control;
    }

    public function tickIfDue(Greenhouse $greenhouse): ?TelemetryReading
    {
        $control = SimulationControl::query()->firstOrCreate(
            ['greenhouse_id' => $greenhouse->id],
            [
                'status' => 'stopped',
                'interval_seconds' => 10,
                'variation_intensity' => 1,
            ]
        );

        if ($control->status !== 'running') {
            return null;
        }

        if (
            $control->last_generated_at
            && $control->last_generated_at
                ->addSeconds($control->interval_seconds)
                ->isFuture()
        ) {
            return null;
        }

        return DB::transaction(function () use ($greenhouse, $control): TelemetryReading {
            $latest = $greenhouse->readings()->latest('recorded_at')->lockForUpdate()->first();

            if (! $latest) {
                throw ValidationException::withMessages([
                    'simulation' => 'No hay una lectura base para ejecutar la simulación.',
                ]);
            }

            $intensity = (float) $control->variation_intensity;
            $irrigating = $latest->irrigation_status === 'active';

            $data = [
                'temperature' => $this->clamp(
                    (float) $latest->temperature + $this->variation(0.6 * $intensity),
                    5,
                    50
                ),
                'soil_humidity' => $this->clamp(
                    (float) $latest->soil_humidity
                        + ($irrigating ? $this->positiveVariation(1.4 * $intensity) : -$this->positiveVariation(0.7 * $intensity)),
                    0,
                    100
                ),
                'ambient_humidity' => $this->clamp(
                    (float) $latest->ambient_humidity + $this->variation(1.2 * $intensity),
                    0,
                    100
                ),
                'luminosity' => $this->clamp(
                    (float) $latest->luminosity + $this->variation(250 * $intensity),
                    0,
                    100000
                ),
                'water_level' => $this->clamp(
                    (float) $latest->water_level - ($irrigating ? $this->positiveVariation(0.8 * $intensity) : 0),
                    0,
                    100
                ),
                'irrigation_status' => $latest->irrigation_status,
                'device_status' => $latest->device_status,
            ];

            $reading = $this->telemetry->store(
                $greenhouse,
                $data,
                'simulation_auto'
            );

            $control->update(['last_generated_at' => now()]);

            return $reading;
        });
    }

    private function synchronizeIrrigationState(
        Greenhouse $greenhouse,
        array $data,
        ?User $user,
        string $source
    ): void {
        $requestedStatus = $data['irrigation_status'] ?? 'inactive';
        $running = $greenhouse->irrigationEvents()
            ->where('status', 'running')
            ->exists();

        if ($requestedStatus === 'active' && ! $running) {
            $this->irrigation->start(
                $greenhouse,
                20,
                $user,
                'simulation',
                $source
            );

            return;
        }

        if (in_array($requestedStatus, ['inactive', 'fault'], true) && $running) {
            $this->irrigation->stop($greenhouse, $user);
        }
    }

    private function variation(float $maximum): float
    {
        return (mt_rand(-1000, 1000) / 1000) * $maximum;
    }

    private function positiveVariation(float $maximum): float
    {
        return (mt_rand(100, 1000) / 1000) * $maximum;
    }

    private function clamp(float $value, float $min, float $max): float
    {
        return round(max($min, min($max, $value)), 2);
    }
}
