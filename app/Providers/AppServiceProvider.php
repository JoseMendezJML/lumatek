<?php

namespace App\Providers;

use App\Contracts\TelemetryProvider;
use App\Services\Telemetry\IoTTelemetryProvider;
use App\Services\Telemetry\SimulationTelemetryProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TelemetryProvider::class,
            fn ($app) => config('telemetry.mode') === 'iot'
                ? $app->make(IoTTelemetryProvider::class)
                : $app->make(SimulationTelemetryProvider::class)
        );
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
