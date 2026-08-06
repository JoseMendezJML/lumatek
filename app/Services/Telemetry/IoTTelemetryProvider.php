<?php

namespace App\Services\Telemetry;

use App\Contracts\TelemetryProvider;
use App\Models\Greenhouse;
use App\Models\TelemetryReading;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class IoTTelemetryProvider implements TelemetryProvider
{
    public function current(Greenhouse $greenhouse): ?TelemetryReading
    {
        // Punto de extensión para MQTT, HTTP o el protocolo IoT elegido.
        // Los dispositivos deberán guardar lecturas con source = "iot".
        return $greenhouse->readings()
            ->latest('recorded_at')
            ->first();
    }

    public function history(Greenhouse $greenhouse, int $perPage = 50): LengthAwarePaginator
    {
        return $greenhouse->readings()
            ->latest('recorded_at')
            ->paginate($perPage);
    }
}
