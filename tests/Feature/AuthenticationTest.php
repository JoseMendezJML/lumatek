<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login(): void
    {
        $this->seed();

        $response = $this->post('/login', [
            'email' => 'admin@lumatek.test',
            'password' => 'Lumatek123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'usuario@lumatek.test')->firstOrFail();
        $user->update(['active' => false]);

        $response = $this->post('/login', [
            'email' => 'usuario@lumatek.test',
            'password' => 'Lumatek123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
