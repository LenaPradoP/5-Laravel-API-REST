<?php

namespace Tests\Feature\API\User;

use Tests\Feature\API\ApiTestCase;

class GetUserProfileTest extends ApiTestCase
{
    /**
     * Test an authenticated user can view their own profile
     */
    public function test_authenticated_user_can_view_profile(): void
    {
        $this->createAuthenticatedUser();
        
        $response = $this->getJson('/api/user/profile', $this->authHeaders());

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'email',
                    'birthdate',
                    'created_at',
                    'updated_at'
                ]
            ]);
            
        $response->assertJson([
            'data' => [
                'id' => $this->user->id,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'birthdate' => $this->user->birthdate->format('d/m/Y')
            ]
        ]);
    }

    /**
     * Test unauthenticated user cannot view profile
     */
    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        $response = $this->getJson('/api/user/profile');
        
        $response->assertStatus(401);
    }
}