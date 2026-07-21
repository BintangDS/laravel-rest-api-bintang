<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user login with valid credentials.
     */
    public function test_user_can_login_with_valid_credentials(): void
    {
        // Create a user in the database
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);

        // Post login request
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Assert response shape and status
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'token_type',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                    ]
                ]
            ]);
    }

    /**
     * Test user login with invalid credentials.
     */
    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        // Create a user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Attempt login with incorrect password
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert invalid credentials error
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Validation failed',
                'errors' => [
                    'credentials' => ['Invalid email or password.']
                ]
            ]);
    }

    /**
     * Test user logout.
     */
    public function test_user_can_logout(): void
    {
        // Create user and token
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;

        // Perform logout request
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        // Assert response success
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'message' => 'Successfully logged out.'
                ]
            ]);

        // Assert token is deleted
        $this->assertEmpty($user->tokens()->get());
    }
}
