<?php

namespace Tests\Feature\API\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class RegisterUserTest extends TestCase
{
    use RefreshDatabase, WithFaker;


    public function test_user_can_register_successfully(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'birthdate' => '1990-01-01',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'birthdate',
                    'created_at',
                ],
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

            $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasRole('user'));
    }


    public function test_register_requires_all_fields(): void
    {
        $response = $this->postJson('/api/users', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'birthdate', 'password']);
    }


    public function test_register_requires_unique_email(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $userData = [
            'name' => 'Another User',
            'email' => 'existing@example.com',
            'birthdate' => '1990-01-01',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }


    public function test_password_must_match_confirmation(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'birthdate' => '1990-01-01',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }


    public function test_password_must_be_minimum_length(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'birthdate' => '1990-01-01',
            'password' => 'short',
            'password_confirmation' => 'short',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

 
    public function test_birthdate_must_be_valid_date(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'birthdate' => 'not-a-date',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['birthdate']);
    }


    public function test_email_must_be_valid_format(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'birthdate' => '1990-01-01',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}