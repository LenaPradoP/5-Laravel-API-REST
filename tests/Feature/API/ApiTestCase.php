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
}
