<?php

namespace Tests\Feature\API\User;

use App\Models\User;
use Tests\Feature\API\ApiTestCase;

class UserManagementTest extends ApiTestCase
{
    public function test_admin_can_view_all_users()
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user1->assignRole('user');
        
        $user2 = User::factory()->create(['email' => 'user2@example.com']);
        $user2->assignRole('user');
        
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
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
            'Accept' => 'application/json',
        ])->getJson('/api/users');
        
        $response->assertStatus(200);
        $this->assertCount(3, $response->json('data'));
        $response->assertJsonFragment(['email' => 'user1@example.com']);
        $response->assertJsonFragment(['email' => 'user2@example.com']);
        $response->assertJsonFragment(['email' => 'admin@example.com']);
    }

    public function test_normal_user_cannot_view_all_users()
    {
        $this->createAuthenticatedUser();
        
        $anotherUser = User::factory()->create(['email' => 'another@example.com']);
        $anotherUser->assignRole('user');
        
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/users');
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['email' => $this->user->email]);
        $response->assertJsonMissing(['email' => 'another@example.com']);
        
        if (isset($response->json()['data'])) {
            $this->assertCount(1, $response->json('data'));
        } else {
            $this->assertTrue(isset($response->json()['email']));
        }
    }

    public function test_admin_can_view_specific_user_profile()
    {
        $regularUser = User::factory()->create(['email' => 'regular@example.com']);
        $regularUser->assignRole('user');
        
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
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
            'Accept' => 'application/json',
        ])->getJson('/api/users/' . $regularUser->id);
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['email' => 'regular@example.com']);
    }

    public function test_normal_user_cannot_view_other_user_profile()
    {
        $this->createAuthenticatedUser();
        
        $anotherUser = User::factory()->create(['email' => 'another@example.com']);
        $anotherUser->assignRole('user');
        
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/users/' . $anotherUser->id);
        
        $response->assertStatus(403);
    }

    public function test_normal_user_can_view_own_profile()
    {
        $this->createAuthenticatedUser();
        
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/users/' . $this->user->id);
        
        $response->assertStatus(200);
        $response->assertJsonFragment(['email' => $this->user->email]);
    }
    
        public function test_admin_can_see_user_roles()
    {
        $regularUser = User::factory()->create(['email' => 'regular@example.com']);
        $regularUser->assignRole('user');
        
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
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
            'Accept' => 'application/json',
        ])->getJson('/api/users/' . $regularUser->id);
        
        $response->assertStatus(200);
        $userData = $response->json('data');
        
        $this->assertArrayHasKey('roles', $userData);
        
        $this->assertContains('user', $userData['roles']);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
            'Accept' => 'application/json',
        ])->getJson('/api/users/' . $admin->id);
        
        $adminData = $response->json('data');
        
        $this->assertArrayHasKey('roles', $adminData);
        $this->assertContains('admin', $adminData['roles']);
    }

    public function test_admin_can_update_any_user_profile(): void
    {
        $regularUser = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'birthdate' => '1990-01-01',
        ]);
        $regularUser->assignRole('user');
        
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
        
        $updateData = [
            'name' => 'Updated By Admin',
            'email' => 'updated-by-admin@example.com',
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
            'Accept' => 'application/json',
        ])->putJson('/api/users/' . $regularUser->id, $updateData);
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('users', [
            'id' => $regularUser->id,
            'name' => 'Updated By Admin',
            'email' => 'updated-by-admin@example.com',
        ]);
    }
    
    public function test_user_cannot_update_another_user_profile(): void
    {
        $this->createAuthenticatedUser();
        
        $anotherUser = User::factory()->create([
            'name' => 'Another User',
            'email' => 'another@example.com',
            'birthdate' => '1990-01-01',
        ]);
        $anotherUser->assignRole('user');
        
        $updateData = [
            'name' => 'Tried To Update',
        ];
        
        $response = $this->withHeaders($this->authHeaders())
            ->putJson('/api/users/' . $anotherUser->id, $updateData);
        
        $response->assertStatus(403);
        
        $this->assertDatabaseMissing('users', [
            'id' => $anotherUser->id,
            'name' => 'Tried To Update',
        ]);
    }

    public function test_user_can_update_own_profile(): void
    {
        $this->createAuthenticatedUser();
        
        $updateData = [
            'name' => 'Self Updated',
            'email' => 'self-updated@example.com',
        ];
        
        $response = $this->withHeaders($this->authHeaders())
            ->putJson('/api/users/' . $this->user->id, $updateData);
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Self Updated',
            'email' => 'self-updated@example.com',
        ]);
    }

    public function test_admin_can_delete_any_user(): void
    {
        $regularUser = User::factory()->create([
            'name' => 'User To Delete',
            'email' => 'delete-me@example.com',
            'birthdate' => '1990-01-01',
        ]);
        $regularUser->assignRole('user');
        $regularUserId = $regularUser->id;
        
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
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken,
            'Accept' => 'application/json',
        ])->deleteJson('/api/users/' . $regularUserId);
        
        $response->assertStatus(204);
        
        $this->assertDatabaseMissing('users', [
            'id' => $regularUserId,
        ]);
    }
    
    public function test_user_cannot_delete_another_user(): void
    {
        $this->createAuthenticatedUser();
        
        $anotherUser = User::factory()->create([
            'name' => 'Another User',
            'email' => 'another@example.com',
            'birthdate' => '1990-01-01',
        ]);
        $anotherUser->assignRole('user');
        $anotherUserId = $anotherUser->id;
        
        $response = $this->withHeaders($this->authHeaders())
            ->deleteJson('/api/users/' . $anotherUserId);
        
        $response->assertStatus(403);
        
        $this->assertDatabaseHas('users', [
            'id' => $anotherUserId,
        ]);
    }
    
    public function test_user_can_delete_own_account(): void
    {
        $this->createAuthenticatedUser();
        $userId = $this->user->id;
        
        $response = $this->withHeaders($this->authHeaders())
            ->deleteJson('/api/users/' . $userId);
        
        $response->assertStatus(204);
        
        $this->assertDatabaseMissing('users', [
            'id' => $userId,
        ]);
    }
}