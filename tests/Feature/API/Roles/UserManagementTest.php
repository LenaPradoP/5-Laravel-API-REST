<?php

namespace Tests\Feature\API\User;

use App\Models\User;
use Tests\Feature\API\ApiTestCase;

class UserManagementTest extends ApiTestCase
{
    public function test_admin_can_view_all_users()
    {
        // Crear algunos usuarios adicionales en la base de datos
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user1->assignRole('user');
        
        $user2 = User::factory()->create(['email' => 'user2@example.com']);
        $user2->assignRole('user');
        
        // Crear un admin y obtener su token
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'birthdate' => '1990-01-01',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');
        
        $adminLoginResponse = $this->postJson('/api/tokens', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        
        $adminToken = $adminLoginResponse->json('token');
        
        // Solicitar la lista de usuarios como administrador
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
            'Accept' => 'application/json',
        ])->getJson('/api/users');
        
        // Verificar que la respuesta contiene la lista de todos los usuarios
        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        $response->assertJsonFragment(['email' => 'user1@example.com']);
        $response->assertJsonFragment(['email' => 'user2@example.com']);
        $response->assertJsonFragment(['email' => 'admin@example.com']);
    }

    public function test_normal_user_cannot_view_all_users()
    {
        // Crear y autenticar un usuario normal
        $this->createAuthenticatedUser();
        
        // Crear otro usuario
        $anotherUser = User::factory()->create(['email' => 'another@example.com']);
        $anotherUser->assignRole('user');
        
        // Intentar acceder al endpoint /users como usuario normal
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/users');
        
        // Verificar que solo ve su propio perfil, no una lista de usuarios
        $response->assertStatus(200);
        $response->assertJsonFragment(['email' => $this->user->email]);
        $response->assertJsonMissing(['email' => 'another@example.com']);
        
        // Confirmar que solo hay 1 usuario en la respuesta (su propio perfil)
        // La respuesta podría venir como un objeto único o dentro de un array 'data'
        if (isset($response->json()['data'])) {
            $this->assertCount(1, $response->json('data'));
        } else {
            $this->assertTrue(isset($response->json()['email']));
        }
    }

    public function test_admin_can_view_specific_user_profile()
    {
        // Crear un usuario normal
        $regularUser = User::factory()->create(['email' => 'regular@example.com']);
        $regularUser->assignRole('user');
        
        // Crear y autenticar un administrador
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'birthdate' => '1990-01-01',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');
        
        $adminLoginResponse = $this->postJson('/api/tokens', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        
        $adminToken = $adminLoginResponse->json('token');
        
        // Solicitar el perfil del usuario normal como administrador
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
            'Accept' => 'application/json',
        ])->getJson('/api/users/' . $regularUser->id);
        
        // Verificar que puede ver el perfil del usuario
        $response->assertStatus(200);
        $response->assertJsonFragment(['email' => 'regular@example.com']);
    }

    public function test_normal_user_cannot_view_other_user_profile()
    {
        // Crear y autenticar un usuario normal
        $this->createAuthenticatedUser();
        
        // Crear otro usuario normal
        $anotherUser = User::factory()->create(['email' => 'another@example.com']);
        $anotherUser->assignRole('user');
        
        // Intentar acceder al perfil del otro usuario
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/users/' . $anotherUser->id);
        
        // Verificar que no puede ver el perfil de otro usuario
        $response->assertStatus(403);
    }

    public function test_normal_user_can_view_own_profile()
    {
        // Crear y autenticar un usuario normal
        $this->createAuthenticatedUser();
        
        // Acceder a su propio perfil
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/users/' . $this->user->id);
        
        // Verificar que puede ver su propio perfil
        $response->assertStatus(200);
        $response->assertJsonFragment(['email' => $this->user->email]);
    }
    
        public function test_admin_can_see_user_roles()
    {
        // Crear usuarios con diferentes roles
        $regularUser = User::factory()->create(['email' => 'regular@example.com']);
        $regularUser->assignRole('user');
        
        // Crear y autenticar un administrador
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'birthdate' => '1990-01-01',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');
        
        $adminLoginResponse = $this->postJson('/api/tokens', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        
        $adminToken = $adminLoginResponse->json('token');
        
        // Primero, obtener el perfil específico del usuario regular como admin
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
            'Accept' => 'application/json',
        ])->getJson('/api/users/' . $regularUser->id);
        
        // Verificar que la respuesta incluye información sobre roles
        $response->assertStatus(200);
        $userData = $response->json('data');
        
        // Verificar que el campo 'roles' existe en la respuesta
        $this->assertArrayHasKey('roles', $userData);
        
        // Verificar que el usuario normal tiene el rol 'user'
        $this->assertContains('user', $userData['roles']);
        
        // Obtener perfil propio del admin
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
            'Accept' => 'application/json',
        ])->getJson('/api/users/' . $admin->id);
        
        $adminData = $response->json('data');
        
        // Verificar que el admin tiene el rol 'admin'
        $this->assertArrayHasKey('roles', $adminData);
        $this->assertContains('admin', $adminData['roles']);
    }

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