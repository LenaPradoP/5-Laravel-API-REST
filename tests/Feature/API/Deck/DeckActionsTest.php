<?php

namespace Tests\Feature\API\Deck;

use App\Models\User;
use App\Models\Deck;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\TestCase;

class DeckActionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Deck $deck;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Solo sembramos las cartas
        $this->seed('TarotCardsSeeder');
        
        // Crear un usuario para las pruebas (el deck se creará automáticamente por el observer)
        $this->user = User::factory()->create();
        
        // Obtener el mazo que se creó automáticamente
        $this->deck = Deck::where('user_id', $this->user->id)->first();
        $this->assertNotNull($this->deck, "No se creó automáticamente un mazo para el usuario");
    }

    public function test_unauthenticated_user_cannot_shuffle_deck(): void
    {
        $response = $this->putJson('/api/decks', [
            'action_type' => 'shuffle'
        ]);
        
        $response->assertStatus(401);
    }
    
    public function test_authenticated_user_can_shuffle_deck(): void
    {
        // Guardar el orden original de las cartas
        $originalOrder = $this->deck->cards()
            ->orderBy('position', 'asc')
            ->pluck('position', 'card_id')
            ->toArray();
        
        // Autenticar al usuario
        Passport::actingAs($this->user);
        
        // Realizar la acción de barajar
        $response = $this->putJson('/api/decks', [
            'action_type' => 'shuffle'
        ]);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'deck_id',
                    'cards' => [
                        '*' => [
                            'id',
                            'card_id',
                            'position'
                        ]
                    ]
                ]
            ]);
        
        // Verificar que el orden de las cartas ha cambiado en la base de datos
        $this->deck->refresh();
        $newDbOrder = $this->deck->cards()
            ->orderBy('position', 'asc')
            ->pluck('position', 'card_id')
            ->toArray();
        
        // Asegurar que el nuevo orden en la DB es diferente al original
        $this->assertNotEquals($originalOrder, $newDbOrder);
        
        // Verificar que el orden en la respuesta coincide con el guardado en la DB
        $responseCards = collect($response->json('data.cards'))->keyBy('card_id');
        $dbCards = $this->deck->cards()->get()->keyBy('card_id');
        
        foreach ($dbCards as $cardId => $dbCard) {
            $this->assertEquals(
                $dbCard->position, 
                $responseCards[$cardId]['position'], 
                "La posición de la carta {$cardId} en la base de datos no coincide con la respuesta"
            );
        }
    }
    
    public function test_invalid_action_type_returns_error(): void
    {
        // Autenticar al usuario
        Passport::actingAs($this->user);
        
        $response = $this->putJson('/api/decks', [
            'action_type' => 'invalid_action'
        ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['action_type']);
    }
    
    public function test_authenticated_user_can_cut_deck(): void
    {
        // Guardar el orden original de las cartas
        $originalOrder = $this->deck->cards()
            ->orderBy('position', 'asc')
            ->pluck('position', 'card_id')
            ->toArray();
        
        // Autenticar al usuario
        Passport::actingAs($this->user);
        
        // Realizar la acción de cortar
        $response = $this->putJson('/api/decks', [
            'action_type' => 'cut'
        ]);
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'deck_id',
                    'cards' => [
                        '*' => [
                            'id',
                            'card_id',
                            'position'
                        ]
                    ],
                    'cut_info' => [
                        'first_half',
                        'second_half'
                    ]
                ]
            ]);
        
        // Verificar que el orden de las cartas ha cambiado en la base de datos
        $this->deck->refresh();
        $newDbOrder = $this->deck->cards()
            ->orderBy('position', 'asc')
            ->pluck('position', 'card_id')
            ->toArray();
        
        // Asegurar que el nuevo orden en la DB es diferente al original
        $this->assertNotEquals($originalOrder, $newDbOrder);
        
        // Verificar que el orden en la respuesta coincide con el guardado en la DB
        $responseCards = collect($response->json('data.cards'))->keyBy('card_id');
        $dbCards = $this->deck->cards()->get()->keyBy('card_id');
        
        foreach ($dbCards as $cardId => $dbCard) {
            $this->assertEquals(
                $dbCard->position, 
                $responseCards[$cardId]['position'], 
                "La posición de la carta {$cardId} en la base de datos no coincide con la respuesta"
            );
        }
        
        // Verificar que todas las cartas están presentes después del corte
        $this->assertCount(count($originalOrder), $newDbOrder);
        
        // Verificar que la operación de corte se ha realizado correctamente
        $cutInfo = $response->json('data.cut_info');
        
        // Verificar que la suma de ambas mitades es igual al total de cartas
        $this->assertEquals(
            count($originalOrder), 
            count($cutInfo['first_half']) + count($cutInfo['second_half'])
        );
    }
    
    public function test_action_type_is_required(): void
    {
        // Autenticar al usuario
        Passport::actingAs($this->user);
        
        $response = $this->putJson('/api/decks', []);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['action_type']);
    }
}