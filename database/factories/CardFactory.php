<?php

namespace Database\Factories;

use App\Models\Card;
use Illuminate\Database\Eloquent\Factories\Factory;

class CardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Card::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['major_arcana', 'minor_arcana'];
        $suits = ['cups', 'swords', 'wands', 'pentacles', null];
        $elements = ['air', 'water', 'fire', 'earth'];
        
        $type = $this->faker->randomElement($types);
        $suit = ($type === 'major_arcana') ? null : $this->faker->randomElement(array_slice($suits, 0, 4));
        
        return [
            'type' => $type,
            'number' => $this->faker->numberBetween(0, 21),
            'name' => $this->faker->word(),
            'suit' => $suit,
            'element' => $this->faker->randomElement($elements),
            'meaning' => $this->faker->sentence(),
        ];
    }
}