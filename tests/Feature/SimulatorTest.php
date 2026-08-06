<?php

namespace Tests\Feature;

use App\Models\Alert;
use App\Models\SimulationScenario;
use App\Models\TelemetryReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_send_manual_reading_and_generate_alerts(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@lumatek.test')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('simulator.manual'), [
            'temperature' => 42,
            'soil_humidity' => 18,
            'ambient_humidity' => 55,
            'luminosity' => 7200,
            'water_level' => 65,
            'irrigation_status' => 'inactive',
            'device_status' => 'connected',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('telemetry_readings', [
            'temperature' => 42,
            'source' => 'simulation_manual',
        ]);
        $this->assertDatabaseHas('alerts', [
            'severity' => 'critical',
            'status' => 'new',
        ]);
    }

    public function test_normal_scenario_resolves_previous_conditions(): void
    {
        $this->seed();
        $admin = User::query()->where('email', 'admin@lumatek.test')->firstOrFail();
        $dry = SimulationScenario::query()->where('slug', 'dry-soil')->firstOrFail();
        $normal = SimulationScenario::query()->where('slug', 'normal')->firstOrFail();

        $this->actingAs($admin)->post(route('simulator.scenario', $dry));
        $this->assertTrue(Alert::query()->whereIn('status', ['new', 'viewed'])->exists());

        $this->actingAs($admin)->post(route('simulator.scenario', $normal));

        $this->assertFalse(Alert::query()->whereIn('status', ['new', 'viewed'])->exists());
        $this->assertTrue(Alert::query()->where('status', 'resolved')->exists());
    }

    public function test_regular_user_cannot_access_simulator(): void
    {
        $this->seed();
        $user = User::query()->where('email', 'usuario@lumatek.test')->firstOrFail();

        $this->actingAs($user)
            ->get(route('simulator.index'))
            ->assertForbidden();
    }
}
