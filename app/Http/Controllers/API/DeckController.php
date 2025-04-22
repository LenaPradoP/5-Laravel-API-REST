<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DeckService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DeckController extends Controller
{
    protected $deckService;

    public function __construct(DeckService $deckService)
    {
        $this->deckService = $deckService;
    }

    /**
     * Get the authenticated user's deck.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Verificar autenticación explícitamente
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $deck = $this->deckService->getCurrentUserDeck();
        $deckCards = $this->deckService->getDeckCards($deck);
        
        // Load the card relationship on each deck card for the response
        $deckCards->load('card');
        
        return response()->json([
            'data' => [
                'id' => $deck->id,
                'user_id' => $deck->user_id,
                'last_used' => $deck->last_used,
                'cards' => $deckCards->map(function($deckCard) {
                    return [
                        'id' => $deckCard->id,
                        'position' => $deckCard->position,
                        'card' => $deckCard->card
                    ];
                })
            ]
        ]);
    }

        /**
     * Update the deck (shuffle, cut, etc.).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'action_type' => 'required|in:shuffle,cut',
        ]);

        $deck = $this->deckService->getCurrentUserDeck();
        $actionType = $request->input('action_type');

        if ($actionType === 'shuffle') {
            $result = $this->deckService->executeShuffleDeckCards($deck);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'deck_id' => $deck->id,
                    'cards' => $this->deckService->getDeckCards($deck)
                ]
            ]);
        } else if ($actionType === 'cut') {
            $cutInfo = $this->deckService->executeCutDeckOperation($deck);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'deck_id' => $deck->id,
                    'cards' => $this->deckService->getDeckCards($deck),
                    'cut_info' => $cutInfo
                ]
            ]);
        }

        // Este código nunca debería ejecutarse debido a la validación
        return response()->json([
            'success' => false,
            'message' => 'Invalid action type'
        ], 400);
    }

}