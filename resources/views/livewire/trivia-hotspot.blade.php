<div class="min-h-screen flex flex-col bg-gradient-to-br from-sky-50 via-white to-violet-50 dark:from-zinc-900 dark:via-zinc-900 dark:to-indigo-950" x-data>
    <style>
        @keyframes fadeInScale {0%{opacity:0;transform:scale(.95)}100%{opacity:1;transform:scale(1)}}
        @keyframes slideUp {0%{opacity:0;transform:translateY(14px)}100%{opacity:1;transform:translateY(0)}}
        .anim-fade-scale{animation:fadeInScale .45s cubic-bezier(.16,.8,.3,1)}
        .anim-slide-up{animation:slideUp .55s cubic-bezier(.16,.8,.3,1)}
        @keyframes confetti-fall {0%{transform:translateY(-10vh) rotateZ(0)}100%{transform:translateY(110vh) rotateZ(720deg)}}
        .confetti-piece{position:fixed;top:0;left:0;width:10px;height:14px;opacity:.9;will-change:transform;animation:confetti-fall linear forwards;pointer-events:none;border-radius:2px}
    </style>
    <header class="px-4 pt-6 pb-4 w-full max-w-7xl lg:px-8 mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-sky-600 to-indigo-600 dark:from-sky-300 dark:to-indigo-300">Hotspot Educativo</h1>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex flex-wrap gap-2 items-center">
                    <span>Conecta aprendiendo: responde y navega.</span>
                    @if(!$preview)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 dark:bg-zinc-700 text-[10px] font-medium text-gray-600 dark:text-gray-300">
                            Router:
                            @if($routerDevice)
                                <span class="text-indigo-600 dark:text-indigo-300">{{$routerDevice->name}} ({{$routerDevice->host}}:{{$routerDevice->port}})</span>
                            @else
                                <span class="text-red-600 dark:text-red-400">no detectado</span>
                            @endif
                        </span>
                    @endif
                </p>
            </div>
            @if($preview)
                <div class="text-xs px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l2 2m6-2a8 8 0 11-16 0 8 8 0 0116 0z"/></svg>
                    Preview
                </div>
            @endif
        </div>
    </header>
    <main class="flex-1 w-full max-w-7xl mx-auto px-4 lg:px-8 pb-12 flex flex-col">
        <div class="flex flex-col lg:flex-row gap-8 xl:gap-12 items-stretch">
            <div class="flex-1">
                <div class="relative overflow-hidden rounded-2xl shadow-sm border bg-white/70 dark:bg-zinc-800/70 dark:border-zinc-700 backdrop-blur-sm p-6 md:p-10 space-y-6">
                    <div class="absolute inset-0 pointer-events-none opacity-30 bg-[radial-gradient(circle_at_40%_20%,theme(colors.sky.200),transparent_60%)] dark:bg-[radial-gradient(circle_at_40%_20%,theme(colors.indigo.800),transparent_60%)]"></div>
                    <div class="relative space-y-6">
                        @if(!$submitted)
                            @if($trivia)
                                <div class="space-y-4">
                                    <h2 class="text-lg font-semibold leading-snug">{{$trivia->question}}</h2>
                                    <form wire:submit.prevent="submit" class="space-y-5">
                                        <div class="grid gap-3">
                                            @foreach(['A' => $trivia->option_a, 'B' => $trivia->option_b, 'C' => $trivia->option_c, 'D' => $trivia->option_d] as $key => $text)
                                                @php $selected = $answer === $key; @endphp
                                                <label class="group flex items-start gap-3 p-3 rounded-xl border cursor-pointer backdrop-blur transition relative overflow-hidden
                                                    {{ $selected
                                                        ? 'border-indigo-400 ring-2 ring-indigo-300/70 dark:ring-indigo-500/40 bg-white dark:bg-zinc-700 shadow'
                                                        : 'border-gray-200/70 dark:border-zinc-600 bg-white/50 dark:bg-zinc-700/40 hover:shadow-sm hover:bg-white dark:hover:bg-zinc-700' }} transition-all duration-200 ease-out" x-data="{pulse:false}" @click="pulse=true; setTimeout(()=>pulse=false,350)" :class="pulse ? 'scale-[1.015] shadow-md' : ''">
                                                    <input type="radio" wire:model="answer" value="{{$key}}" class="mt-1 w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-500 transition" />
                                                    <span class="flex-1 text-sm leading-relaxed">
                                                        <strong class="font-semibold mr-1 {{ $selected ? 'text-indigo-700 dark:text-indigo-300' : 'text-indigo-600 dark:text-indigo-300' }}">{{$key}})</strong>{{$text}}
                                                    </span>
                                                    @if($selected)
                                                        <span class="absolute inset-y-0 right-0 w-1.5 bg-gradient-to-b from-indigo-400 to-sky-400 dark:from-indigo-500 dark:to-sky-500"></span>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('answer')<div class="text-red-600 text-xs font-medium">{{$message}}</div>@enderror
                                        <div class="flex flex-wrap gap-3 items-center">
                                            <button type="submit" wire:loading.attr="disabled" wire:target="submit" class="px-5 py-2.5 rounded-lg bg-gradient-to-r from-indigo-600 to-sky-600 text-white text-sm font-semibold shadow hover:from-indigo-500 hover:to-sky-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span wire:loading.remove wire:target="submit">Enviar respuesta</span>
                                                <span wire:loading wire:target="submit" class="flex items-center gap-1">
                                                    <svg class="w-4 h-4 animate-spin text-white/80" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                                                    Enviando
                                                </span>
                                            </button>
                                            @if($preview)
                                                <span class="text-[11px] uppercase tracking-wider font-semibold text-indigo-500 dark:text-indigo-300">Modo Vista Previa</span>
                                            @endif
                                        </div>
                                    </form>
                                </div>
                            @else
                                <div class="p-5 rounded-xl bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 text-sm">
                                    <p class="font-medium text-amber-800 dark:text-amber-200">No hay trivia disponible por ahora.</p>
                                    @if($preview)
                                        <p class="mt-1 text-amber-600 dark:text-amber-300 text-xs">(En modo preview se muestra este mensaje si no existe trivia programada para hoy)</p>
                                    @endif
                                </div>
                            @endif
                        @else
                            <div x-data="{show:false}" x-init="setTimeout(()=>show=true,40)" x-show="show" x-transition.opacity.duration.350ms class="space-y-6 anim-fade-scale">
                                @if($attempt->is_correct)
                                    <div x-data x-init="if(!window.__confettiDone){window.__confettiDone=true;(function(){const colors=['#6366F1','#0EA5E9','#F59E0B','#10B981','#EC4899','#8B5CF6'];const pieces=45;for(let i=0;i<pieces;i++){const el=document.createElement('div');el.className='confetti-piece';const size=6+Math.random()*8;el.style.width=size+'px';el.style.height=(size*1.4)+'px';el.style.background=colors[Math.floor(Math.random()*colors.length)];el.style.left=(Math.random()*100)+'vw';el.style.animationDuration=(5+Math.random()*2)+'s';el.style.animationDelay=(Math.random()*0.3)+'s';el.style.transform='translateY(-10vh) rotateZ('+ (Math.random()*360)+'deg)';el.style.opacity=(0.6+Math.random()*0.4);el.style.filter='drop-shadow(0 0 2px rgba(0,0,0,.15))';document.body.appendChild(el);setTimeout(()=>el.remove(),8000);} })();}"></div>
                                @endif
                                <div class="space-y-1">
                                    <h2 class="text-xl md:text-2xl font-semibold flex items-center gap-2 anim-slide-up">
                                        @if($attempt->is_correct)
                                            <span class="inline-flex items-center gap-1">¡Excelente! <span class="text-2xl leading-none">🎉</span></span>
                                        @else
                                            <span class="inline-flex items-center gap-1">Sigue intentando <span class="text-2xl leading-none">😕</span></span>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $attempt->is_correct ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' }}">{{$attempt->is_correct ? 'Correcto' : 'Incorrecto'}}</span>
                                        @if(!$preview && isset($attempt->offline) && $attempt->offline)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300" title="{{$connectionError}}">Offline</span>
                                        @endif
                                        @if(!$preview && isset($attempt->attempt_order))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">Intento #{{$attempt->attempt_order}}</span>
                                        @endif
                                    </h2>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        @if(!$preview && isset($attempt->offline) && $attempt->offline)
                                            No se pudo conectar al router. @if(config('app.debug') && $connectionError)<span class="text-red-600 dark:text-red-400">{{$connectionError}}</span>@endif
                                        @else
                                            @if($attempt->is_correct)
                                                Ganaste <strong>{{$credentials['minutes']}} min</strong> de navegación. ¡Aprovéchalos aprendiendo! 💡
                                            @else
                                                Obtienes <strong>{{$credentials['minutes']}} min</strong> básicos. Reintenta para sumar más tiempo. 🔄
                                            @endif
                                        @endif
                                    </p>
                                </div>
                                <div class="grid md:grid-cols-2 gap-6 md:gap-7">
                                    <div class="p-5 md:p-6 rounded-xl bg-white/80 dark:bg-zinc-700/60 border border-gray-200 dark:border-zinc-600 space-y-3 shadow-sm relative overflow-hidden anim-slide-up" style="animation-delay:.05s;animation-fill-mode:both">
                                        @if(!$preview && isset($attempt->offline) && $attempt->offline)
                                            <div class="absolute inset-0 bg-red-500/5 pointer-events-none"></div>
                                        @endif
                                        <p class="text-xs md:text-[11px] font-semibold uppercase tracking-wide text-gray-500">Credenciales</p>
                                        <div class="text-sm md:text-base grid grid-cols-3 gap-y-1 md:gap-y-2 leading-relaxed">
                                            <div class="col-span-1 font-medium text-gray-600 dark:text-gray-300">Usuario</div>
                                            <div class="col-span-2 font-mono text-[13px] md:text-sm break-all">{{$credentials['username']}}</div>
                                            <div class="col-span-1 font-medium text-gray-600 dark:text-gray-300">Clave</div>
                                            <div class="col-span-2 font-mono text-[13px] md:text-sm break-all">{{$credentials['password']}}</div>
                                            <div class="col-span-1 font-medium text-gray-600 dark:text-gray-300">Minutos</div>
                                            <div class="col-span-2 font-mono text-[13px] md:text-sm">{{$credentials['minutes']}}</div>
                                        </div>
                                    </div>
                                    <div class="p-5 md:p-6 rounded-xl bg-gradient-to-br from-indigo-600/95 to-sky-600/95 text-white border border-indigo-500/40 space-y-3 shadow-sm anim-slide-up" style="animation-delay:.12s;animation-fill-mode:both">
                                        <p class="text-xs md:text-[11px] font-semibold uppercase tracking-wide text-indigo-100/80">Estado</p>
                                        <ul class="text-sm md:text-[15px] space-y-1 md:space-y-1.5 leading-relaxed">
                                            <li><span class="font-semibold">Resultado:</span> {{$attempt->is_correct ? 'Respuesta correcta' : 'Respuesta incorrecta'}} </li>
                                            <li><span class="font-semibold">Tiempo concedido:</span> {{$credentials['minutes']}} min</li>
                                            @if(!$preview)
                                                @if(isset($attempt->offline) && $attempt->offline)
                                                    <li><span class="font-semibold">Conexión:</span> sin enlace (offline)</li>
                                                @else
                                                    <li><span class="font-semibold">Conexión:</span> preparándose para redirigir...</li>
                                                @endif
                                            @else
                                                <li><span class="font-semibold">Conexión:</span> (simulada en preview)</li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                                @if(!$preview && (!isset($attempt->offline) || !$attempt->offline) && ($url = $this->hotspotLoginUrl()))
                                    <div class="space-y-2">
                                        <p class="text-sm">Serás redirigido automáticamente en <span x-data="{s:5, init(){ setInterval(()=>{ if(this.s>0){this.s--; if(this.s===0){ window.location='{{$url}}'; } } },1000)}}" x-text="s" class="font-semibold"></span> segundos...</p>
                                        <script>setTimeout(function(){ window.location = @json($url); }, 5000);</script>
                                        <a href="{{$url}}" class="inline-block text-xs font-medium text-indigo-600 dark:text-indigo-300 underline">Si no redirige haz clic aquí</a>
                                    </div>
                                @elseif($preview)
                                    <div class="p-3 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs">En preview no se realiza redirección automática.</div>
                                @elseif(!$preview && isset($attempt->offline) && $attempt->offline)
                                    <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 text-xs">
                                        No hay redirección porque no se creó usuario Mikrotik (offline).
                                        @if(config('app.debug') && $connectionError)
                                            <div class="mt-1 font-mono text-[10px] whitespace-pre-wrap">{{$connectionError}}</div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <aside class="lg:w-80 xl:w-88 flex flex-col gap-6">
                <div class="rounded-2xl border bg-white/70 dark:bg-zinc-800/70 dark:border-zinc-700 backdrop-blur p-5 space-y-4 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">¿Cómo funciona?</h3>
                    <ol class="text-xs space-y-2 list-decimal list-inside text-gray-600 dark:text-gray-400">
                        <li>Responde la pregunta diaria.</li>
                        <li>Si aciertas obtienes más minutos.</li>
                        <li>Recibes usuario y contraseña temporales.</li>
                        <li>Se abre el portal y navegas.</li>
                    </ol>
                    <div class="text-[11px] text-gray-400">Impulsa el aprendizaje motivando el acceso responsable.</div>
                </div>
                <div class="rounded-2xl border bg-gradient-to-br from-sky-500/90 to-indigo-600/90 text-white backdrop-blur p-5 space-y-3 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide">Diseño responsive</h3>
                    <p class="text-xs leading-relaxed text-sky-50/90">Esta tarjeta muestra cómo se adapta el portal cautivo en distintos tamaños de pantalla. En móviles los elementos se apilan y mantienen clic/touch amplio.</p>
                    @if($preview)
                        <p class="text-[11px] uppercase tracking-wider font-semibold text-sky-100/70">Modo Preview Activo</p>
                    @endif
                </div>
            </aside>
        </div>
    </main>
    <footer class="py-6 text-center text-[11px] text-gray-400 dark:text-gray-500">
        &copy; {{date('Y')}} Hotspot Educativo. {{ $preview ? 'Vista previa de experiencia.' : 'Acceso educativo.' }}
    </footer>
</div>
