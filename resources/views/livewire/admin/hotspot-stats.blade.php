<div class="p-6 space-y-6">
    <h1 class="text-2xl font-semibold">Hotspot - Estadísticas</h1>
    <div class="grid md:grid-cols-5 gap-4">
        <div class="p-4 bg-white dark:bg-zinc-800 rounded shadow">
            <div class="text-sm text-gray-500">Intentos</div>
            <div class="text-xl font-bold">{{$stats['total']}}</div>
        </div>
        <div class="p-4 bg-white dark:bg-zinc-800 rounded shadow">
            <div class="text-sm text-gray-500">Correctos</div>
            <div class="text-xl font-bold text-green-600">{{$stats['correct']}}</div>
        </div>
        <div class="p-4 bg-white dark:bg-zinc-800 rounded shadow">
            <div class="text-sm text-gray-500">Incorrectos</div>
            <div class="text-xl font-bold text-red-600">{{$stats['incorrect']}}</div>
        </div>
        <div class="p-4 bg-white dark:bg-zinc-800 rounded shadow">
            <div class="text-sm text-gray-500">% Acierto</div>
            <div class="text-xl font-bold">{{$stats['percent_correct']}}%</div>
        </div>
        <div class="p-4 bg-white dark:bg-zinc-800 rounded shadow">
            <div class="text-sm text-gray-500">Min Prom.</div>
            <div class="text-xl font-bold">{{$stats['avg_minutes']}}</div>
        </div>
    </div>

    <div class="flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-xs font-semibold">Fecha</label>
            <input type="date" wire:model.live="date" class="border rounded px-2 py-1" />
        </div>
        <div>
            <label class="block text-xs font-semibold">Trivia</label>
            <select wire:model.live="triviaId" class="border rounded px-2 py-1">
                <option value="">Todas</option>
                @foreach($trivias as $t)
                    <option value="{{$t->id}}">#{{$t->id}} - {{Str::limit($t->question,40)}}</option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-semibold">Buscar (MAC/IP/User)</label>
            <input type="text" wire:model.live="search" placeholder="Ej: aa:bb o 192.168" class="w-full border rounded px-2 py-1" />
        </div>
        <div>
            <label class="block text-xs font-semibold">Por página</label>
            <select wire:model.live="perPage" class="border rounded px-2 py-1">
                <option>25</option>
                <option>50</option>
                <option>100</option>
            </select>
        </div>
    </div>

    <div class="overflow-x-auto bg-white dark:bg-zinc-800 rounded shadow">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 dark:bg-zinc-700 text-left">
                <tr>
                    <th class="p-2">ID</th>
                    <th class="p-2">Fecha/Hora</th>
                    <th class="p-2">Trivia</th>
                    <th class="p-2">MAC</th>
                    <th class="p-2">IP</th>
                    <th class="p-2">Usuario</th>
                    <th class="p-2">Resp.</th>
                    <th class="p-2">Correcto</th>
                    <th class="p-2">Min</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attempts as $a)
                    <tr class="border-b border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-700/50">
                        <td class="p-2">{{$a->id}}</td>
                        <td class="p-2">{{$a->created_at->format('H:i:s')}}</td>
                        <td class="p-2">{{$a->trivia_id}}</td>
                        <td class="p-2 font-mono">{{$a->mac}}</td>
                        <td class="p-2 font-mono">{{$a->ip}}</td>
                        <td class="p-2 font-mono">{{$a->mikrotik_username}}</td>
                        <td class="p-2">{{$a->selected_option}}</td>
                        <td class="p-2">@if($a->is_correct)<span class="text-green-600">Sí</span>@else<span class="text-red-600">No</span>@endif</td>
                        <td class="p-2">{{$a->granted_minutes}}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="p-4 text-center text-gray-500">Sin registros</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-2">{{$attempts->links()}}</div>
    </div>
</div>
