<?php

namespace Tests\Feature\API\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Laravel\Passport\Passport;
use Database\Seeders\RoleSeeder;
use Database\Seeders\PassportSeeder;

class LogoutUserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(RoleSeeder::class);
        $this->seed(PassportSeeder::class);
    }

    /**
     * Test successful logout
     */
    public function test_user_can_logout_successfully(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        
        $user->assignRole('user');
        
        $loginResponse = $this->postJson('/api/tokens', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        
        $token = $loginResponse->json('token');
        
        $response = $this->deleteJson('/api/tokens', [], [
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out',
            ]);
    }

    /**
     * Test unauthorized logout attempt
     */
    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->deleteJson('/api/tokens');

        $response->assertStatus(401);
    }
}