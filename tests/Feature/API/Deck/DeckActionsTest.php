<?php

namespace Tests\Feature\API\Deck;

use App\Models\User;
use App\Models\Deck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\API\ApiTestCase;

class DeckActionsTest extends ApiTestCase
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

    public function test_action_type_is_required(): void
    {        
        $response = $this->putJson('/api/decks', [], $this->authHeaders());
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['action_type']);
    }

    public function test_invalid_action_type_returns_error(): void
    {        
        $response = $this->putJson('/api/decks', [
            'action_type' => 'invalid_action'
        ], $this->authHeaders());
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['action_type']);
    }

    public function test_unauthenticated_user_cannot_shuffle_deck(): void
    {
        $response = $this->putJson('/api/decks', [
            'action_type' => 'shuffle'
        ]);
        
        $response->assertStatus(401);
    }
    
    public function test_authenticated_user_can_shuffle_deck(): void
    {
        $originalOrder = $this->deck->cards()
            ->orderBy('position', 'asc')
            ->pluck('position', 'card_id')
            ->toArray();
                
        $response = $this->putJson('/api/decks', [
            'action_type' => 'shuffle'
        ], $this->authHeaders());
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'deck_id',
                    'cards' => [
                        '*' => [
                            'id',
                            'card_id',
                            'position'
                        ]
                    ]
                ]
            ]);
        
        $this->deck->refresh();
        $newDbOrder = $this->deck->cards()
            ->orderBy('position', 'asc')
            ->pluck('position', 'card_id')
            ->toArray();
        
        $this->assertNotEquals($originalOrder, $newDbOrder);
        
        $responseCards = collect($response->json('data.cards'))->keyBy('card_id');
        $dbCards = $this->deck->cards()->get()->keyBy('card_id');
        
        foreach ($dbCards as $cardId => $dbCard) {
            $this->assertEquals(
                $dbCard->position, 
                $responseCards[$cardId]['position'], 
                "The position of card {$cardId} in the database does not match the response"
            );
        }
    }
    
    public function test_authenticated_user_can_cut_deck(): void
    {
        $originalOrder = $this->deck->cards()
            ->orderBy('position', 'asc')
            ->pluck('position', 'card_id')
            ->toArray();
                
        $response = $this->putJson('/api/decks', [
            'action_type' => 'cut'
        ], $this->authHeaders());
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'deck_id',
                    'cards' => [
                        '*' => [
                            'id',
                            'card_id',
                            'position'
                        ]
                    ],
                    'cut_info' => [
                        'first_half',
                        'second_half'
                    ]
                ]
            ]);
        
        $this->deck->refresh();
        $newDbOrder = $this->deck->cards()
            ->orderBy('position', 'asc')
            ->pluck('position', 'card_id')
            ->toArray();
        
        $this->assertNotEquals($originalOrder, $newDbOrder);
        
        $responseCards = collect($response->json('data.cards'))->keyBy('card_id');
        $dbCards = $this->deck->cards()->get()->keyBy('card_id');
        
        foreach ($dbCards as $cardId => $dbCard) {
            $this->assertEquals(
                $dbCard->position, 
                $responseCards[$cardId]['position'], 
                "The position of card {$cardId} in the database does not match the response"
            );
        }
        
        $this->assertCount(count($originalOrder), $newDbOrder);
        
        $cutInfo = $response->json('data.cut_info');
        
        $this->assertEquals(
            count($originalOrder), 
            count($cutInfo['first_half']) + count($cutInfo['second_half'])
        );
    }

}