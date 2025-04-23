<?php

namespace Tests\Feature\Deck;

use App\Models\User;
use App\Models\Deck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\API\ApiTestCase;

class GetUserDeckTest extends ApiTestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Deck $deck;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed('TarotCardsSeeder');
        $this->createAuthenticatedUser();
        $this->deck = Deck::where('user_id', $this->user->id)->first();
        $this->assertNotNull($this->deck, "Deck was not created");
    }

    public function test_user_can_get_their_deck(): void
    {        

        $response = $this->getJson('/api/decks', $this->authHeaders());
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         'id',
                         'user_id',
                         'last_used',
                         'cards' => [
                             '*' => [
                                 'id',
                                 'position',
                                 'card' => [
                                     'id',
                                     'type',
                                     'number',
                                     'name',
                                     'suit',
                                     'element',
                                     'meaning'
                                 ]
                             ]
                         ]
                     ]
                 ]);
        
        $this->assertEquals($this->user->id, $response->json('data.user_id'));
        
        $this->assertCount(78, $response->json('data.cards'));
    }

    public function test_unauthenticated_user_cannot_get_deck(): void
    {
        $response = $this->getJson('/api/decks');
        
        $response->assertStatus(401);
    }

    public function test_user_can_only_see_own_deck(): void
    {
        $otherUser = User::factory()->create();
        $otherDeck = Deck::where('user_id', $otherUser->id)->first();
        
        $this->assertNotNull($this->deck);
        $this->assertNotNull($otherDeck);
        
        $response = $this->getJson('/api/decks', $this->authHeaders());
        
        $response->assertStatus(200);
        $this->assertEquals($this->user->id, $response->json('data.user_id'));
        $this->assertEquals($this->deck->id, $response->json('data.id'));
        $this->assertNotEquals($otherDeck->id, $response->json('data.id'));
    }

}
