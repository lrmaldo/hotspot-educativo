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
                                @if(!$preview && (!isset($attempt->offline) || !$attempt->offline))
                                    <div class="space-y-2" x-data="hotspotLogin({
                                            username: @js($credentials['username']),
                                            password: @js($credentials['password']),
                                            mk: @js($mikrotik)
                                        })" x-init="init()">
                                        <template x-if="!ready">
                                            <p class="text-sm">Preparando conexión al hotspot...</p>
                                        </template>
                                        <template x-if="ready && submitting">
                                            <p class="text-sm">Conectando al hotspot...</p>
                                        </template>
                                        <template x-if="error">
                                            <div class="text-xs text-red-600" x-text="error"></div>
                                        </template>
                                        <template x-if="manualUrl">
                                            <a :href="manualUrl" class="inline-block text-xs font-medium text-indigo-600 dark:text-indigo-300 underline">Acceso manual</a>
                                        </template>
                                        <form x-ref="form" x-show="false" method="post">
                                            <input type="hidden" name="username" :value="finalUser">
                                            <input type="hidden" name="password" :value="finalPass">
                                            <input type="hidden" name="dst" :value="mk['link-orig'] || ''">
                                            <input type="hidden" name="popup" value="true">
                                        </form>
                                        <script>
                                            function hotspotLogin({username,password,mk}){
                                                return {
                                                    mk, username, password,
                                                    ready:false, submitting:false, error:null, manualUrl:null,
                                                    finalUser:'', finalPass:'',
                                                    init(){
                                                        this.finalUser = this.username;
                                                        const base = mk['link-login-only'] || mk['link-login'];
                                                        if(!base){
                                                            // Fallback manual usando host capturado o router host en backend (incrustado vía dataset opcional)
                                                            this.manualUrl = '#';
                                                            this.error='No llegaron parámetros link-login (-only). Verifica login.html en el router.';
                                                            this.ready=true;return;
                                                        }
                                                        // Limpiar query existente
                                                        let action = base.replace(/\?.*$/,'');
                                                        this.$refs.form.action = action;
                                                        // CHAP
                                                        if(mk['chap-id']){
                                                            this.finalPass = this.chapHash(mk['chap-id'], this.password, mk['chap-challenge']);
                                                        } else {
                                                            this.finalPass = this.password;
                                                        }
                                                        this.ready = true;
                                                        this.submitting = true;
                                                        setTimeout(()=>{ this.$refs.form.submit(); }, 200);
                                                    },
                                                    chapHash(chapId, pwd, chapChallenge){
                                                        // MD5(chap-id + password + chap-challenge)
                                                        return hexMD5(chapId + pwd + chapChallenge);
                                                    }
                                                }
                                            }
                                            // MD5 implementación ligera
                                            /* eslint-disable */
                                            function hexMD5(s){function L(k,d){return(k<<d)|(k>>>(32-d))}function K(G,k){var I,D,F,H,E;F=(G&2147483648);H=(k&2147483648);I=(G&1073741824);D=(k&1073741824);E=(G&1073741823)+(k&1073741823);if(I&D){return(E^2147483648^F^H)}if(I|D){if(E&1073741824){return(E^3221225472^F^H)}else{return(E^1073741824^F^H)}}else{return(E^F^H)}}function r(d,F,k){return(d&F)|((~d)&k)}function q(d,F,k){return(d&k)|(F&(~k))}function p(d,F,k){return(d^F^k)}function n(d,F,k){return(F^(d|(~k)))}function u(G,F,aa,Z,k,H,I){G=K(G,K(K(r(F,aa,Z),k),I));return K(L(G,H),F)}function f(G,F,aa,Z,k,H,I){G=K(G,K(K(q(F,aa,Z),k),I));return K(L(G,H),F)}function D(G,F,aa,Z,k,H,I){G=K(G,K(K(p(F,aa,Z),k),I));return K(L(G,H),F)}function t(G,F,aa,Z,k,H,I){G=K(G,K(K(n(F,aa,Z),k),I));return K(L(G,H),F)}function e(G){var Z;var F=G.length;var x=F+8;var k=(x-(x%64))/64;var I=(k+1)*16;var aa=Array(I-1);var d=0;var H=0;while(H<F){Z=(H-(H%4))/4;d=(H%4)*8;aa[Z]=(aa[Z]| (G.charCodeAt(H)<<d));H++}Z=(H-(H%4))/4;d=(H%4)*8;aa[Z]=aa[Z]| (128<<d);aa[I-2]=F<<3;aa[I-1]=F>>>29;return aa}function B(x){var k="",F="",G,d;for(d=0;d<=3;d++){G=(x>>>(d*8))&255;F="0"+G.toString(16);k+=F.substr(F.length-2,2)}return k}function J(k){k=k.replace(/\r\n/g,"\n");var d="";for(var F=0;F<k.length;F++){var x=k.charCodeAt(F);if(x<128){d+=String.fromCharCode(x)}else{if((x>127)&&(x<2048)){d+=String.fromCharCode((x>>6)|192);d+=String.fromCharCode((x&63)|128)}else{d+=String.fromCharCode((x>>12)|224);d+=String.fromCharCode(((x>>6)&63)|128);d+=String.fromCharCode((x&63)|128)}}}return d}var C=Array();var P,h,E,v,g,Y,X,W,V;var S=7,Q=12,N=17,M=22;var A=5,z=9,y=14,w=20;var o=4,m=11,l=16,j=23;var U=6,T=10,R=15,O=21;s=J(s);C=e(s);Y=1732584193;X=4023233417;W=2562383102;V=271733878;for(P=0;P<C.length;P+=16){h=Y;E=X;v=W;g=V;Y=u(Y,X,W,V,C[P+0],S,3614090360);V=u(V,Y,X,W,C[P+1],Q,3905402710);W=u(W,V,Y,X,C[P+2],N,606105819);X=u(X,W,V,Y,C[P+3],M,3250441966);Y=u(Y,X,W,V,C[P+4],S,4118548399);V=u(V,Y,X,W,C[P+5],Q,1200080426);W=u(W,V,Y,X,C[P+6],N,2821735955);X=u(X,W,V,Y,C[P+7],M,4249261313);Y=u(Y,X,W,V,C[P+8],S,1770035416);V=u(V,Y,X,W,C[P+9],Q,2336552879);W=u(W,V,Y,X,C[P+10],N,4294925233);X=u(X,W,V,Y,C[P+11],M,2304563134);Y=u(Y,X,W,V,C[P+12],S,1804603682);V=u(V,Y,X,W,C[P+13],Q,4254626195);W=u(W,V,Y,X,C[P+14],N,2792965006);X=u(X,W,V,Y,C[P+15],M,1236535329);Y=f(Y,X,W,V,C[P+1],A,4129170786);V=f(V,Y,X,W,C[P+6],z,3225465664);W=f(W,V,Y,X,C[P+11],y,643717713);X=f(X,W,V,Y,C[P+0],w,3921069994);Y=f(Y,X,W,V,C[P+5],A,3593408605);V=f(V,Y,X,W,C[P+10],z,38016083);W=f(W,V,Y,X,C[P+15],y,3634488961);X=f(X,W,V,Y,C[P+4],w,3889429448);Y=f(Y,X,W,V,C[P+9],A,568446438);V=f(V,Y,X,W,C[P+14],z,3275163606);W=f(W,V,Y,X,C[P+3],y,4107603335);X=f(X,W,V,Y,C[P+8],w,1163531501);Y=f(Y,X,W,V,C[P+13],A,2850285829);V=f(V,Y,X,W,C[P+2],z,4243563512);W=f(W,V,Y,X,C[P+7],y,1735328473);X=f(X,W,V,Y,C[P+12],w,2368359562);Y=D(Y,X,W,V,C[P+5],o,4294588738);V=D(V,Y,X,W,C[P+8],m,2272392833);W=D(W,V,Y,X,C[P+11],l,1839030562);X=D(X,W,V,Y,C[P+14],j,4259657740);Y=D(Y,X,W,V,C[P+1],o,2763975236);V=D(V,Y,X,W,C[P+4],m,1272893353);W=D(W,V,Y,X,C[P+7],l,4139469664);X=D(X,W,V,Y,C[P+10],j,3200236656);Y=D(Y,X,W,V,C[P+13],o,681279174);V=D(V,Y,X,W,C[P+0],m,3936430074);W=D(W,V,Y,X,C[P+3],l,3572445317);X=D(X,W,V,Y,C[P+6],j,76029189);Y=t(Y,X,W,V,C[P+5],U,3654602809);V=t(V,Y,X,W,C[P+8],T,3873151461);W=t(W,V,Y,X,C[P+11],R,530742520);X=t(X,W,V,Y,C[P+14],O,3299628649);Y=t(Y,X,W,V,C[P+1],U,4096336452);V=t(V,Y,X,W,C[P+4],T,1126891415);W=t(W,V,Y,X,C[P+7],R,2878612391);X=t(X,W,V,Y,C[P+10],O,4237533241);Y=t(Y,X,W,V,C[P+13],U,1700485571);V=t(V,Y,X,W,C[P+0],T,2399980690);W=t(W,V,Y,X,C[P+3],R,4293915773);X=t(X,W,V,Y,C[P+6],O,2240044497);Y=t(Y,X,W,V,C[P+9],U,1873313359);V=t(V,Y,X,W,C[P+14],T,4264355552);W=t(W,V,Y,X,C[P+5],R,2734768916);X=t(X,W,V,Y,C[P+12],O,1309151649);Y=t(Y,X,W,V,C[P+2],U,4149444226);V=t(V,Y,X,W,C[P+7],T,3174756917);W=t(W,V,Y,X,C[P+10],O,718787259);X=t(X,W,V,Y,C[P+13],U,3951481745);Y=K(Y,h);X=K(X,E);W=K(W,v);V=K(V,g)}return (B(Y)+B(X)+B(W)+B(V)).toLowerCase();}
                                        </script>
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
