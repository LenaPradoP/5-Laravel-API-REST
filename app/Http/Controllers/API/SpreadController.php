<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Spread;
use App\Services\{DeckService, SpreadService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\SpreadCollection;
use App\Http\Resources\SpreadResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;

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

    public function index(Request $request)
    {
        try {
            $spreadType = $request->input('spread_type');
            $data = $this->spreadService->getSpreadsForDeck($spreadType);
            
            return new SpreadCollection(
                SpreadResource::collection($data['spreads']),
                $data['deck_id']
            );
        } catch (\Exception $e) {
            Log::error('Error fetching spreads: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to fetch spreads',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $spreadData = $this->spreadService->getSpread($id);
            
            return response()->json($spreadData);
                
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Spread not found.'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        } catch (\Exception $e) {
            Log::error('Error fetching spread: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to fetch spread',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $this->spreadService->deleteSpread($id);
            
            return response()->json(null, 204);
                
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Spread not found.'
            ], 404);
        } catch (AuthorizationException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 403);
        } catch (\Exception $e) {
            Log::error('Error deleting spread: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to delete spread',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}