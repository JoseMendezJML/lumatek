<?php

namespace App\Contracts;

use App\Models\Greenhouse;
use App\Models\TelemetryReading;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TelemetryProvider
{
    public function current(Greenhouse $greenhouse): ?TelemetryReading;

    public function history(Greenhouse $greenhouse, int $perPage = 50): LengthAwarePaginator;
}
