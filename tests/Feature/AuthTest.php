<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    public function test_web_login_succeeds_and_redirects(): void
    {
        $this->admin();

        $this->post('/login', ['email' => 'admin@example.com', 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticated();
    }

    public function test_web_login_fails_with_bad_password(): void
    {
        $this->admin();

        $this->from('/login')
            ->post('/login', ['email' => 'admin@example.com', 'password' => 'nope'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_dashboard_requires_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_api_login_returns_a_token(): void
    {
        $this->admin();

        $this->postJson('/api/login', ['email' => 'admin@example.com', 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
    }

    public function test_api_login_rejects_bad_credentials(): void
    {
        $this->admin();

        $this->postJson('/api/login', ['email' => 'admin@example.com', 'password' => 'wrong'])
            ->assertStatus(401);
    }

    public function test_protected_route_needs_a_token(): void
    {
        $this->postJson('/api/hotels', ['name' => 'X', 'city' => 'Dubai', 'country' => 'UAE', 'rating' => 5])
            ->assertStatus(401);
    }

    public function test_token_holder_can_create_a_hotel(): void
    {
        Sanctum::actingAs($this->admin());

        $this->postJson('/api/hotels', ['name' => 'Token Hotel', 'city' => 'Dubai', 'country' => 'UAE', 'rating' => 5])
            ->assertCreated()
            ->assertJsonPath('data.name', 'Token Hotel');

        $this->assertDatabaseHas('hotels', ['name' => 'Token Hotel']);
    }
}
