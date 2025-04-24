<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Spread;
use App\Models\SpreadCard;
use Illuminate\Database\Eloquent\Collection;

class SpreadService
{
    protected $deckService;

    public function __construct(DeckService $deckService)
    {
        $this->deckService = $deckService;
    }

    public function createSpread(string $spreadType): array
    {
        return match($spreadType) {
            Spread::TYPE_SINGLE => $this->createSingleCardSpread($spreadType),
            Spread::TYPE_THREE => $this->createThreeCardSpread($spreadType),
        };
    }

    public function createSingleCardSpread(string $spreadType = null): array
    {
        return $this->createSpreadWithCardCount(
            $spreadType ?? Spread::TYPE_SINGLE, 
            1, 
            [2]
        );
    }

    public function createThreeCardSpread(string $spreadType = null): array
    {
        return $this->createSpreadWithCardCount(
            $spreadType ?? Spread::TYPE_THREE, 
            3, 
            [2, 1, 3]
        );
    }

    private function createSpreadWithCardCount(string $spreadType, int $cardCount, array $positions): array
    {
        return $this->executeInTransaction(function() use ($spreadType, $cardCount, $positions) {
            $deck = $this->deckService->getCurrentUserDeck();
            $deck->updateLastUsed();
            
            $selectedCards = $this->selectRandomCardsFromDeck($deck, $cardCount);
            $spread = $this->createBaseSpread($deck->id, $spreadType);
            $spreadCards = $this->createSpreadCards($spread, $selectedCards, $positions);
            
            return $this->formatSpreadResponse(
                $spread, 
                collect($spreadCards)->map->toArray()->toArray()
            );
        });
    }
    
    public function getSpreadsForDeck(?string $spreadType = null): array
    {
        $deck = $this->deckService->getCurrentUserDeck();
        $query = Spread::where('deck_id', $deck->id);
        
        if ($spreadType) {
            $query->where('spread_type', $spreadType);
        }
        
        $spreads = $query->orderBy('creation_date', 'desc')->get();
        
        return [
            'deck_id' => $deck->id,
            'spreads' => $spreads
        ];
    }
    
    private function executeInTransaction(callable $callback)
    {
        DB::beginTransaction();
        
        try {
            $result = $callback();
            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function createSpreadCards(Spread $spread, Collection $deckCards, array $positions): array
    {
        $spreadCards = [];
        
        foreach ($deckCards as $index => $deckCard) {
            $position = $positions[$index] ?? ($index + 1);
            
            $spreadCard = SpreadCard::create([
                'spread_id' => $spread->id,
                'card_id' => $deckCard->card_id,
                'position' => $position
            ]);
            
            $spreadCard->setRelation('card', $deckCard->card);
            $spreadCards[] = $spreadCard;
        }
        
        return $spreadCards;
    }
    
    private function selectRandomCardsFromDeck($deck, int $count): Collection
    {
        return $deck->cards()->with('card')->get()->random($count);
    }

    private function createBaseSpread(int $deckId, string $spreadType): Spread
    {
        return Spread::create([
            'deck_id' => $deckId,
            'spread_type' => $spreadType,
            'creation_date' => now()
        ]);
    }
    
    private function formatSpreadResponse(Spread $spread, array $cards): array
    {
        return [
            'id' => $spread->id,
            'deck_id' => $spread->deck_id,
            'spread_type' => $spread->spread_type,
            'creation_date' => $spread->creation_date->format('Y-m-d H:i:s'),
            'cards' => $cards
        ];
    }
}