<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Spread;
use App\Services\DeckService;
use App\Services\SpreadService;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Log};
use App\Http\Resources\SpreadCollection;
use App\Http\Resources\SpreadResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;

class SpreadController extends Controller
{
    protected $spreadService;
    protected $deckService;
    protected $roleService;
    
    public function __construct(
        SpreadService $spreadService, 
        DeckService $deckService,
        RoleService $roleService
    ) {
        $this->spreadService = $spreadService;
        $this->deckService = $deckService;
        $this->roleService = $roleService;
    }

    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $spreadType = $request->input('spread_type');
            
            if ($this->roleService->canViewAllSpreads($user)) {
                $spreads = Spread::when($spreadType, function($query) use ($spreadType) {
                    return $query->where('spread_type', $spreadType);
                })->orderBy('creation_date', 'desc')->get();
                
                return response()->json([
                    'spreads' => $spreads->toArray()
                ]);
            }
            
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
            $user = Auth::user();
            $spread = Spread::findOrFail($id);
            
            if (!$this->roleService->canViewSpread($user, $spread)) {
                return response()->json([
                    'message' => 'You do not have permission to view this spread.'
                ], 403);
            }
            
            try {
                $spreadData = $this->spreadService->getSpread($id);
                return response()->json($spreadData);
            } catch (AuthorizationException $e) {
                $spread->load('spreadCards.card');
                $cardsData = $spread->spreadCards->sortBy('position')->values()->map->toArray()->toArray();
                
                return response()->json([
                    'id' => $spread->id,
                    'deck_id' => $spread->deck_id,
                    'spread_type' => $spread->spread_type,
                    'creation_date' => $spread->creation_date->format('Y-m-d H:i:s'),
                    'cards' => $cardsData
                ]);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Spread not found.'
            ], 404);
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
            $user = Auth::user();
            $spread = Spread::findOrFail($id);
            
            if (!$this->roleService->canDeleteSpread($user, $spread)) {
                return response()->json([
                    'message' => 'You do not have permission to delete this spread.'
                ], 403);
            }
            
            try {
                $this->spreadService->deleteSpread($id);
            } catch (AuthorizationException $e) {
                $spread->delete();
            }
            
            return response()->json(null, 204);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Spread not found.'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting spread: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Failed to delete spread',
                'error' => $e->getMessage()
            ], 500);
        }
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