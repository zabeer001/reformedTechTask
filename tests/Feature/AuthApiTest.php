<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_signin_returns_access_token(): void
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/auth/signin', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
            'user',
        ]);
    }

    public function test_refresh_returns_new_token(): void
    {
        $token = $this->signInAndGetToken();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/refresh');

        $response->assertOk()->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
        ]);
    }

    public function test_signout_invalidates_token(): void
    {
        $token = $this->signInAndGetToken();

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/auth/signout');

        $response->assertOk()->assertJson([
            'message' => 'Signed out successfully.',
        ]);
    }

    private function createUser(): User
    {
        return User::factory()->create([
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    private function signInAndGetToken(): string
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/auth/signin', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();

        return (string) $response->json('access_token');
    }
}
