<?php

namespace Tests\Feature\API\Spread;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\API\ApiTestCase;
use App\Models\Card;
use App\Models\Deck;
use App\Models\Spread;
use App\Models\SpreadCard;
use App\Models\User;

class DeleteSpreadTest extends ApiTestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $anotherUser;
    protected Deck $deck;
    protected Spread $spread;
    protected string $token;
    protected string $anotherToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(['TarotCardsSeeder']);
        
        // Crear y autenticar el usuario principal
        $this->createAuthenticatedUser();
        $this->deck = Deck::where('user_id', $this->user->id)->first();
        $this->assertNotNull($this->deck, "Deck was not created");
        
        // Crear otro usuario y autenticarlo
        $this->anotherUser = User::factory()->create([
            'name' => 'Another User',
            'email' => 'another@example.com',
            'birthdate' => '1990-01-01',
            'password' => bcrypt('password'),
        ]);
        $this->anotherUser->assignRole('user');
        
        // Obtener token para el otro usuario
        $loginResponse = $this->postJson('/api/tokens', [
            'email' => $this->anotherUser->email,
            'password' => 'password',
        ]);
        $this->anotherToken = $loginResponse->json('token');
        
        // Crear una tirada para el usuario principal
        $this->spread = Spread::create([
            'deck_id' => $this->deck->id,
            'spread_type' => 'first',
            'creation_date' => now()
        ]);
        
        // Añadir una carta a la tirada
        $card = Card::inRandomOrder()->first();
        SpreadCard::create([
            'spread_id' => $this->spread->id,
            'card_id' => $card->id,
            'position' => 1
        ]);
    }

    public function test_unauthenticated_user_cannot_delete_spread()
    {        
        $response = $this->deleteJson("/api/spreads/{$this->spread->id}");
        
        $response->assertStatus(401);
    }
    
    public function test_user_can_delete_their_own_spread()
    {
        $response = $this->deleteJson("/api/spreads/{$this->spread->id}", [], $this->authHeaders());
        
        $response->assertStatus(204);
        
        // Verificar que la tirada ya no existe en la base de datos
        $this->assertDatabaseMissing('spreads', [
            'id' => $this->spread->id
        ]);
        
        // Verificar que las cartas asociadas también se eliminaron
        $this->assertDatabaseMissing('spread_cards', [
            'spread_id' => $this->spread->id
        ]);
    }
    
    public function test_user_cannot_delete_another_users_spread()
    {
        $anotherAuthHeaders = [
            'Authorization' => 'Bearer ' . $this->anotherToken,
            'Accept' => 'application/json',
        ];
        
        $response = $this->deleteJson("/api/spreads/{$this->spread->id}", [], $anotherAuthHeaders);
        
        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You do not have permission to delete this spread.'
            ]);
            
        // Verificar que la tirada sigue existiendo
        $this->assertDatabaseHas('spreads', [
            'id' => $this->spread->id
        ]);
    }
    
    public function test_returns_404_for_nonexistent_spread()
    {
        $nonexistentId = 9999;
        
        $response = $this->deleteJson("/api/spreads/{$nonexistentId}", [], $this->authHeaders());
        
        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Spread not found.'
            ]);
    }
}
