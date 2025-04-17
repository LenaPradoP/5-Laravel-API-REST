<?php

namespace Tests\Feature\API;

use App\Models\User;
use Database\Seeders\PassportSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class ApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(RoleSeeder::class);
        $this->seed(PassportSeeder::class);
    }
    
    /**
     * Create a user and get authentication token
     */
    protected function createAuthenticatedUser(array $attributes = []): void
    {
        $defaultAttributes = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'birthdate' => '1990-01-01',
            'password' => bcrypt('password'),
        ];
        
        $this->user = User::factory()->create(array_merge($defaultAttributes, $attributes));
        
        $this->user->assignRole('user');
        
        $loginResponse = $this->postJson('/api/tokens', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);
        
        $this->token = $loginResponse->json('token');
    }
    
    /**
     * Get headers with authentication token
     */
    protected function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ];
    }
    
    /**
     * Assert validation error for required fields
     */
    protected function assertRequiredValidationFields(string $endpoint, array $fields, string $method = 'POST'): void
    {
        $response = $this->json($method, $endpoint, [], $this->authHeaders());
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors($fields);
    }
    
    /**
     * Assert validation error for email format
     */
    protected function assertEmailFormatValidation(string $endpoint, string $method = 'POST'): void
    {
        $response = $this->json($method, $endpoint, [
            'email' => 'invalid-email'
        ], $this->authHeaders());
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
    
    /**
     * Assert validation error for date format
     */
    protected function assertDateFormatValidation(string $endpoint, string $field = 'birthdate', string $method = 'POST'): void
    {
        $response = $this->json($method, $endpoint, [
            $field => 'not-a-date'
        ], $this->authHeaders());
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors([$field]);
    }
    
    /**
     * Assert unauthorized access
     */
    protected function assertUnauthorizedAccess(string $endpoint, string $method = 'GET', array $data = []): void
    {
        $response = $this->json($method, $endpoint, $data);
        
        $response->assertStatus(401);
    }
}
