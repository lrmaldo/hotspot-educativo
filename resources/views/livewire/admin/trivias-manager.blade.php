<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Administrar Trivias</h1>
        <div class="flex gap-2 flex-wrap justify-end">
            <input type="text" wire:model.live="search" placeholder="Buscar pregunta..." class="border rounded px-2 py-1" />
            <button wire:click="exportCsv" type="button" class="bg-emerald-600 text-white px-3 py-1 rounded">Exportar CSV</button>
            <button type="button" wire:click="openImport" class="bg-orange-600 text-white px-3 py-1 rounded">Importar CSV</button>
            <button wire:click="create" class="bg-blue-600 text-white px-3 py-1 rounded">Nueva</button>
        </div>
    </div>

    @if (session('status'))
        <div class="p-2 bg-green-100 text-green-800 rounded text-sm">{{ session('status') }}</div>
    @endif

    <div class="overflow-x-auto bg-white dark:bg-zinc-800 rounded shadow">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-100 dark:bg-zinc-700">
                <tr>
                    <th class="p-2">ID</th>
                    <th class="p-2">Pregunta</th>
                    <th class="p-2">Activa</th>
                    <th class="p-2">Fecha</th>
                    <th class="p-2">Correcta</th>
                    <th class="p-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @foreach($trivias as $t)
                <tr class="border-b border-gray-200 dark:border-zinc-700">
                    <td class="p-2">{{$t->id}}</td>
                    <td class="p-2 max-w-md truncate" title="{{$t->question}}">{{$t->question}}</td>
                    <td class="p-2">@if($t->active)<span class="text-green-600">Sí</span>@else<span class="text-gray-500">No</span>@endif</td>
                    <td class="p-2">{{$t->valid_on? $t->valid_on->toDateString(): '-'}}</td>
                    <td class="p-2">{{$t->correct_option}}</td>
                    <td class="p-2 flex gap-2 flex-wrap">
                        <button wire:click="edit({{$t->id}})" class="text-blue-600 text-xs">Editar</button>
                        <button wire:click="duplicate({{$t->id}})" class="text-indigo-600 text-xs">Duplicar</button>
                        <button wire:click="confirmDelete({{$t->id}})" class="text-red-600 text-xs">Eliminar</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="p-2">{{$trivias->links()}}</div>
    </div>

    @if($showForm)
        <div
            x-data="{show:true}"
            x-init="requestAnimationFrame(()=>{$el.classList.add('opened')})"
            x-on:keydown.escape.window="@this.call('cancel')"
            class="modal-overlay fixed inset-0 flex items-center justify-center z-50 p-2 md:p-6"
        >
            <div class="modal-panel bg-white dark:bg-zinc-800 w-full max-w-3xl rounded-lg md:rounded-xl shadow-lg relative flex flex-col max-h-full sm:animate-scale-in animate-slide-up">
                <button class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" wire:click="cancel" aria-label="Cerrar">✕</button>
                <div class="px-5 pt-6 pb-2 border-b border-gray-200 dark:border-zinc-700">
                    <h2 class="text-xl font-semibold leading-tight">{{$editingId? 'Editar Trivia':'Nueva Trivia'}}</h2>
                </div>
                <div class="px-5 pb-5 overflow-y-auto custom-scrollbar">
                <form wire:submit.prevent="save" class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold">Pregunta</label>
                        <textarea wire:model.defer="question" class="w-full border rounded px-2 py-1" rows="2"></textarea>
                        @error('question')<div class="text-red-600 text-xs">{{$message}}</div>@enderror
                    </div>
                    @foreach(['A','B','C','D'] as $opt)
                        <div>
                            <label class="block text-xs font-semibold">Opción {{$opt}}</label>
                            <input type="text" wire:model.defer="option_{{strtolower($opt)}}" class="w-full border rounded px-2 py-1" />
                            @error('option_'.strtolower($opt))<div class="text-red-600 text-xs">{{$message}}</div>@enderror
                        </div>
                    @endforeach
                    <div>
                        <label class="block text-xs font-semibold">Correcta</label>
                        <select wire:model.defer="correct_option" class="w-full border rounded px-2 py-1">
                            <option>A</option><option>B</option><option>C</option><option>D</option>
                        </select>
                        @error('correct_option')<div class="text-red-600 text-xs">{{$message}}</div>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold">Fecha (valid_on)</label>
                        <input type="date" wire:model.defer="valid_on" class="w-full border rounded px-2 py-1" />
                        @error('valid_on')<div class="text-red-600 text-xs">{{$message}}</div>@enderror
                    </div>
                    <div class="flex items-center gap-2 mt-4">
                        <input type="checkbox" wire:model.defer="active" id="active_chk" />
                        <label for="active_chk" class="text-sm">Activa</label>
                    </div>
                    <div class="md:col-span-2 flex flex-wrap gap-2 mt-2">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded w-full sm:w-auto">Guardar</button>
                        <button type="button" wire:click="togglePreview" class="px-4 py-2 rounded border w-full sm:w-auto">{{$preview? 'Ocultar Preview':'Vista Previa'}}</button>
                        <button type="button" wire:click="cancel" class="px-4 py-2 rounded border w-full sm:w-auto">Cancelar</button>
                    </div>
                </form>
                @if($preview)
                    <div class="mt-6 border-t pt-4 md:col-span-2">
                        <h3 class="font-semibold mb-2">Preview (Alumno)</h3>
                        <div class="p-4 rounded border bg-gray-50 dark:bg-zinc-700 space-y-3 text-sm">
                            <p class="font-medium">{{$this->previewTrivia['question']}}</p>
                            <ul class="space-y-1">
                                @foreach($this->previewTrivia['options'] as $key=>$text)
                                    <li class="flex items-start gap-2">
                                        <span class="font-bold">{{$key}})</span>
                                        <span class="flex-1">{{$text}}</span>
                                        @if($key === $this->previewTrivia['correct'])<span class="text-green-600 font-semibold">(Correcta)</span>@endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                </div>
            </div>
            </div>
        </div>
    @endif

    @if($showImport)
        <div
            x-data="{}"
            x-init="requestAnimationFrame(()=>{$el.classList.add('opened')})"
            class="modal-overlay fixed inset-0 flex items-center justify-center z-50 p-2 md:p-6"
            wire:key="import-modal"
            wire:ignore.self>
            <div class="modal-panel bg-white dark:bg-zinc-800 w-full max-w-2xl rounded-lg md:rounded-xl shadow-lg p-6 space-y-4 relative sm:animate-scale-in animate-slide-up">
                <button class="absolute top-2 right-2 text-gray-500" wire:click="closeImport">✕</button>
                <h2 class="text-xl font-semibold">Importar CSV de Trivias</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Sube un archivo CSV UTF-8 con encabezados: <code>question,option_a,option_b,option_c,option_d,correct_option,active,valid_on</code><br>
                    Campos:
                    <ul class="list-disc ms-5">
                        <li><strong>correct_option</strong>: A|B|C|D</li>
                        <li><strong>active</strong>: 1/0, true/false, sí/no (opcional, default 1)</li>
                        <li><strong>valid_on</strong>: YYYY-MM-DD (opcional)</li>
                    </ul>
                    Filas con pregunta existente se actualizan; otras se crean. Líneas que comienzan con <code>#</code> o vacías se ignoran.
                </p>
                <button wire:click="downloadTemplate" type="button" class="text-blue-600 underline text-sm">Descargar template CSV</button>
                <div class="space-y-4 relative">
                    @if(!$importing && !$importSummary)
                        <div class="space-y-3">
                            <form wire:submit.prevent="prepareImport" class="space-y-3">
                                <div>
                                    <input type="file" wire:model="importFile" id="csv_import_file" accept=".csv,text/csv" class="border rounded px-2 py-1 w-full" />
                                </div>
                                @error('importFile')<div class="text-red-600 text-xs">{{$message}}</div>@enderror
                                <div wire:loading wire:target="importFile" class="text-xs text-gray-500">Subiendo archivo...</div>
                                @if($importFile)
                                    <div class="text-xs text-green-600">Archivo seleccionado y listo para procesar</div>
                                @endif
                                <div class="flex gap-2 flex-wrap">
                                    <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded disabled:opacity-50" @disabled(!$importFile)>Validar / Iniciar</button>
                                    <button type="button" wire:click="closeImport" class="px-4 py-2 rounded border">Cerrar</button>
                                </div>
                            </form>
                        </div>
                    @endif

                    @if($importing)
                        <div class="space-y-3" @if($autoImport) wire:poll.400ms="processChunk" @endif>
                            <div class="flex items-center gap-3 flex-wrap">
                                <div class="text-sm font-medium">Procesando {{$processedLines}} / {{$totalLines}} filas</div>
                                <div class="text-xs px-2 py-0.5 rounded bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300">
                                    {{ $totalLines>0 ? round(($processedLines/$totalLines)*100,1) : 0 }}%
                                </div>
                                <div class="flex gap-2 text-xs">
                                    @if($autoImport)
                                        <button type="button" wire:click="pauseAuto" class="underline">Pausar</button>
                                    @else
                                        <button type="button" wire:click="resumeAuto" class="underline">Reanudar</button>
                                    @endif
                                </div>
                            </div>
                            <div class="w-full h-3 bg-gray-200 dark:bg-zinc-700 rounded overflow-hidden">
                                <div class="h-full bg-orange-500 transition-all" style="width: {{ $totalLines>0 ? round(($processedLines/$totalLines)*100,2) : 0 }}%"></div>
                            </div>
                            <div class="grid gap-2 sm:flex sm:flex-wrap">
                                <button type="button" wire:click="processChunk" class="bg-orange-600 text-white px-4 py-2 rounded">Procesar siguiente</button>
                                <button type="button" wire:click="resetImportState" class="px-4 py-2 rounded border">Cancelar</button>
                            </div>
                            <p class="text-xs text-gray-500">Bloques de {{$chunkSize}} filas. Puedes pausar o continuar manualmente.</p>
                        </div>
                    @endif

                    @if($importSummary && !$importing)
                        <div class="space-y-3">
                            <div class="p-2 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 rounded text-xs">{{$importSummary}}</div>
                            <div class="flex gap-2 flex-wrap">
                                <button type="button" wire:click="closeImport" class="bg-blue-600 text-white px-4 py-2 rounded">Cerrar</button>
                                <button type="button" wire:click="prepareNewImport" class="px-4 py-2 rounded border">Nueva importación</button>
                            </div>
                        </div>
                    @endif
                </div>
                @if($importSummary)
                    <div class="p-2 bg-emerald-50 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 rounded text-xs">{{$importSummary}}</div>
                @endif
            </div>
        </div>
    @endif

    @once
        <style>
            .modal-overlay {background:rgba(0,0,0,.40);opacity:0;transition:opacity .25s ease;}
            .modal-overlay.opened{opacity:1;}
            .modal-panel{transform:translateY(12px);opacity:0;transition:transform .35s cubic-bezier(.16,.84,.44,1),opacity .35s ease;}
            .modal-overlay.opened .modal-panel{transform:translateY(0);opacity:1;}
            @media (max-width: 640px){
                .modal-panel{border-radius:0!important;max-width:none;height:100%;max-height:none;}
            }
        </style>
    @endonce

    @if($confirmDelete)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-zinc-800 w-full max-w-md rounded shadow p-6 space-y-4">
                <h2 class="text-lg font-semibold">Confirmar eliminación</h2>
                <p class="text-sm">Esta acción eliminará la trivia seleccionada.</p>
                <div class="flex gap-2 justify-end">
                    <button wire:click="$set('confirmDelete', false)" class="px-3 py-1 rounded border">Cancelar</button>
                    <button wire:click="delete" class="px-3 py-1 rounded bg-red-600 text-white">Eliminar</button>
                </div>
            </div>
        </div>
    @endif
</div>
