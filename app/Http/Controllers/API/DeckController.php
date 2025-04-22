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
        
        $deck = $this->deckService->getUserDeck($user);
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
}