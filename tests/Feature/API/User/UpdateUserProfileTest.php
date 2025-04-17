<?php

namespace Tests\Feature\API\User;

use Tests\Feature\API\ApiTestCase;
use App\Models\User;

class UpdateUserProfileTest extends ApiTestCase
{
    /**
     * Test an authenticated user can update their profile
     */
    public function test_authenticated_user_can_update_profile(): void
    {
        // Setup authenticated user
        $this->createAuthenticatedUser();
        
        $updatedData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'birthdate' => '1995-05-05',
        ];
        
        // Cambiar de '/api/user/profile' a '/api/users'
        $response = $this->putJson('/api/users', $updatedData, $this->authHeaders());

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
            
        // Assert the data is updated
        $response->assertJson([
            'data' => [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'birthdate' => '05/05/1995', // Formato europeo (dd/mm/yyyy)
            ]
        ]);
        
        // Verify database was updated
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test user can update only some fields
     */
    public function test_user_can_update_partial_data(): void
    {
        // Setup authenticated user
        $this->createAuthenticatedUser();
        
        // Only update name
        // Cambiar de '/api/user/profile' a '/api/users'
        $response = $this->putJson('/api/users', [
            'name' => 'Partial Update'
        ], $this->authHeaders());

        $response->assertStatus(200);
        
        // Assert only name was changed
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Partial Update',
            'email' => $this->user->email, // Email remains unchanged
        ]);
    }

    /**
     * Test validation for email format
     */
    public function test_email_must_be_valid_format(): void
    {
        // Setup authenticated user
        $this->createAuthenticatedUser();
        
        // Cambiar de '/api/user/profile' a '/api/users'
        $response = $this->putJson('/api/users', [
            'email' => 'invalid-email'
        ], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test email must be unique
     */
    public function test_email_must_be_unique(): void
    {
        // Setup authenticated user
        $this->createAuthenticatedUser();
        
        // Create another user with a different email
        $anotherUser = User::factory()->create([
            'email' => 'another@example.com'
        ]);
        
        // Try to update to the email of another user
        // Cambiar de '/api/user/profile' a '/api/users'
        $response = $this->putJson('/api/users', [
            'email' => 'another@example.com'
        ], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test birthdate must be a valid date
     */
    public function test_birthdate_must_be_valid_date(): void
    {
        // Setup authenticated user
        $this->createAuthenticatedUser();
        
        // Cambiar de '/api/user/profile' a '/api/users'
        $response = $this->putJson('/api/users', [
            'birthdate' => 'not-a-date'
        ], $this->authHeaders());

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['birthdate']);
    }

    /**
     * Test unauthenticated user cannot update profile
     */
    public function test_unauthenticated_user_cannot_update_profile(): void
    {
        // Cambiar de '/api/user/profile' a '/api/users'
        $response = $this->putJson('/api/users', [
            'name' => 'Hacker'
        ]);
        
        $response->assertStatus(401);
    }
    
    /**
     * Test authenticated user can update a specific profile
     */
    public function test_admin_can_update_another_user(): void
    {
        // Setup authenticated user with admin role
        $this->createAuthenticatedUser();
        $this->user->assignRole('admin');
        
        // Create another user
        $anotherUser = User::factory()->create([
            'name' => 'Another User',
            'email' => 'another@example.com',
        ]);
        
        $updatedData = [
            'name' => 'Admin Updated Name',
        ];
        
        // Update the other user's profile
        $response = $this->putJson('/api/users/' . $anotherUser->id, $updatedData, $this->authHeaders());

        $response->assertStatus(200);
        
        // Verify database was updated
        $this->assertDatabaseHas('users', [
            'id' => $anotherUser->id,
            'name' => 'Admin Updated Name',
        ]);
    }
    
    /**
     * Test regular user cannot update another user's profile
     */
    public function test_regular_user_cannot_update_another_profile(): void
    {
        // Setup authenticated user
        $this->createAuthenticatedUser();
        
        // Create another user
        $anotherUser = User::factory()->create();
        
        // Try to update the other user's profile
        $response = $this->putJson('/api/users/' . $anotherUser->id, [
            'name' => 'Hacked Name'
        ], $this->authHeaders());

        $response->assertStatus(403);
        
        // Verify database was not updated
        $this->assertDatabaseMissing('users', [
            'id' => $anotherUser->id,
            'name' => 'Hacked Name',
        ]);
    }
}