<?php

namespace Database\Seeders;

use App\Models\Card;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class TarotCardsSeeder extends Seeder
{
    public function run(): void
    {
        Log::channel('stderr')->info('🔍 TarotCardsSeeder is running');
        Card::query()->delete();
        
        try {
            $this->createMajorArcana();
            $this->createMinorArcana();
            
            $cardCount = Card::count();
            Log::channel('stderr')->info("✅ Seeded $cardCount tarot cards successfully!");
        } catch (\Exception $e) {
            Log::channel('stderr')->error('❌ Error seeding tarot cards: ' . $e->getMessage());
            Log::channel('stderr')->error($e->getTraceAsString());
            throw $e;
        }
    }

    private function createMajorArcana()
    {
        $cards = [
            [0, 'The Fool', 'air', 'Fresh start, leap of faith. "It\'s time to embark on a brand new beginning."'],
            [1, 'The Magician', 'air', 'Power, skill. "I have all the resources I need, inner and outer."'],
            [2, 'The High Priestess', 'water', 'Intuition, higher wisdom. "My inner knowing is my best guide of all."'],
            [3, 'The Empress', 'earth', 'Fertility, abundance, creativity. "Connecting to the earth reminds me that abundance is unlimited."'],
            [4, 'The Emperor', 'fire', 'Authority, father-figure. "I am my own authority. I have the will and the power to create my own life\'s structure."'],
            [5, 'The Hierophant', 'earth', 'Religion, group identity. "I choose which traditions I embrace, and how I do it."'],
            [6, 'The Lovers', 'air', 'Love, union, bonds. "My personal values system lead me to love."'],
            [7, 'The Chariot', 'water', 'Victory, assertion, momentum. "No obstacles will stop me now."'],
            [8, 'Strength', 'fire', 'Courage, self-control. "Strength begins with the choice to be kind to myself."'],
            [9, 'The Hermit', 'earth', 'Soul-searching, solitude. "I honour my spiritual self."'],
            [10, 'Wheel of Fortune', 'fire', 'Karma, turning a cycle. "I ride the waves of life."'],
            [11, 'Justice', 'air', 'Fairness, cause and effect. "I get what I give."'],
            [12, 'The Hanged Man', 'water', 'Letting go, suspension. "It\'s time for a sacred pause. Stillness grants perspective."'],
            [13, 'Death', 'water', 'Endings, beginnings. "I\'m willing to let go of a past version of myself."'],
            [14, 'Temperance', 'fire', 'Balance, healing. "I know my extremes, now I seek peace."'],
            [15, 'The Devil', 'earth', 'Bondage, restriction. "I am not a puppet."'],
            [16, 'The Tower', 'fire', 'Sudden change. "I surrender to the storm."'],
            [17, 'The Star', 'air', 'Hope, spiritual guidance. "The universe shows me that I can have faith in my dreams."'],
            [18, 'The Moon', 'water', 'Illusion, mystery, dreams. "The path may not be clear, but my intuition lights the way, one step at a time."'],
            [19, 'The Sun', 'fire', 'Success, vitality, youth. "I shine my light on the world around me and my radiance attracts more success."'],
            [20, 'Judgement', 'fire', 'Inner calling. "The daily choices I make now align me with my life\'s purpose."'],
            [21, 'The World', 'earth', 'Completion, accomplishment. "What I\'ve been working for is already done."']
        ];
    
        foreach ($cards as $card) {
            Card::create([
                'type' => 'major_arcana',
                'number' => $card[0],
                'name' => $card[1],
                'element' => $card[2],
                'meaning' => $card[3],
                'suit' => null
            ]);
        }
    }

    private function createMinorArcana()
    {
        $suits = [
            'wands' => [
                'element' => 'fire',
                'theme' => 'Action, creativity, energy'
            ],
            'cups' => [
                'element' => 'water',
                'theme' => 'Emotions, relationships, intuition'
            ],
            'swords' => [
                'element' => 'air',
                'theme' => 'Intellect, challenges, truth'
            ],
            'pentacles' => [
                'element' => 'earth',
                'theme' => 'Material world, work, health'
            ]
        ];
    
        foreach ($suits as $suit => $data) {
            for ($num = 1; $num <= 10; $num++) {
                $name = ($num === 1 ? "Ace" : $num) . " of " . ucfirst($suit);
                $meaning = $this->getNumberedMeaning($num, $suit);
                
                Card::create([
                    'type' => 'minor_arcana',
                    'number' => $num,
                    'name' => $name,
                    'suit' => $suit,
                    'element' => $data['element'],
                    'meaning' => $meaning
                ]);
            }
    
            $courtCards = [
                11 => ['title' => 'Page',    'theme' => 'New beginnings, messages'],
                12 => ['title' => 'Knight',  'theme' => 'Action, movement, ambition'],
                13 => ['title' => 'Queen',   'theme' => 'Nurturing, mastery, wisdom'],
                14 => ['title' => 'King',    'theme' => 'Authority, leadership, expertise']
            ];
    
            foreach ($courtCards as $num => $court) {
                $name = $court['title'] . " of " . ucfirst($suit);
                $meaning = $court['theme'] . " in " . strtolower($data['theme']);
                
                Card::create([
                    'type' => 'minor_arcana',
                    'number' => $num,
                    'name' => $name,
                    'suit' => $suit,
                    'element' => $data['element'],
                    'meaning' => $meaning
                ]);
            }
        }
    }
    
    private function getNumberedMeaning(int $number, string $suit): string
    {
        $meanings = [
            'wands' => [
                1 => "New creative energy. Inspiration and potential.",
                2 => "Planning future actions. A crossroads in projects.",
                3 => "Initial success through effort. Building momentum.",
                4 => "Celebration with others. Stable foundations.",
                5 => "Creative conflicts. Healthy competition.",
                6 => "Public recognition. Progress through confidence.",
                7 => "Defending your vision. Standing your ground.",
                8 => "Swift movement. News or travel related to goals.",
                9 => "Resilience under pressure. Preparing for final push.",
                10 => "Burden of responsibility. Completion of a cycle."
            ],
            'cups' => [
                1 => "New emotional beginnings. Overflowing feelings.",
                2 => "Deep connections. Romantic or spiritual bonds.",
                3 => "Friendship and joy. Emotional fulfillment.",
                4 => "Emotional stagnation. Need for renewal.",
                5 => "Loss or disappointment. Processing grief.",
                6 => "Nostalgia. Reconnecting with the past.",
                7 => "Emotional choices. Daydreams vs reality.",
                8 => "Moving on emotionally. Leaving comfort zones.",
                9 => "Wishes fulfilled. Emotional satisfaction.",
                10 => "Harmonious relationships. Family happiness."
            ],
            'swords' => [
                1 => "Mental clarity. A breakthrough idea.",
                2 => "Difficult decisions. Stalemates or balance.",
                3 => "Heartbreak. Emotional pain from truth.",
                4 => "Mental rest. Meditation and recovery.",
                5 => "Conflict with unfair advantage. Win/lose scenarios.",
                6 => "Transition after struggle. Moving forward.",
                7 => "Deception or strategy. Quick thinking required.",
                8 => "Feeling trapped mentally. Self-imposed limits.",
                9 => "Anxiety or worry. Overthinking at night.",
                10 => "Painful endings. Crisis leading to release."
            ],
            'pentacles' => [
                1 => "New financial opportunity. Seed of prosperity.",
                2 => "Balancing resources. Adapting to changes.",
                3 => "Teamwork for material goals. Skill development.",
                4 => "Financial stability. Conservative approach.",
                5 => "Material hardship. Temporary scarcity.",
                6 => "Generosity. Giving/receiving help.",
                7 => "Long-term investments. Patience with results.",
                8 => "Dedication to craft. Skillful work ethic.",
                9 => "Financial comfort. Enjoying luxuries.",
                10 => "Legacy and abundance. Family security."
            ]
        ];
    
        return $meanings[$suit][$number] ?? "Meaning not defined";
    }
}