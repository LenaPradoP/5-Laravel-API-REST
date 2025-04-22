<?php

namespace Tests\Feature\Deck;

use App\Models\User;
use App\Models\Deck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class GetUserDeckTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed('TarotCardsSeeder');
    }

    /**
     * Test that an authenticated user can get their deck.
     *
     * @return void
     */
    public function test_user_can_get_their_deck(): void
    {
        // Arrange: Setup a user with a deck
        $user = User::factory()->create();
        
        // User should have a deck created via the observer
        $deck = Deck::where('user_id', $user->id)->first();
        $this->assertNotNull($deck);
        
        // Act: Authenticate as user and make request
        Passport::actingAs($user);
        $response = $this->getJson('/api/decks');
        
        // Assert: Verify the response
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
        
        // Verify that the returned deck belongs to the user
        $this->assertEquals($user->id, $response->json('data.user_id'));
        
        // Verify that we have all 78 cards
        $this->assertCount(78, $response->json('data.cards'));
    }

    /**
     * Test that unauthenticated users cannot access the deck.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_get_deck(): void
    {
        // Act: Make request without authentication
        $response = $this->getJson('/api/decks');
        
        // Assert: Verify the response is unauthorized
        $response->assertStatus(401);
    }

    /**
     * Test that a user can only see their own deck.
     *
     * @return void
     */
    public function test_user_can_only_see_own_deck(): void
    {
        // Arrange: Setup two users with decks
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        // Both users should have decks created via observer
        $deck1 = Deck::where('user_id', $user1->id)->first();
        $deck2 = Deck::where('user_id', $user2->id)->first();
        
        $this->assertNotNull($deck1);
        $this->assertNotNull($deck2);
        
        // Act: Authenticate as user1 and get deck
        Passport::actingAs($user1);
        $response = $this->getJson('/api/decks');
        
        // Assert: Verify that user1 can only see their own deck
        $response->assertStatus(200);
        $this->assertEquals($user1->id, $response->json('data.user_id'));
        $this->assertEquals($deck1->id, $response->json('data.id'));
        $this->assertNotEquals($deck2->id, $response->json('data.id'));
    }

}
