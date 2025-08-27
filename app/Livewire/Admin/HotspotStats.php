<?php

namespace App\Livewire\Admin;

use App\Models\Attempt;
use App\Models\Trivia;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class HotspotStats extends Component
{
    use WithPagination;

    public string $date = '';
    public string $search = '';
    public ?int $triviaId = null;
    public int $perPage = 25;

    protected $queryString = ['date','search','triviaId'];

    public function mount(): void
    {
        $this->date = $this->date ?: now()->toDateString();
    }

    public function updating($field)
    {
        if (in_array($field, ['date','search','triviaId','perPage'])) {
            $this->resetPage();
        }
    }

    protected function baseQuery(): Builder
    {
        return Attempt::query()
            ->with('trivia')
            ->when($this->date, fn($q) => $q->whereDate('created_at', $this->date))
            ->when($this->triviaId, fn($q) => $q->where('trivia_id', $this->triviaId))
            ->when($this->search, function($q){
                $s = '%'.$this->search.'%';
                $q->where(function($qq) use ($s){
                    $qq->where('identifier','like',$s)
                        ->orWhere('ip','like',$s)
                        ->orWhere('mac','like',$s)
                        ->orWhere('mikrotik_username','like',$s);
                });
            })
            ->orderByDesc('id');
    }

    public function stats(): array
    {
        $q = Attempt::query()
            ->when($this->date, fn($q) => $q->whereDate('created_at', $this->date));

        $total = (clone $q)->count();
        $correct = (clone $q)->where('is_correct', true)->count();
        $incorrect = $total - $correct;
        $avgMinutes = (clone $q)->avg('granted_minutes');
        return [
            'total' => $total,
            'correct' => $correct,
            'incorrect' => $incorrect,
            'percent_correct' => $total ? round($correct*100/$total,1) : 0,
            'avg_minutes' => round($avgMinutes,1),
        ];
    }

    public function render()
    {
        return view('livewire.admin.hotspot-stats', [
            'attempts' => $this->baseQuery()->paginate($this->perPage),
            'trivias' => Trivia::orderBy('id','desc')->get(['id','question']),
            'stats' => $this->stats(),
        ]);
    }
}
