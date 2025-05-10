<?php

namespace Tests\Feature\API\Spread;

use App\Models\User;
use App\Models\Spread;
use Tests\Feature\API\ApiTestCase;

class SpreadManagementTest extends ApiTestCase
{
    protected User $adminUser;
    protected string $adminToken;
    protected Spread $userSpread;
    protected Spread $adminSpread;
    protected Spread $otherUserSpread;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->createAuthenticatedUser();
        
        $this->adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'birthdate' => '1990-01-01',
            'password' => bcrypt('password'),
        ]);
        $this->adminUser->assignRole('admin');
        
        $adminLoginResponse = $this->postJson('/api/tokens', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);
        $this->adminToken = $adminLoginResponse->json('token');
        
        $otherUser = User::factory()->create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'birthdate' => '1995-01-01',
            'password' => bcrypt('password'),
        ]);
        $otherUser->assignRole('user');
        
        $userDeck = $this->user->deck;
        $this->userSpread = Spread::create([
            'deck_id' => $userDeck->id,
            'spread_type' => 'first',
            'creation_date' => now(),
        ]);
        
        $adminDeck = $this->adminUser->deck;
        $this->adminSpread = Spread::create([
            'deck_id' => $adminDeck->id,
            'spread_type' => 'second',
            'creation_date' => now(),
        ]);
        
        $otherUserDeck = $otherUser->deck;
        $this->otherUserSpread = Spread::create([
            'deck_id' => $otherUserDeck->id,
            'spread_type' => 'first',
            'creation_date' => now(),
        ]);
    }

    protected function adminHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->adminToken,
            'Accept' => 'application/json',
        ];
    }

    public function test_admin_can_view_all_spreads()
    {
        $response = $this->getJson('/api/spreads', $this->adminHeaders());
        
        $response->assertStatus(200);
        
        // La respuesta para admin viene en formato 'spreads'
        $spreads = $response->json('spreads');
        
        // Verificar que se muestran las 3 tiradas
        $this->assertCount(3, $spreads);
        
        // Verificar que las tiradas incluyen las de todos los usuarios
        $ids = array_column($spreads, 'id');
        $this->assertContains($this->userSpread->id, $ids);
        $this->assertContains($this->adminSpread->id, $ids);
        $this->assertContains($this->otherUserSpread->id, $ids);
    }

    public function test_normal_user_can_only_view_own_spreads()
    {
        $response = $this->getJson('/api/spreads', $this->authHeaders());
        
        $response->assertStatus(200);
        
        // Verificar que la respuesta tiene el formato esperado
        $this->assertArrayHasKey('data', $response->json());
        $data = $response->json('data');
        
        // Verificar que hay spreads dentro de data
        $this->assertArrayHasKey('spreads', $data);
        $spreads = $data['spreads'];
        
        // Verificar que solo se muestra 1 tirada (la del usuario)
        $this->assertCount(1, $spreads);
        
        // Verificar que la tirada es del usuario actual y no de otros
        $ids = array_column($spreads, 'id');
        $this->assertContains($this->userSpread->id, $ids);
        $this->assertNotContains($this->otherUserSpread->id, $ids);
    }

    public function test_admin_can_view_any_spread_details()
    {
        $response = $this->getJson("/api/spreads/{$this->adminSpread->id}", $this->adminHeaders());
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $this->adminSpread->id]);
        
        $response = $this->getJson("/api/spreads/{$this->userSpread->id}", $this->adminHeaders());
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $this->userSpread->id]);
    }

    public function test_normal_user_can_only_view_own_spread_details()
    {
        $response = $this->getJson("/api/spreads/{$this->userSpread->id}", $this->authHeaders());
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $this->userSpread->id]);
        
        $response = $this->getJson("/api/spreads/{$this->otherUserSpread->id}", $this->authHeaders());
        $response->assertStatus(403);
    }

    public function test_admin_can_delete_any_spread()
    {
        // Corrección: Los headers deben ir como tercer parámetro
        $response = $this->deleteJson("/api/spreads/{$this->userSpread->id}", [], $this->adminHeaders());
        $response->assertStatus(204);
        
        $this->assertDatabaseMissing('spreads', ['id' => $this->userSpread->id]);
    }

    public function test_normal_user_can_only_delete_own_spread()
    {
        $userDeck = $this->user->deck;
        $secondUserSpread = Spread::create([
            'deck_id' => $userDeck->id,
            'spread_type' => 'first',
            'creation_date' => now(),
        ]);
        
        // Corrección: Los headers deben ir como tercer parámetro
        $response = $this->deleteJson("/api/spreads/{$this->otherUserSpread->id}", [], $this->authHeaders());
        $response->assertStatus(403);
        
        $this->assertDatabaseHas('spreads', ['id' => $this->otherUserSpread->id]);
        
        // Corrección: Los headers deben ir como tercer parámetro
        $response = $this->deleteJson("/api/spreads/{$secondUserSpread->id}", [], $this->authHeaders());
        $response->assertStatus(204);
        
        $this->assertDatabaseMissing('spreads', ['id' => $secondUserSpread->id]);
    }

    public function test_unauthorized_user_cannot_access_spreads()
    {
        $this->assertUnauthorizedAccess('/api/spreads');
        $this->assertUnauthorizedAccess("/api/spreads/{$this->userSpread->id}");
        $this->assertUnauthorizedAccess("/api/spreads/{$this->userSpread->id}", 'DELETE');
    }
}
