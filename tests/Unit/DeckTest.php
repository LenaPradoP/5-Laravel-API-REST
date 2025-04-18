<?php

namespace Tests\Unit;

use App\Models\Card;
use App\Models\Deck;
use App\Models\DeckCard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeckTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a deck is automatically created for a new user.
     */
    public function test_deck_is_created_when_user_registers(): void
    {
        // Arrange: We need cards in the database
        $this->seed('TarotCardsSeeder');
        
        // Act: Create a new user, which should trigger deck creation
        $user = User::factory()->create();

        // Assert: Check if a deck was created for the user
        $this->assertDatabaseHas('decks', [
            'user_id' => $user->id,
        ]);

        // Get the deck and make sure it has 78 cards
        $deck = Deck::where('user_id', $user->id)->first();
        $this->assertNotNull($deck);
        
        // The deck should have 78 cards
        $cardCount = DeckCard::where('deck_id', $deck->id)->count();
        $this->assertEquals(78, $cardCount);
    }

    /**
     * Test the relationship between decks and cards.
     */
    public function test_deck_card_relationships(): void
    {
        // Arrange: We need cards in the database
        $this->seed('TarotCardsSeeder');
        
        // Create a user and get their deck
        $user = User::factory()->create();
        $deck = Deck::where('user_id', $user->id)->first();
        
        // Get all deck cards
        $deckCards = DeckCard::where('deck_id', $deck->id)->get();
        
        // Get a deck card for testing relationships
        $deckCard = $deckCards->first();
        
        // Assert: Check the deck relationship
        $this->assertEquals($deck->id, $deckCard->deck->id);
        
        // Assert: Check the card relationship
        $card = Card::find($deckCard->card_id);
        $this->assertEquals($card->id, $deckCard->card->id);
        
        // Check that the deck has the right number of cards
        $this->assertEquals(78, $deck->cards->count());
        
        // Verify that the user can access their deck through the relationship
        $this->assertEquals($deck->id, $user->deck->id);
    }
}