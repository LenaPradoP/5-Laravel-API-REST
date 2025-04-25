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
}