<?php

namespace Tests\Feature;

use App\Livewire\TriviaHotspot;
use App\Models\Trivia;
use Livewire\Livewire;
use Tests\TestCase;

class TriviaHotspotTest extends TestCase
{
    public function test_trivia_component_renders(): void
    {
        $trivia = Trivia::factory()->create([
            'correct_option' => 'A'
        ]);

        Livewire::test(TriviaHotspot::class)
            ->assertSee($trivia->question);
    }
}
