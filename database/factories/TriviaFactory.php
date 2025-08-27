<?php

namespace Database\Factories;

use App\Models\Trivia;
use Illuminate\Database\Eloquent\Factories\Factory;

class TriviaFactory extends Factory
{
    protected $model = Trivia::class;

    public function definition(): array
    {
        return [
            'question' => $this->faker->sentence(8) . '?',
            'option_a' => $this->faker->word(),
            'option_b' => $this->faker->word(),
            'option_c' => $this->faker->word(),
            'option_d' => $this->faker->word(),
            'correct_option' => 'A',
            'active' => true,
        ];
    }
}
