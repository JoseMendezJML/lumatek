<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_mobile_user_can_login_and_read_session(): void
    {
        $this->seed();

        $login = $this->postJson('/api/mobile/login', [
            'email' => 'admin@lumatek.test',
            'password' => 'Lumatek123!',
        ]);

        $login->assertOk()
            ->assertJsonPath('user.email', 'admin@lumatek.test')
            ->assertJsonCount(1, 'greenhouses')
            ->assertJsonStructure(['token', 'user', 'greenhouses']);

        $token = $login->json('token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/mobile/me')
            ->assertOk()
            ->assertJsonPath('user.name', 'Javier Guillén');
    }

    public function test_mobile_login_rejects_invalid_credentials(): void
    {
        $this->seed();

        $this->postJson('/api/mobile/login', [
            'email' => 'admin@lumatek.test',
            'password' => 'incorrecta',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_mobile_user_cannot_access_session_without_token(): void
    {
        $this->getJson('/api/mobile/me')->assertUnauthorized();
    }
}