<?php

namespace App\Services;

use App\Models\Card;
use App\Models\Deck;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
     * Get the current authenticated user's deck
     *
     * @return \App\Models\Deck
     * @throws \Exception if user is not authenticated
     */
    public function getCurrentUserDeck(): Deck
    {
        $user = request()->user();
        
        if (!$user) {
            throw new \Exception('Unauthenticated user');
        }
        
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

    public function executeShuffleDeckCards(Deck $deck): bool
    {
        return $this->executeInTransaction(function() use ($deck) {
            $deckCards = $this->getDeckCards($deck);
            $positionsMap = $this->getShuffledPositionsMap($deckCards);
            
            $result = $this->updateCardPositionsInBatch($positionsMap);
            $deck->updateLastUsed();
            
            return $result;
        });
    }  

    public function executeCutDeckOperation(Deck $deck): array
    {
        return $this->executeInTransaction(function() use ($deck) {
            $deckCards = $this->getDeckCards($deck);
            
            $halves = $this->splitDeckInHalf($deckCards);
            $firstHalf = $halves['firstHalf'];
            $secondHalf = $halves['secondHalf'];
            $cutInfo = $halves['cutInfo'];
            
            $positionsMap = $this->getCutPositionsMap($firstHalf, $secondHalf);
            $this->updateCardPositionsInBatch($positionsMap);
            
            $deck->updateLastUsed();
            
            return [
                'first_half' => $cutInfo['firstHalf'],
                'second_half' => $cutInfo['secondHalf']
            ];
        });
    }

    private function getShuffledPositionsMap($deckCards): array
    {
        $positions = range(1, $deckCards->count());
        shuffle($positions);
        
        $positionsMap = [];
        foreach ($deckCards as $index => $card) {
            $positionsMap[$card->id] = $positions[$index];
        }
        
        return $positionsMap;
    }

    private function splitDeckInHalf($deckCards): array
    {
        $cutPoint = intval($deckCards->count() / 2);
        
        $firstHalf = $deckCards->take($cutPoint);
        $secondHalf = $deckCards->slice($cutPoint);
        
        return [
            'firstHalf' => $firstHalf,
            'secondHalf' => $secondHalf,
            'cutInfo' => [
                'firstHalf' => $firstHalf->pluck('id')->toArray(),
                'secondHalf' => $secondHalf->pluck('id')->toArray()
            ]
        ];
    }

    private function getCutPositionsMap($firstHalf, $secondHalf): array
    {
        $combinedHalves = $secondHalf->merge($firstHalf);
        
        $positionsMap = [];
        $position = 1;
        
        foreach ($combinedHalves as $card) {
            $positionsMap[$card->id] = $position++;
        }
        
        return $positionsMap;
    }

    private function updateCardPositionsInBatch(array $cardsWithPositions): bool
    {
        if (empty($cardsWithPositions)) {
            return true;
        }
        
        $cases = $this->buildCaseStatementForPositions($cardsWithPositions);
        $ids = array_keys($cardsWithPositions);
        $idsString = implode(',', $ids);
        
        DB::update("
            UPDATE deck_cards 
            SET position = CASE id 
                {$cases}
            END
            WHERE id IN ({$idsString})
        ");
        
        return true;
    }

    private function buildCaseStatementForPositions(array $cardsWithPositions): string
    {
        $cases = [];
        
        foreach ($cardsWithPositions as $id => $position) {
            $cases[] = "WHEN {$id} THEN {$position}";
        }
        
        return implode(' ', $cases);
    }

    private function executeInTransaction(callable $operation)
    {
        DB::beginTransaction();
        
        try {
            $result = $operation();
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}