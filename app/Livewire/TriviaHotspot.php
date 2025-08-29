<?php

namespace App\Livewire;

use App\Models\Attempt;
use App\Models\Trivia;
use App\Services\MikrotikHotspotService;
use App\Models\RouterDevice;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class TriviaHotspot extends Component
{
    public ?Trivia $trivia = null;
    public string $answer = '';
    public bool $submitted = false;
    public ?array $credentials = null;
    public ?Attempt $attempt = null;
    public bool $preview = false; // modo preview (no crea usuarios reales)
    protected ?int $routerId = null; // id router validado
    public ?RouterDevice $routerDevice = null; // instancia elegida (public para la vista)
    public ?string $connectionError = null; // detalle cuando cae a offline
    public array $mikrotik = []; // parámetros capturados del login (link-login-only, chap-id, etc.)

    public function mount(): void
    {
    $this->preview = (bool) request()->query('preview');
    $this->resolveRouterFromRequest();
        // Capturar parámetros Mikrotik para posterior auto-login CHAP
        $this->mikrotik = request()->only([
            'link-login-only','link-login','link-orig','chap-id','chap-challenge','username','error','mac','ip','login-host','login-ip','popup'
        ]);
        $this->trivia = Trivia::getToday();
        if (!$this->trivia && $this->preview) {
            // Crear instancia temporal sin guardar
            $this->trivia = new Trivia([
                'question' => '¿Ejemplo de pregunta para el portal cautivo educativo?',
                'option_a' => 'Opción A',
                'option_b' => 'Opción B',
                'option_c' => 'Opción C',
                'option_d' => 'Opción D',
                'correct_option' => 'A',
            ]);
        }
    }

    protected function identifier(): string
    {
        // 1) Usar MAC real si viene y no es el placeholder literal "$(mac)"
        $mac = request()->query('mac');
        if ($mac && !str_starts_with($mac, '$(')) {
            return strtolower($mac);
        }
        // 2) Evitar que todas las pruebas locales usen el mismo 127.0.0.1 bloqueando intentos.
        // Guardamos un identificador estable en sesión mientras dure la sesión.
        $sessionId = session()->get('hotspot_identifier');
        if (!$sessionId) {
            $sessionId = 'sess_'.substr(bin2hex(random_bytes(8)),0,12);
            session()->put('hotspot_identifier', $sessionId);
        }
        return $sessionId;
    }

    protected function resolveRouterFromRequest(): void
    {
        $id = request()->integer('router');
        $token = request()->query('rtoken');
        if ($id && $token && hash_equals(hash_hmac('sha256', 'router:'.$id, config('app.key')), $token)) {
            $candidate = RouterDevice::enabled()->find($id);
            if ($candidate) {
                $this->routerDevice = $candidate;
                $this->routerId = $candidate->id;
                return;
            }
        }
        // Fallback al default
        $default = RouterDevice::enabled()->where('is_default', true)->first();
        if (! $default) {
            $default = RouterDevice::enabled()->orderByDesc('is_default')->orderBy('id')->first();
        }
        $this->routerDevice = $default;
        $this->routerId = $default?->id;
    }

    public function submit()
    {
        $this->validate([
            'answer' => 'required|in:A,B,C,D'
        ]);

        // Verificar si ya respondió hoy (pero flexibilizar en entorno local/debug para pruebas)
    // Intentos ilimitados: se elimina cualquier bloqueo diario.

        if (! $this->trivia) {
            $this->addError('answer', 'No hay trivia disponible.');
            return;
        }

        $isCorrect = $this->answer === $this->trivia->correct_option;
        $minutes = $isCorrect
            ? (int) config('services.mikrotik.minutes_correct', 30)
            : (int) config('services.mikrotik.minutes_incorrect', 5);

        // Si se especificó router, instanciar servicio con ese dispositivo
        $mikrotik = null;
        if (!$this->preview && $this->routerDevice) {
            $mikrotik = new MikrotikHotspotService($this->routerDevice);
        } elseif (!$this->preview && !$this->routerDevice) {
            \Log::warning('TriviaHotspot sin routerDevice válido, modo offline forzado');
            $this->connectionError = 'No se encontró router habilitado (param router/rtoken inválido o ninguno habilitado).';
        }

        if ($this->preview) {
            // Simula credenciales sin tocar Mikrotik
            $creds = [
                'username' => 'demoUser',
                'password' => 'demoPass',
                'minutes' => $minutes,
            ];
        } else {
            if ($mikrotik) {
                try {
                    $creds = $mikrotik->createTemporaryUser($minutes);
                } catch (\Throwable $e) {
                    $msg = $e->getMessage();
                    \Log::error('Fallo creación usuario Mikrotik '.($this->routerDevice? 'router='.$this->routerDevice->id: '').' error='.$msg);
                    $this->connectionError = $msg;
                    $creds = [ 'username' => 'offline','password' => 'offline','minutes' => 0 ];
                    $this->addError('answer', 'No se pudo conectar al router.');
                }
            } else {
                $creds = [ 'username' => 'offline','password' => 'offline','minutes' => 0 ];
                $this->addError('answer', 'Router no configurado.');
            }
        }

        if (!$this->preview) {
            $order = Attempt::query()
                ->where('identifier', $this->identifier())
                ->whereDate('created_at', now()->toDateString())
                ->count() + 1;
            $this->attempt = Attempt::create([
                'trivia_id' => $this->trivia->id,
                'identifier' => $this->identifier(),
                'ip' => request()->query('ip', request()->ip()),
                'mac' => request()->query('mac'),
                'selected_option' => $this->answer,
                'is_correct' => $isCorrect,
                'mikrotik_username' => $creds['username'],
                'mikrotik_password' => $creds['password'],
        'granted_minutes' => $creds['minutes'],
                'offline' => ($creds['username'] === 'offline'),
                'connection_error' => $this->connectionError,
                'attempt_order' => $order,
            ]);
        } else {
            // Falso Attempt para mostrar estructura
            $this->attempt = new Attempt([
                'is_correct' => $isCorrect,
            ]);
        }

        $this->submitted = true;
        $this->credentials = $creds;
    }

    public function hotspotLoginUrl(): ?string
    {
        if (! $this->credentials) return null;
        if ($this->credentials['username'] === 'offline') return null;

        // Usar estrictamente link-login-only (ya apunta a la IP HTTP del hotspot). Si no existe, link-login.
        $loginOnly = request()->query('link-login-only');
        $login = request()->query('link-login');
        $loginHost = request()->query('login-host');
        $loginIp = request()->query('login-ip');
        $chapId = request()->query('chap-id');
        $chapChallenge = request()->query('chap-challenge');
    // Si tenemos chap-id preferimos el nuevo flujo de formulario, retornamos null para que la vista use el auto-post.
    if ($chapId) return null;
        if (config('app.debug')) {
            static $logged=false; if(!$logged){
                $logged=true;
                \Log::debug('Hotspot full query', request()->query());
                \Log::debug('Hotspot redirect params (simplified)', [
                    'link-login-only'=>$loginOnly,
                    'link-login'=>$login,
                    'login-host'=>$loginHost,
                    'login-ip'=>$loginIp,
                    'chap-id'=>$chapId,
                    'router_host'=>$this->routerDevice?->host,
                ]);
            }
        }

        $base = $loginOnly ?: $login;
        if (! $base) {
            // Fallback: construir con host/ip capturado
            $candidate = $loginHost ?: $loginIp;
            if($candidate){
                // asegurar protocolo
                if(!preg_match('~^https?://~i',$candidate)) { $candidate = 'http://'.$candidate; }
                $base = rtrim($candidate,'/').'/login';
            } elseif ($this->routerDevice) {
                // Último fallback: host del router (sin puerto API)
                $base = 'http://'.$this->routerDevice->host.'/login';
            } else {
                return null;
            }
        }

        // Si la URL trae query, la limpiamos para agregar credenciales limpias
        $base = preg_replace('~\?.*$~','',$base);

        // NOTA: Si existe chap-id se debería hacer POST con hash MD5 (fase siguiente). Mientras tanto usamos GET plano.
        return $base.'?username='.urlencode($this->credentials['username']).'&password='.urlencode($this->credentials['password']);
    }

    public function render()
    {
        return view('livewire.trivia-hotspot');
    }
}
