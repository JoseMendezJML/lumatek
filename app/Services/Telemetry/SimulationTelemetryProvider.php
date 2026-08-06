<?php

namespace App\Services\Telemetry;

use App\Contracts\TelemetryProvider;
use App\Models\Greenhouse;
use App\Models\TelemetryReading;
use App\Services\IrrigationService;
use App\Services\SimulationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SimulationTelemetryProvider implements TelemetryProvider
{
    public function __construct(
        private readonly SimulationService $simulation,
        private readonly IrrigationService $irrigation,
    ) {
    }

    public function current(Greenhouse $greenhouse): ?TelemetryReading
    {
        $this->irrigation->tick($greenhouse);
        $this->simulation->tickIfDue($greenhouse);

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
