<div class="p-6 space-y-6 bg-gradient-to-br from-white via-sky-50 to-indigo-50 dark:from-zinc-900 dark:via-zinc-900 dark:to-indigo-950" x-data="{ showPass:false }">
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold tracking-tight">Routers Mikrotik</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400">Administra endpoints API para emitir credenciales hotspot.</p>
        </div>
        <div class="flex gap-2 flex-wrap items-center">
            <button wire:click="new" type="button" class="px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-medium shadow hover:bg-blue-500">Nuevo</button>
            <span wire:loading.delay class="text-[11px] text-indigo-600 animate-pulse">Procesando...</span>
            @if($testMessage)
                <div class="text-[11px] px-2 py-1 rounded bg-gray-100 dark:bg-zinc-700 border border-gray-200 dark:border-zinc-600 shadow-inner">{{$testMessage}}</div>
            @endif
        </div>
    </div>

    <div class="grid xl:grid-cols-3 gap-6">
        <!-- Tabla Routers -->
        <div class="xl:col-span-2 space-y-4">
            <div class="overflow-hidden rounded-xl border border-gray-200/80 dark:border-zinc-700 bg-white/70 dark:bg-zinc-800/80 shadow-sm backdrop-blur">
                <div class="px-4 py-2 border-b border-gray-200 dark:border-zinc-700 flex items-center justify-between text-xs">
                    <div class="font-semibold text-gray-600 dark:text-gray-300">Listado ({{ count($routers) }})</div>
                    <div class="flex gap-2 items-center text-[11px]">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500"></span>Activo</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span>Inactivo</span>
                    </div>
                </div>
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-zinc-700/60 text-left text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-300">
                            <tr>
                                <th class="p-2 font-medium">Nombre</th>
                                <th class="p-2 font-medium">Endpoint</th>
                                <th class="p-2 font-medium">Conn</th>
                                <th class="p-2 font-medium">Cred</th>
                                <th class="p-2 font-medium">Notas</th>
                                <th class="p-2 font-medium">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-zinc-700">
                            @forelse($routers as $r)
                                @php $pwdLen = strlen($r->password); @endphp
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-zinc-700/40 transition">
                                    <td class="p-2 align-top">
                                        <div class="flex flex-col gap-0.5">
                                            <span class="font-medium {{ $r->is_default ? 'text-indigo-600 dark:text-indigo-400' : '' }}">{{$r->name}}</span>
                                            <div class="flex flex-wrap gap-1">
                                                @if($r->is_default)<span class="px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 text-[10px]">Default</span>@endif
                                                @if($r->enabled)<span class="px-1.5 py-0.5 rounded bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-300 text-[10px]">Activo</span>@else<span class="px-1.5 py-0.5 rounded bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300 text-[10px]">Inactivo</span>@endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-2 align-top font-mono text-[11px]">
                                        {{$r->host}}:{{$r->port}}
                                        <div class="mt-0.5 flex gap-1 flex-wrap">
                                            <span class="px-1 py-0.5 rounded bg-gray-100 dark:bg-zinc-600 text-[10px] text-gray-600 dark:text-gray-200">{{$r->ssl ? 'SSL' : 'Plain'}}</span>
                                            <span class="px-1 py-0.5 rounded bg-gray-100 dark:bg-zinc-600 text-[10px] text-gray-600 dark:text-gray-200">{{$r->timeout}}s</span>
                                        </div>
                                    </td>
                                    <td class="p-2 align-top text-[11px]">
                                        <span class="block">Usuario:</span>
                                        <span class="font-mono text-gray-700 dark:text-gray-200">{{$r->username}}</span>
                                    </td>
                                    <td class="p-2 align-top text-[11px]">
                                        <span class="block">Pwd: ••• ({{$pwdLen}})</span>
                                    </td>
                                    <td class="p-2 align-top text-[11px] max-w-[140px] truncate" title="{{$r->notes}}">{{$r->notes}}</td>
                                    <td class="p-2 align-top text-[11px]">
                                        <div class="flex flex-col gap-1">
                                            <button wire:click="edit({{$r->id}})" class="px-2 py-0.5 rounded bg-white dark:bg-zinc-700 border border-gray-200 dark:border-zinc-600 hover:border-indigo-400 text-xs">Editar</button>
                                            <a href="{{ route('admin.routers.template',$r->id) }}" class="px-2 py-0.5 rounded bg-amber-500 text-white hover:bg-amber-400 text-xs" title="Descargar login.html">Template</a>
                                            <button wire:click="test({{$r->id}})" class="px-2 py-0.5 rounded bg-indigo-600 text-white hover:bg-indigo-500 text-xs relative" wire:loading.attr="disabled" wire:target="test({{$r->id}})">
                                                <span wire:loading.remove wire:target="test({{$r->id}})">Test</span>
                                                <span wire:loading wire:target="test({{$r->id}})" class="animate-pulse">...</span>
                                            </button>
                                            <button wire:click="delete({{$r->id}})" onclick="return confirm('¿Eliminar router?')" class="px-2 py-0.5 rounded bg-red-600/90 text-white hover:bg-red-600 text-xs">Eliminar</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-6 text-center text-gray-500 text-sm">Sin routers configurados</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Mobile cards -->
                <div class="md:hidden divide-y divide-gray-100 dark:divide-zinc-700">
                    @forelse($routers as $r)
                        @php $pwdLen = strlen($r->password); @endphp
                        <div class="p-4 flex flex-col gap-2 bg-white/60 dark:bg-zinc-800/70">
                            <div class="flex items-start justify-between gap-2">
                                <div class="space-y-1">
                                    <div class="font-medium text-sm {{ $r->is_default ? 'text-indigo-600 dark:text-indigo-400' : '' }}">{{$r->name}}</div>
                                    <div class="flex flex-wrap gap-1">
                                        @if($r->is_default)<span class="px-1.5 py-0.5 rounded bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 text-[10px]">Default</span>@endif
                                        @if($r->enabled)<span class="px-1.5 py-0.5 rounded bg-green-100 dark:bg-green-500/20 text-green-700 dark:text-green-300 text-[10px]">Activo</span>@else<span class="px-1.5 py-0.5 rounded bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-300 text-[10px]">Off</span>@endif
                                    </div>
                                </div>
                                <div class="text-[10px] font-mono text-gray-600 dark:text-gray-300 text-right leading-tight">
                                    {{$r->host}}:{{$r->port}}<br>
                                    <span>{{$r->ssl ? 'SSL' : 'Plain'}} · {{$r->timeout}}s</span>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-[11px]">
                                <div class="text-gray-500 dark:text-gray-400">Usuario</div>
                                <div class="font-mono break-all">{{$r->username}}</div>
                                <div class="text-gray-500 dark:text-gray-400">Pwd</div>
                                <div>••• ({{$pwdLen}})</div>
                                @if($r->notes)
                                    <div class="text-gray-500 dark:text-gray-400 col-span-2">{{$r->notes}}</div>
                                @endif
                            </div>
                            <div class="flex gap-2 flex-wrap pt-1">
                                <button wire:click="edit({{$r->id}})" class="px-2 py-1 rounded bg-white dark:bg-zinc-700 border border-gray-200 dark:border-zinc-600 text-xs">Editar</button>
                                <a href="{{ route('admin.routers.template',$r->id) }}" class="px-2 py-1 rounded bg-amber-500 text-white text-xs">Template</a>
                                <button wire:click="test({{$r->id}})" class="px-2 py-1 rounded bg-indigo-600 text-white text-xs hover:bg-indigo-500" wire:loading.attr="disabled" wire:target="test({{$r->id}})">Test</button>
                                <button wire:click="delete({{$r->id}})" onclick="return confirm('¿Eliminar router?')" class="px-2 py-1 rounded bg-red-600/90 text-white text-xs">Eliminar</button>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-500 text-sm">Sin routers configurados</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Formulario -->
        <div class="space-y-4">
            <div class="rounded-xl border border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 dark:border-zinc-700 flex items-center justify-between">
                    <h2 class="text-sm font-semibold">@if($editingId) Editar Router #{{$editingId}} @else Nuevo Router @endif</h2>
                    @if($editingId)
                        <button type="button" wire:click="new" class="text-[11px] underline">Limpiar</button>
                    @endif
                </div>
                <form wire:submit.prevent="save" class="p-4 space-y-4">
                    <div class="grid gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wide">Nombre</label>
                            <input type="text" wire:model.defer="name" class="w-full border rounded px-2 py-1 text-sm bg-white/60 dark:bg-zinc-700/50" />
                            @error('name')<div class="text-red-600 text-[11px] mt-0.5">{{$message}}</div>@enderror
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wide">Host / IP</label>
                            <input type="text" wire:model.defer="host" class="w-full border rounded px-2 py-1 text-sm font-mono bg-white/60 dark:bg-zinc-700/50" />
                            @error('host')<div class="text-red-600 text-[11px] mt-0.5">{{$message}}</div>@enderror
                        </div>
                        <div class="flex gap-3">
                            <div class="flex-1">
                                <label class="block text-[11px] font-semibold uppercase tracking-wide">Puerto</label>
                                <input type="number" wire:model.defer="port" class="w-full border rounded px-2 py-1 text-sm bg-white/60 dark:bg-zinc-700/50" />
                                @error('port')<div class="text-red-600 text-[11px] mt-0.5">{{$message}}</div>@enderror
                            </div>
                            <div class="flex-1">
                                <label class="block text-[11px] font-semibold uppercase tracking-wide">Usuario</label>
                                <input type="text" wire:model.defer="username" class="w-full border rounded px-2 py-1 text-sm bg-white/60 dark:bg-zinc-700/50" />
                                @error('username')<div class="text-red-600 text-[11px] mt-0.5">{{$message}}</div>@enderror
                            </div>
                        </div>
            <div x-data="{show:false}">
                            <label class="block text-[11px] font-semibold uppercase tracking-wide">Password</label>
                            <div class="relative">
                <input :type="show?'text':'password'" wire:model.defer="password" class="w-full border rounded px-2 py-1 text-sm font-mono pr-14 bg-white/60 dark:bg-zinc-700/50" />
                <button type="button" @click="show=!show" class="absolute inset-y-0 right-0 px-2 text-[10px] text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" x-text="show ? 'Ocultar' : 'Ver'"></button>
                            </div>
                            @error('password')<div class="text-red-600 text-[11px] mt-0.5">{{$message}}</div>@enderror
                            @if($editingId)
                                <div class="text-[10px] text-gray-500 mt-1">Deja en blanco para conservar la contraseña actual.</div>
                            @endif
                        </div>
                        <div class="flex gap-3 items-end">
                            <div class="flex-1">
                                <label class="block text-[11px] font-semibold uppercase tracking-wide">Timeout (s)</label>
                                <input type="number" wire:model.defer="timeout" min="1" max="60" class="w-full border rounded px-2 py-1 text-sm bg-white/60 dark:bg-zinc-700/50" />
                                @error('timeout')<div class="text-red-600 text-[11px] mt-0.5">{{$message}}</div>@enderror
                            </div>
                            <div class="flex items-center gap-2 mt-5">
                                <label class="flex items-center gap-1 text-xs"><input type="checkbox" wire:model="ssl" class="rounded" /> SSL</label>
                                <label class="flex items-center gap-1 text-xs"><input type="checkbox" wire:model="enabled" class="rounded" /> Activo</label>
                                <label class="flex items-center gap-1 text-xs"><input type="checkbox" wire:model="is_default" class="rounded" /> Default</label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold uppercase tracking-wide">Notas</label>
                            <textarea wire:model.defer="notes" rows="2" class="w-full border rounded px-2 py-1 text-xs bg-white/60 dark:bg-zinc-700/50"></textarea>
                            @error('notes')<div class="text-red-600 text-[11px] mt-0.5">{{$message}}</div>@enderror
                        </div>
                    </div>
                    <div class="flex gap-2 flex-wrap pt-2">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-white text-xs font-semibold shadow hover:bg-green-500 disabled:opacity-50" wire:loading.attr="disabled">Guardar</button>
                        @if($editingId)
                            <button type="button" wire:click="new" class="px-4 py-2 rounded-lg border text-xs font-semibold hover:bg-gray-50 dark:hover:bg-zinc-700">Nuevo</button>
                        @endif
                        @if($editingId)
                            <button type="button" wire:click="test({{$editingId}})" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-500" wire:loading.attr="disabled">Test</button>
                        @endif
                    </div>
                </form>
            </div>
            <div class="text-[11px] text-gray-500 leading-relaxed space-y-1">
                <p><strong>Consejos:</strong> Habilita el servicio API en Mikrotik (/ip service print). Si usas SSL usa el puerto api-ssl (p.ej. 8729).</p>
                <p>Credenciales se almacenan cifradas. Al editar, contraseña vacía = conserva la actual.</p>
            </div>
        </div>
    </div>
</div>
