<?php

namespace Tests\Feature\API\User;

use Tests\Feature\API\ApiTestCase;
use App\Models\User;

class GetUserProfileTest extends ApiTestCase
{
    /**
     * Test an authenticated user can view their own profile
     */
    public function test_authenticated_user_can_view_profile(): void
    {
        $this->createAuthenticatedUser();
        
        $response = $this->getJson('/api/users', $this->authHeaders());
    
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'birthdate',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);
            
        // Verificar que el usuario actual está en la respuesta
        $response->assertJsonFragment([
            'email' => $this->user->email,
            'name' => 'Test User',
        ]);
        
        // Verificar que solo hay un elemento en la colección
        $this->assertCount(1, $response->json('data'));
    }

    /**
     * Test unauthenticated user cannot view profile
     */
    public function test_unauthenticated_user_cannot_view_profile(): void
    {
        // Cambiar de '/api/user/profile' a '/api/users'
        $response = $this->getJson('/api/users');
        
        $response->assertStatus(401);
    }
    
    /**
     * Test authenticated user can view specific user profile by ID
     */
    public function test_authenticated_user_can_view_specific_profile(): void
    {
        $this->createAuthenticatedUser();
        
        // Crear otro usuario
        $anotherUser = User::factory()->create([
            'name' => 'Another User',
            'email' => 'another@example.com',
        ]);
        
        // Dar permiso admin al usuario actual para ver otros perfiles
        $this->user->assignRole('admin');
        
        // Probar acceso al perfil específico
        $response = $this->getJson('/api/users/' . $anotherUser->id, $this->authHeaders());

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $anotherUser->id,
                    'name' => 'Another User',
                    'email' => 'another@example.com',
                ]
            ]);
    }
    
    /**
     * Test regular user cannot view another user's profile
     */
    public function test_regular_user_cannot_view_other_profile(): void
    {
        $this->createAuthenticatedUser();
        
        // Crear otro usuario
        $anotherUser = User::factory()->create();
        
        // Intentar acceder al perfil del otro usuario
        $response = $this->getJson('/api/users/' . $anotherUser->id, $this->authHeaders());

        $response->assertStatus(403);
    }
}