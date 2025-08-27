<div class="space-y-8" x-data>
    <div class="flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-bold tracking-tight">Dashboard Hotspot</h1>
        <div class="flex items-center gap-1 text-xs bg-gray-100 dark:bg-zinc-800 rounded-full p-0.5">
            @foreach(['today'=>'Hoy','7d'=>'7d','30d'=>'30d'] as $key=>$label)
                <button wire:click="setRange('{{$key}}')" class="px-3 py-1 rounded-full font-medium transition text-gray-600 dark:text-gray-300 {{ $range===$key ? 'bg-white dark:bg-zinc-700 shadow text-indigo-600 dark:text-indigo-300' : 'hover:bg-white/60 dark:hover:bg-zinc-700/60' }}">{{$label}}</button>
            @endforeach
        </div>
        <span class="text-xs text-gray-500">Período: {{$metrics['period_label']}}</span>
        <div class="ml-auto flex items-center gap-2">
            <button wire:click="exportCsv" wire:loading.attr="disabled" class="text-xs inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-indigo-600 text-white font-medium shadow hover:bg-indigo-500 disabled:opacity-50">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/></svg>
                <span>CSV</span>
            </button>
            <span wire:loading wire:target="exportCsv" class="text-[11px] text-gray-500">Generando...</span>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div class="p-4 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 shadow-sm">
            <div class="text-xs font-semibold text-gray-500 uppercase">Intentos</div>
            <div class="mt-1 text-2xl font-bold">{{$metrics['total']}}</div>
            <div class="mt-1 text-[11px] text-gray-500">Correctos {{$metrics['percent_correct']}}%</div>
        </div>
        <div class="p-4 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 shadow-sm">
            <div class="text-xs font-semibold text-gray-500 uppercase">Tiempo Prom.</div>
            <div class="mt-1 text-2xl font-bold">{{$metrics['avg_minutes']}} <span class="text-base font-medium">min</span></div>
            <div class="mt-1 text-[11px] text-gray-500">Intentos/éxito: {{ $metrics['avg_attempts_per_success'] ?? '—' }}</div>
        </div>
        <div class="p-4 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 shadow-sm">
            <div class="text-xs font-semibold text-gray-500 uppercase">Offline</div>
            <div class="mt-1 text-2xl font-bold text-red-600">{{$metrics['offline']}}</div>
            <div class="mt-1 text-[11px] text-gray-500">Incidencias de router</div>
        </div>
        <div class="p-4 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 shadow-sm">
            <div class="text-xs font-semibold text-gray-500 uppercase">Trivias activas</div>
            <div class="mt-1 text-2xl font-bold">{{$metrics['active_trivias']}}</div>
            <div class="mt-1 text-[11px] text-gray-500">Hoy: {{$metrics['trivias_today']}}</div>
        </div>
        <div class="p-4 rounded-xl bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 shadow-sm col-span-full xl:col-span-1 xl:row-span-1">
            <div class="text-xs font-semibold text-gray-500 uppercase mb-1">Tendencia</div>
            <div x-data="{ series: @js($metrics['series']),
                    get max(){ return this.series.length ? Math.max(...this.series.map(s=>s.total)) : 0},
                    get points(){ if(!this.series.length) return ''; const w=220, h=50, max=this.max ||1; return this.series.map((s,i)=>{ const x = (w/(this.series.length-1))*i; const y = h - (s.total/max)*h; return `${x},${y}` }).join(' '); },
                    get correctLine(){ if(!this.series.length) return ''; const w=220,h=50,max=this.max||1; return this.series.map((s,i)=>{ const x=(w/(this.series.length-1))*i; const y=h - (s.correct/max)*h; return `${x},${y}` }).join(' ');} }" class="space-y-2">
                <template x-if="series.length">
                    <div class="flex flex-col gap-1">
                        <svg :width="220" :height="50" class="overflow-visible">
                            <polyline :points="points" fill="none" stroke="url(#gTotal)" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />
                            <polyline :points="correctLine" fill="none" stroke="#10B981" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" stroke-dasharray="4 3" />
                            <defs>
                                <linearGradient id="gTotal" x1="0" x2="1" y1="0" y2="0">
                                    <stop offset="0%" stop-color="#6366F1" />
                                    <stop offset="100%" stop-color="#0EA5E9" />
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="flex justify-between text-[10px] text-gray-500" x-text="series.map(s=>s.date.slice(5)).join(' · ')"></div>
                    </div>
                </template>
                <template x-if="!series.length">
                    <p class="text-[11px] text-gray-500">Sin datos</p>
                </template>
                <div class="text-[11px] text-gray-500" x-show="series.length">Total (línea sólida) / Correctos (línea punteada)</div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border bg-white dark:bg-zinc-800 border-gray-200 dark:border-zinc-700 p-4 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-3">Top Trivias (aciertos)</h2>
                <div class="space-y-3">
                    @forelse($metrics['top_trivias'] as $t)
                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">#{{$t['id']}} {{$t['question']}}</p>
                                <div class="h-2 rounded bg-gray-100 dark:bg-zinc-700 mt-1 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-emerald-500 to-sky-500" style="width: {{$t['pct']}}%"></div>
                                </div>
                            </div>
                            <div class="text-xs font-semibold text-gray-500 w-12 text-right">{{$t['pct']}}%</div>
                            <div class="text-[10px] text-gray-400 w-10 text-right">{{$t['total']}} it.</div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-500">Aún no hay suficientes datos.</p>
                    @endforelse
                </div>
            </div>
            <div class="rounded-xl border bg-white dark:bg-zinc-800 border-gray-200 dark:border-zinc-700 p-4 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-3">Últimos intentos</h2>
                <ul class="divide-y divide-gray-200 dark:divide-zinc-700">
                    @foreach($metrics['recent_attempts'] as $a)
                        <li class="py-2 flex items-center gap-3 text-sm">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-[11px] font-bold {{ $a->is_correct ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' }}">{{ $a->is_correct ? '✓':'×' }}</span>
                            <span class="font-mono text-xs text-gray-500">{{$a->created_at->format('H:i:s')}}</span>
                            <span class="flex-1 truncate font-mono text-[11px]">{{$a->mikrotik_username}}</span>
                            <span class="text-xs {{ $a->offline ? 'text-red-600' : 'text-gray-500' }}">{{$a->offline ? 'offline':'ok'}}</span>
                            <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-300">{{$a->granted_minutes}}m</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="space-y-6">
            <div class="rounded-xl border bg-white dark:bg-zinc-800 border-gray-200 dark:border-zinc-700 p-4 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300 mb-3">Routers</h2>
                <p class="text-sm"><span class="font-semibold">{{$metrics['routers_enabled']}}</span> habilitados / {{$metrics['routers_total']}} total.</p>
                <p class="text-sm mt-1">Default: <span class="font-medium text-indigo-600 dark:text-indigo-300">{{$metrics['default_router'] ?? '—'}}</span></p>
                <div class="mt-4 h-2 bg-gray-100 dark:bg-zinc-700 rounded overflow-hidden">
                    @php $pctRouters = $metrics['routers_total'] ? round($metrics['routers_enabled']*100/$metrics['routers_total']) : 0; @endphp
                    <div class="h-full bg-gradient-to-r from-indigo-500 to-sky-500" style="width: {{$pctRouters}}%"></div>
                </div>
                <p class="mt-1 text-[11px] text-gray-500">{{$pctRouters}}% activos</p>
            </div>
            <div class="rounded-xl border bg-gradient-to-br from-indigo-600/90 to-sky-600/90 text-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold uppercase tracking-wide mb-2">Resumen</h2>
                <ul class="text-[13px] leading-relaxed space-y-1">
                    <li>Aciertos: <strong>{{$metrics['percent_correct']}}%</strong></li>
                    <li>Offline: <strong>{{$metrics['offline']}}</strong></li>
                    <li>Prom. minutos: <strong>{{$metrics['avg_minutes']}}</strong></li>
                    <li>Trivias activas: <strong>{{$metrics['active_trivias']}}</strong></li>
                </ul>
                <p class="text-[11px] mt-3 text-indigo-100/70">Este panel consolida datos clave de intentos, trivias y routers.</p>
            </div>
        </div>
    </div>
</div>
