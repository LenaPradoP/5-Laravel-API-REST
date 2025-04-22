<?php

namespace App\Services;

use App\Models\Card;
use App\Models\Deck;
use App\Models\User;
use Illuminate\Support\Collection;

class DeckService
{
    /**
     * Create a new deck for a user with all tarot cards.
     */
    public function createDeckForUser(User $user): Deck
    {
        $cards = Card::all();

        $deck = Deck::create([
            'user_id' => $user->id,
            'last_used' => now(),
        ]);

        $position = 1;
        
        foreach ($cards as $card) {
            $deck->cards()->create([
                'card_id' => $card->id,
                'position' => $position++
            ]);
        }

        return $deck;
    }

    /**
     * Get the current user's deck.
     */
    public function getUserDeck(User $user): Deck
    {
        $deck = Deck::where('user_id', $user->id)->first();
        
        if (!$deck) {
            $deck = $this->createDeckForUser($user);
        }
        
        return $deck;
    }

    /**
     * Get the cards in a deck.
     */
    public function getDeckCards(Deck $deck): Collection
    {
        return $deck->cards()->with('card')->orderBy('position', 'asc')->get();
    }
}