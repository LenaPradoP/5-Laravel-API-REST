<?php

namespace Tests\Feature\API\User;

use Tests\Feature\API\ApiTestCase;
use App\Models\User;

class UserUpdateDeleteTest extends ApiTestCase
{
    /**
     * Test admin can update any user profile
     */
    public function test_admin_can_update_any_user_profile(): void
    {
        // Crear un usuario normal
        $regularUser = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'birthdate' => '1990-01-01',
        ]);
        $regularUser->assignRole('user');
        
        // Crear y autenticar un admin
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'birthdate' => '1990-01-01',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');
        
        $loginResponse = $this->postJson('/api/tokens', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        
        $adminToken = $loginResponse->json('token');
        
        // Datos de actualización
        $updateData = [
            'name' => 'Updated By Admin',
            'email' => 'updated-by-admin@example.com',
        ];
        
        // Admin intenta actualizar el perfil del usuario normal
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
            'Accept' => 'application/json',
        ])->putJson('/api/users/' . $regularUser->id, $updateData);
        
        $response->assertStatus(200);
        
        // Verificar que los datos se actualizaron
        $this->assertDatabaseHas('users', [
            'id' => $regularUser->id,
            'name' => 'Updated By Admin',
            'email' => 'updated-by-admin@example.com',
        ]);
    }
    
    /**
     * Test user cannot update another user's profile
     */
    public function test_user_cannot_update_another_user_profile(): void
    {
        // Crear y autenticar un usuario normal
        $this->createAuthenticatedUser();
        
        // Crear otro usuario
        $anotherUser = User::factory()->create([
            'name' => 'Another User',
            'email' => 'another@example.com',
            'birthdate' => '1990-01-01',
        ]);
        $anotherUser->assignRole('user');
        
        // Datos de actualización
        $updateData = [
            'name' => 'Tried To Update',
        ];
        
        // Intentar actualizar el otro usuario
        $response = $this->withHeaders($this->authHeaders())
            ->putJson('/api/users/' . $anotherUser->id, $updateData);
        
        // Debe recibir un error de permiso
        $response->assertStatus(403);
        
        // Verificar que los datos NO se actualizaron
        $this->assertDatabaseMissing('users', [
            'id' => $anotherUser->id,
            'name' => 'Tried To Update',
        ]);
    }
    
    /**
     * Test user can update own profile
     */
    public function test_user_can_update_own_profile(): void
    {
        // Crear y autenticar un usuario normal
        $this->createAuthenticatedUser();
        
        // Datos de actualización
        $updateData = [
            'name' => 'Self Updated',
            'email' => 'self-updated@example.com',
        ];
        
        // Actualizar su propio perfil
        $response = $this->withHeaders($this->authHeaders())
            ->putJson('/api/users/' . $this->user->id, $updateData);
        
        $response->assertStatus(200);
        
        // Verificar que los datos se actualizaron
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Self Updated',
            'email' => 'self-updated@example.com',
        ]);
    }
    
    /**
     * Test admin can delete any user
     */
    public function test_admin_can_delete_any_user(): void
    {
        // Crear un usuario normal
        $regularUser = User::factory()->create([
            'name' => 'User To Delete',
            'email' => 'delete-me@example.com',
            'birthdate' => '1990-01-01',
        ]);
        $regularUser->assignRole('user');
        $regularUserId = $regularUser->id;
        
        // Crear y autenticar un admin
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'birthdate' => '1990-01-01',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');
        
        $loginResponse = $this->postJson('/api/tokens', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        
        $adminToken = $loginResponse->json('token');
        
        // Admin intenta eliminar al usuario normal
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
            'Accept' => 'application/json',
        ])->deleteJson('/api/users/' . $regularUserId);
        
        $response->assertStatus(204);
        
        // Verificar que el usuario fue eliminado
        $this->assertDatabaseMissing('users', [
            'id' => $regularUserId,
        ]);
    }
    
    /**
     * Test user cannot delete another user
     */
    public function test_user_cannot_delete_another_user(): void
    {
        // Crear y autenticar un usuario normal
        $this->createAuthenticatedUser();
        
        // Crear otro usuario
        $anotherUser = User::factory()->create([
            'name' => 'Another User',
            'email' => 'another@example.com',
            'birthdate' => '1990-01-01',
        ]);
        $anotherUser->assignRole('user');
        $anotherUserId = $anotherUser->id;
        
        // Intentar eliminar al otro usuario
        $response = $this->withHeaders($this->authHeaders())
            ->deleteJson('/api/users/' . $anotherUserId);
        
        // Debe recibir un error de permiso
        $response->assertStatus(403);
        
        // Verificar que el usuario NO fue eliminado
        $this->assertDatabaseHas('users', [
            'id' => $anotherUserId,
        ]);
    }
    
    /**
     * Test user can delete own account
     */
    public function test_user_can_delete_own_account(): void
    {
        // Crear y autenticar un usuario normal
        $this->createAuthenticatedUser();
        $userId = $this->user->id;
        
        // Eliminar su propia cuenta
        $response = $this->withHeaders($this->authHeaders())
            ->deleteJson('/api/users/' . $userId);
        
        $response->assertStatus(204);
        
        // Verificar que el usuario fue eliminado
        $this->assertDatabaseMissing('users', [
            'id' => $userId,
        ]);
    }
}