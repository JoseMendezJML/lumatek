<?php

namespace Tests\Feature;

use App\Models\IrrigationEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IrrigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_start_simulated_irrigation(): void
    {
        $this->seed();
        $user = User::query()->where('email', 'usuario@lumatek.test')->firstOrFail();

        $response = $this->actingAs($user)->post(route('irrigation.start'), [
            'duration_minutes' => 20,
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('irrigation_events', [
            'status' => 'running',
            'duration_minutes' => 20,
        ]);
        $this->assertDatabaseHas('telemetry_readings', [
            'irrigation_status' => 'active',
        ]);
    }
}
