<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Spread;
use App\Services\{DeckService, SpreadService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SpreadController extends Controller
{
    protected $spreadService;
    protected $deckService;
    
    public function __construct(SpreadService $spreadService, DeckService $deckService)
    {
        $this->spreadService = $spreadService;
        $this->deckService = $deckService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'spread_type' => 'required|in:' . implode(',', Spread::TYPES),
        ]);
        
        try {
            $spreadData = $this->spreadService->createSpread($validated['spread_type']);
            
            Log::info('Spread created with ID: ' . $spreadData['id']);
            
            return response()->json($spreadData, 201);
                
        } catch (\Exception $e) {
            Log::error('Error creating spread: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to create spread',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}