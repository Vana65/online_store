<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_access_their_profile(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.email', 'user@example.com');

        $token = $response->json('access_token');

        $this->withToken($token)
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('user.email', 'user@example.com');
    }

    public function test_admin_can_login_and_access_admin_routes(): void
    {
        Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/admin/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJsonPath('admin.email', 'admin@example.com');

        $token = $response->json('access_token');

        $this->withToken($token)
            ->getJson('/api/admin/auth/me')
            ->assertOk()
            ->assertJsonPath('admin.email', 'admin@example.com');
    }

    public function test_user_token_cannot_access_admin_routes(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $token = $user->createToken('user-token', ['user'])->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/admin/auth/me')
            ->assertUnauthorized();
    }
}
