<?php

namespace App\Services;

use App\Models\Greenhouse;
use App\Models\IrrigationEvent;
use App\Models\TelemetryReading;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IrrigationService
{
    public function __construct(
        private readonly AlertEvaluationService $alerts,
        private readonly ActivityLogger $logger,
    ) {
    }

    public function start(
        Greenhouse $greenhouse,
        int $durationMinutes,
        ?User $user = null,
        string $type = 'manual',
        string $source = 'simulation_manual'
    ): IrrigationEvent {
        $running = $greenhouse->irrigationEvents()
            ->where('status', 'running')
            ->first();

        if ($running) {
            throw ValidationException::withMessages([
                'duration_minutes' => 'Ya existe un riego simulado en ejecución.',
            ]);
        }

        $latest = $greenhouse->readings()->latest('recorded_at')->first();

        if (! $latest) {
            throw ValidationException::withMessages([
                'duration_minutes' => 'Primero debe existir una lectura de telemetría.',
            ]);
        }

        if ((float) $latest->water_level < 1) {
            throw ValidationException::withMessages([
                'duration_minutes' => 'No hay agua disponible para iniciar el riego.',
            ]);
        }

        return DB::transaction(function () use (
            $greenhouse,
            $durationMinutes,
            $user,
            $type,
            $source,
            $latest
        ): IrrigationEvent {
            $event = IrrigationEvent::query()->create([
                'greenhouse_id' => $greenhouse->id,
                'initiated_by' => $user?->id,
                'type' => $type,
                'status' => 'running',
                'started_at' => now(),
                'duration_minutes' => $durationMinutes,
                'humidity_before' => $latest->soil_humidity,
                'source' => $source,
                'notes' => 'Riego simulado iniciado desde Lumatek.',
            ]);

            $reading = TelemetryReading::query()->create([
                'greenhouse_id' => $greenhouse->id,
                'temperature' => $latest->temperature,
                'soil_humidity' => $latest->soil_humidity,
                'ambient_humidity' => $latest->ambient_humidity,
                'luminosity' => $latest->luminosity,
                'water_level' => max(0, (float) $latest->water_level - 0.5),
                'irrigation_status' => 'active',
                'device_status' => $latest->device_status,
                'source' => $source,
                'recorded_at' => now(),
            ]);

            $this->alerts->evaluate($reading);
            $this->logger->log(
                'irrigation.started',
                "Se inició un riego simulado de {$durationMinutes} minutos.",
                $event,
                ['greenhouse_id' => $greenhouse->id],
                $user
            );

            return $event;
        });
    }

    public function autoStartIfNeeded(TelemetryReading $reading): ?IrrigationEvent
    {
        $greenhouse = $reading->greenhouse;

        if (
            ! $greenhouse->automatic_irrigation
            || $reading->irrigation_status !== 'inactive'
            || $reading->device_status !== 'connected'
            || (float) $reading->soil_humidity >= (float) $greenhouse->soil_humidity_min
            || (float) $reading->water_level <= (float) $greenhouse->water_level_min
        ) {
            return null;
        }

        $running = $greenhouse->irrigationEvents()
            ->where('status', 'running')
            ->exists();

        if ($running) {
            return null;
        }

        return $this->start(
            $greenhouse,
            20,
            null,
            'automatic',
            'simulation_auto'
        );
    }

    public function stop(
        Greenhouse $greenhouse,
        ?User $user = null,
        string $status = 'stopped'
    ): ?IrrigationEvent {
        $event = $greenhouse->irrigationEvents()
            ->where('status', 'running')
            ->latest('started_at')
            ->first();

        if (! $event) {
            return null;
        }

        return DB::transaction(function () use ($greenhouse, $event, $user, $status): IrrigationEvent {
            $latest = $greenhouse->readings()->latest('recorded_at')->firstOrFail();
            $elapsedMinutes = max(
                1,
                (int) ceil($event->started_at->diffInSeconds(now()) / 60)
            );
            $effectiveMinutes = min($elapsedMinutes, $event->duration_minutes);
            $humidityGain = min(30, $effectiveMinutes * 0.8);
            $finalHumidity = min(100, (float) $event->humidity_before + $humidityGain);

            $event->update([
                'status' => $status,
                'ended_at' => now(),
                'humidity_after' => $finalHumidity,
            ]);

            $reading = TelemetryReading::query()->create([
                'greenhouse_id' => $greenhouse->id,
                'temperature' => $latest->temperature,
                'soil_humidity' => $finalHumidity,
                'ambient_humidity' => $latest->ambient_humidity,
                'luminosity' => $latest->luminosity,
                'water_level' => max(0, (float) $latest->water_level - ($effectiveMinutes * 0.2)),
                'irrigation_status' => 'inactive',
                'device_status' => $latest->device_status,
                'source' => $event->source,
                'recorded_at' => now(),
            ]);

            $this->alerts->evaluate($reading);
            $this->logger->log(
                'irrigation.'.$status,
                'Se finalizó el riego simulado.',
                $event,
                [
                    'greenhouse_id' => $greenhouse->id,
                    'humidity_after' => $finalHumidity,
                ],
                $user
            );

            return $event;
        });
    }

    public function tick(Greenhouse $greenhouse): void
    {
        $event = $greenhouse->irrigationEvents()
            ->where('status', 'running')
            ->latest('started_at')
            ->first();

        if (! $event) {
            return;
        }

        if ($event->started_at->addMinutes($event->duration_minutes)->isPast()) {
            $this->stop($greenhouse, null, 'completed');
        }
    }
}
