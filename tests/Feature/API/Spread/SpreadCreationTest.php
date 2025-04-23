<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\API\ApiTestCase;
use App\Models\Deck;
use App\Models\User;

class SpreadCreationTest extends ApiTestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Deck $deck;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed('TarotCardsSeeder');
        $this->createAuthenticatedUser();
        $this->deck = Deck::where('user_id', $this->user->id)->first();
        $this->assertNotNull($this->deck, "Deck was not created");
    }

    public function test_unauthenticated_user_cannot_create_spread()
    {        
        $response = $this->postJson('/api/spreads', [
            'spread_type' => 'first'
        ]);
        
        $response->assertStatus(401);
    }
    
    public function test_spread_type_is_required()
    {
        $response = $this->postJson('/api/spreads', [], $this->authHeaders());
    
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['spread_type']);        

    }
    
    public function test_spread_type_must_be_valid()
    {
        $response = $this->postJson('/api/spreads', [
            'spread_type' => 'invalid_type'
        ], $this->authHeaders());

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['spread_type']);
    }
    
    public function test_user_can_create_single_card_spread()
    {
        $response = $this->postJson('/api/spreads', [
            'spread_type' => 'first'
        ], $this->authHeaders());
        
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'id',
                     'deck_id',
                     'spread_type',
                     'creation_date',
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
                 ]);
        
        $responseData = $response->json();
        $this->assertCount(1, $responseData['cards']);
        
        $this->assertDatabaseHas('spreads', [
            'id' => $responseData['id'],
            'deck_id' => $this->deck->id,
            'spread_type' => 'first'
        ]);
    }
    
    public function test_user_can_create_three_card_spread()
    {
        $response = $this->postJson('/api/spreads', [
            'spread_type' => 'second'
        ], $this->authHeaders());
        
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'id',
                     'deck_id',
                     'spread_type',
                     'creation_date',
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
                 ]);
        
        $responseData = $response->json();
        $this->assertCount(3, $responseData['cards']);
        
        $this->assertDatabaseHas('spreads', [
            'id' => $responseData['id'],
            'deck_id' => $this->deck->id,
            'spread_type' => 'second'
        ]);
    } 
    
}