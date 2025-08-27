<?php

namespace Database\Seeders;

use App\Models\Trivia;
use Illuminate\Database\Seeder;

class TriviaSeeder extends Seeder
{
    public function run(): void
    {
        if (Trivia::count() === 0) {
            Trivia::create([
                'question' => '¿Capital de Francia?',
                'option_a' => 'Madrid',
                'option_b' => 'París',
                'option_c' => 'Roma',
                'option_d' => 'Berlín',
                'correct_option' => 'B',
                'active' => true,
            ]);

            Trivia::create([
                'question' => '¿Resultado de 3 + 5?',
                'option_a' => '6',
                'option_b' => '7',
                'option_c' => '8',
                'option_d' => '9',
                'correct_option' => 'C',
                'active' => true,
            ]);
        }
    }
}
