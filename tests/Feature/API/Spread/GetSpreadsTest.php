<?php

namespace Tests\Feature\API\Spreads;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\API\ApiTestCase;
use App\Models\Card;
use App\Models\Deck;
use App\Models\Spread;
use App\Models\SpreadCard;
use App\Models\User;

class GetSpreadsTest extends ApiTestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Deck $deck;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(['TarotCardsSeeder']);
        $this->createAuthenticatedUser();
        $this->deck = Deck::where('user_id', $this->user->id)->first();
        $this->assertNotNull($this->deck, "Deck was not created");
    }

    public function test_unauthenticated_user_cannot_get_spreads()
    {        
        $response = $this->getJson('/api/spreads');
        
        $response->assertStatus(401);
    }
    
    public function test_user_can_get_list_of_spreads(): void
    {
        // Crear algunas tiradas para este usuario
        $spread1 = Spread::create([
            'deck_id' => $this->deck->id,
            'spread_type' => 'first',
            'creation_date' => now()->subDay()
        ]);
        
        $spread2 = Spread::create([
            'deck_id' => $this->deck->id,
            'spread_type' => 'second',
            'creation_date' => now()
        ]);
        
        // Añadir cartas a las tiradas
        $cards = Card::inRandomOrder()->take(4)->get();
        
        // Para la primera tirada (una carta)
        SpreadCard::create([
            'spread_id' => $spread1->id,
            'card_id' => $cards[0]->id,
            'position' => 1
        ]);
        
        // Para la segunda tirada (tres cartas)
        for ($i = 0; $i < 3; $i++) {
            SpreadCard::create([
                'spread_id' => $spread2->id,
                'card_id' => $cards[$i+1]->id,
                'position' => $i + 1
            ]);
        }
        
        $response = $this->getJson('/api/spreads', $this->authHeaders());
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'deck_id',
                    'spreads' => [
                        '*' => [
                            'id',
                            'deck_id',
                            'spread_type',
                            'creation_date',
                            'card_count'
                        ]
                    ]
                ]
            ]);
            
        $data = $response->json()['data'];
        $this->assertEquals($this->deck->id, $data['deck_id']);
        $this->assertCount(2, $data['spreads']);
    }

    public function test_user_can_filter_spreads_by_type(): void
    {
        // Crear tiradas de diferentes tipos
        Spread::create([
            'deck_id' => $this->deck->id,
            'spread_type' => 'first',
            'creation_date' => now()->subDay()
        ]);
        
        Spread::create([
            'deck_id' => $this->deck->id,
            'spread_type' => 'second',
            'creation_date' => now()
        ]);
        
        // Filtrar por tipo 'first'
        $response = $this->getJson('/api/spreads?spread_type=first', $this->authHeaders());
        
        $response->assertStatus(200);
        $data = $response->json()['data'];
        
        $this->assertCount(1, $data['spreads']);
        $this->assertEquals('first', $data['spreads'][0]['spread_type']);
    }

    public function test_returns_empty_array_when_no_spreads(): void
    {
        // No crear ninguna tirada
        
        $response = $this->getJson('/api/spreads', $this->authHeaders());
        
        $response->assertStatus(200);
        $data = $response->json()['data'];
        
        $this->assertCount(0, $data['spreads']);
    }
}