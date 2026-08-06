<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelemetryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_telemetry_endpoint_returns_expected_contract(): void
    {
        $this->seed();
        $user = User::query()->where('email', 'usuario@lumatek.test')->firstOrFail();

        $this->actingAs($user)
            ->getJson(route('api.telemetry.current'))
            ->assertOk()
            ->assertJsonStructure([
                'greenhouse' => ['id', 'name', 'code'],
                'reading' => [
                    'id',
                    'temperature',
                    'soil_humidity',
                    'ambient_humidity',
                    'luminosity',
                    'water_level',
                    'irrigation_status',
                    'device_status',
                    'source',
                    'recorded_at',
                ],
                'statuses',
                'active_alerts',
            ]);
    }
}
