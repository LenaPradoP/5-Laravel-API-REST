<?php

namespace Tests\Unit;

use App\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the structure of the Card model.
     */
    public function test_card_model_has_correct_structure(): void
    {
        $card = Card::factory()->create([
            'type' => 'major_arcana',
            'number' => 0,
            'name' => 'The Fool',
            'suit' => null,
            'element' => 'air',
            'meaning' => 'New beginnings, innocence, spontaneity'
        ]);

        $this->assertDatabaseHas('cards', [
            'id' => $card->id,
            'type' => 'major_arcana',
            'number' => 0,
            'name' => 'The Fool',
            'element' => 'air',
            'meaning' => 'New beginnings, innocence, spontaneity'
        ]);

        $this->assertInstanceOf(Card::class, $card);
        $this->assertIsString($card->type);
        $this->assertIsInt($card->number);
        $this->assertIsString($card->name);
        $this->assertIsString($card->element);
        $this->assertIsString($card->meaning);
    }

    /**
     * Test that all 78 tarot cards are seeded correctly.
     */
    public function test_all_tarot_cards_are_seeded_correctly(): void
    {
        $this->artisan('db:seed', ['--class' => 'TarotCardsSeeder']);

        $this->assertEquals(78, Card::count());

        $this->assertEquals(22, Card::where('type', 'major_arcana')->count());

        $this->assertEquals(56, Card::where('type', 'minor_arcana')->count());

        $this->assertEquals(14, Card::where('suit', 'cups')->count());
        $this->assertEquals(14, Card::where('suit', 'swords')->count());
        $this->assertEquals(14, Card::where('suit', 'wands')->count());
        $this->assertEquals(14, Card::where('suit', 'pentacles')->count());
        
        $this->assertDatabaseHas('cards', [
            'type' => 'major_arcana',
            'number' => 0,
            'name' => 'The Fool'
        ]);

        $this->assertDatabaseHas('cards', [
            'type' => 'minor_arcana',
            'suit' => 'cups',
            'number' => 1,
            'name' => 'Ace of Cups'
        ]);
    }
}
