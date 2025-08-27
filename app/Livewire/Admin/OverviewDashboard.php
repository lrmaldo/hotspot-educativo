<?php

namespace App\Livewire\Admin;

use App\Models\Attempt;
use App\Models\RouterDevice;
use App\Models\Trivia;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\Lazy;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Lazy]
class OverviewDashboard extends Component
{
    public string $range = 'today'; // today|7d|30d

    protected function periodBounds(): array
    {
        $now = now();
        return match($this->range) {
            '7d' => [$now->copy()->subDays(6)->startOfDay(), $now],
            '30d' => [$now->copy()->subDays(29)->startOfDay(), $now],
            default => [$now->copy()->startOfDay(), $now],
        };
    }

    protected function attemptsBase()
    {
        [$from,$to] = $this->periodBounds();
        return Attempt::query()->whereBetween('created_at', [$from, $to]);
    }

    protected function computeMetrics(): array
    {
        $today = today();
        $base = $this->attemptsBase();
        $total = (clone $base)->count();
        $correct = (clone $base)->where('is_correct', true)->count();
        $offline = (clone $base)->where('offline', true)->count();
        $avgMinutes = (clone $base)->avg('granted_minutes');
        $avgAttemptsPerSuccess = $correct ? round($total / $correct, 2) : null;

        // Top trivias por tasa de acierto (mínimo 3 intentos en periodo)
        $topTrivias = (clone $base)
            ->selectRaw('trivia_id, COUNT(*) total, SUM(CASE WHEN is_correct=1 THEN 1 ELSE 0 END) corrects')
            ->groupBy('trivia_id')
            ->havingRaw('COUNT(*) >= 3')
            ->orderByRaw(' (SUM(CASE WHEN is_correct=1 THEN 1 ELSE 0 END) / COUNT(*)) DESC ')
            ->limit(5)
            ->get()
            ->map(function($row){
                $trivia = Trivia::find($row->trivia_id);
                $pct = $row->total ? round($row->corrects * 100 / $row->total,1) : 0;
                return [
                    'id' => $row->trivia_id,
                    'question' => $trivia ? Str::limit($trivia->question, 60) : '—',
                    'total' => $row->total,
                    'pct' => $pct,
                ];
            });

        $activeTrivias = Trivia::where('active', true)->count();
        $triviasToday = Trivia::whereDate('valid_on', $today)->count();

        $routersTotal = RouterDevice::count();
        $routersEnabled = RouterDevice::where('enabled', true)->count();
        $defaultRouter = RouterDevice::where('is_default', true)->first();

        // Últimos intentos (independiente del rango, solo rápida vista)
    $recentAttempts = Attempt::latest()->limit(6)->get(['id','is_correct','granted_minutes','created_at','mikrotik_username','offline']);

    $series = $this->dailySeries();

        return [
            'period_label' => match($this->range){ '7d' => 'Últimos 7 días', '30d' => 'Últimos 30 días', default => 'Hoy' },
            'total' => $total,
            'correct' => $correct,
            'incorrect' => $total - $correct,
            'percent_correct' => $total ? round($correct * 100 / $total,1) : 0,
            'offline' => $offline,
            'avg_minutes' => $avgMinutes ? round($avgMinutes,1) : 0,
            'avg_attempts_per_success' => $avgAttemptsPerSuccess,
            'active_trivias' => $activeTrivias,
            'trivias_today' => $triviasToday,
            'routers_total' => $routersTotal,
            'routers_enabled' => $routersEnabled,
            'default_router' => $defaultRouter?->name,
            'top_trivias' => $topTrivias,
            'recent_attempts' => $recentAttempts,
            'series' => $series,
        ];
    }

    protected function dailySeries(): array
    {
        $rows = $this->attemptsBase()
            ->selectRaw("DATE(created_at) as d, COUNT(*) total, SUM(CASE WHEN is_correct=1 THEN 1 ELSE 0 END) corrects, SUM(CASE WHEN offline=1 THEN 1 ELSE 0 END) offline_cnt, AVG(granted_minutes) avg_minutes")
            ->groupBy('d')
            ->orderBy('d')
            ->get();
        return $rows->map(function($r){
            $incorrect = $r->total - $r->corrects;
            $pct = $r->total ? round($r->corrects*100/$r->total,1) : 0;
            return [
                'date' => $r->d,
                'total' => (int) $r->total,
                'correct' => (int) $r->corrects,
                'incorrect' => (int) $incorrect,
                'offline' => (int) $r->offline_cnt,
                'percent_correct' => $pct,
                'avg_minutes' => $r->avg_minutes ? round($r->avg_minutes,1) : 0,
            ];
        })->toArray();
    }

    public function exportCsv(): StreamedResponse
    {
        $metrics = $this->computeMetrics();
        $series = $metrics['series'];
        $filename = 'hotspot_dashboard_'.now()->format('Ymd_His').'.csv';
        return response()->streamDownload(function() use ($series){
            $out = fopen('php://output','w');
            fputcsv($out, ['Fecha','Intentos','Correctos','Incorrectos','Offline','% Acierto','Min Prom']);
            foreach($series as $row){
                fputcsv($out, [
                    $row['date'],
                    $row['total'],
                    $row['correct'],
                    $row['incorrect'],
                    $row['offline'],
                    $row['percent_correct'],
                    $row['avg_minutes'],
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function setRange(string $range): void
    {
        $this->range = in_array($range, ['today','7d','30d']) ? $range : 'today';
    }

    public function render()
    {
        return view('livewire.admin.overview-dashboard', [
            'metrics' => $this->computeMetrics(),
        ]);
    }
}
