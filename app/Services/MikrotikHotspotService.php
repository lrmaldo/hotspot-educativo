<?php

namespace App\Services;

use Exception;
use App\Models\RouterDevice;
use Illuminate\Support\Str;

/**
 * Servicio para crear usuarios temporales en Mikrotik Hotspot.
 * Requiere paquete routeros-api-php (agregar en composer) o similar interfaz.
 */
class MikrotikHotspotService
{
    protected string $host = '';
    protected string $username = '';
    protected string $password = '';
    protected int $port = 8728;
    protected bool $ssl = false;
    protected int $timeout = 5;

    public function __construct(?RouterDevice $device = null)
    {
        if ($device) {
            $this->host = (string) $device->host;
            $this->username = (string) $device->username;
            $this->password = (string) $device->password; // decrypted accessor
            $this->port = (int) ($device->port ?? 8728);
            $this->ssl = (bool) ($device->ssl ?? false);
            $this->timeout = (int) ($device->timeout ?? 5);
        } else {
            $this->host = (string) (config('services.mikrotik.host') ?? '');
            $this->username = (string) (config('services.mikrotik.username') ?? '');
            $this->password = (string) (config('services.mikrotik.password') ?? '');
            $this->port = (int) (config('services.mikrotik.port', 8728));
            $this->ssl = (bool) env('MIKROTIK_SSL', false);
            $this->timeout = (int) env('MIKROTIK_TIMEOUT', 5);
        }
    }

    /**
     * Crea un usuario hotspot temporal.
     * @param int $minutes Tiempo de navegación (limit-uptime) en minutos.
     * @return array [username,password,minutes]
     * @throws Exception
     */
    public function createTemporaryUser(int $minutes): array
    {
        $user = 'edu'.Str::lower(Str::random(6));
        $pass = Str::random(8);
        $uptime = $minutes.'m';

        $client = $this->client();

        $q = (new \RouterOS\Query('/ip/hotspot/user/add'))
            ->equal('name', $user)
            ->equal('password', $pass)
            ->equal('limit-uptime', $uptime)
            ->equal('disabled', 'no');

        $client->query($q)->read();

        return [ 'username' => $user, 'password' => $pass, 'minutes' => $minutes ];
    }

    /**
     * Limpia usuarios expirados (limit-uptime consumido) devolviendo array de nombres eliminados.
     */
    public function cleanupExpired(bool $dryRun = false): array
    {
        $client = $this->client();
        $users = $client->query(new \RouterOS\Query('/ip/hotspot/user/print'))->read();
        $removed = [];
        foreach ($users as $u) {
            if (!isset($u['name'])) continue;
            // Heurística: eliminar usuarios con prefijo 'edu' y uptime>=limit-uptime
            if (str_starts_with($u['name'], 'edu') && isset($u['uptime'], $u['limit-uptime']) && $u['uptime'] >= $u['limit-uptime']) {
                if (!$dryRun && isset($u['.id'])) {
                    $client->query((new \RouterOS\Query('/ip/hotspot/user/remove'))->equal('.id', $u['.id']))->read();
                }
                $removed[] = $u['name'];
            }
        }
        return $removed;
    }

    /**
     * Devuelve instancia de cliente RouterOS con manejo de errores estandarizado.
     */
    protected function client()
    {
        if ($this->host === '' || $this->username === '') {
            throw new Exception('Configuración Mikrotik incompleta (host/username vacío)');
        }
        if (! class_exists('RouterOS\\Client')) {
            throw new Exception('RouterOS Client class not found. Run: composer require evilfreelancer/routeros-api-php');
        }
        try {
            return new \RouterOS\Client([
                'host' => $this->host,
                'user' => $this->username,
                'pass' => $this->password,
                'port' => $this->port,
                'timeout' => $this->timeout,
                'ssl' => $this->ssl,
                // 'attempts' => 1, // se puede habilitar para conexiones lentas
            ]);
        } catch (Exception $e) {
            report($e);
            throw new Exception('No se pudo conectar al Mikrotik: '.$e->getMessage());
        }
    }

    /**
     * Prueba la conexión devolviendo array con estado y mensaje.
     */
    public function testConnection(): array
    {
        try {
            $t0 = microtime(true);
            $client = $this->client();
            $client->query(new \RouterOS\Query('/system/resource/print'))->read();
            $elapsed = round((microtime(true) - $t0)*1000); // ms
            return ['ok' => true, 'message' => 'Conexión OK '.$this->host.':'.$this->port.' '.($this->ssl?'(SSL) ':'').'~'.$elapsed.'ms'];
        } catch (Exception $e) {
            $hint = '';
            if (str_contains($e->getMessage(), 'reading 1 bytes')) {
                $hint = ' Posibles causas: puerto incorrecto, firewall, mismatch SSL, o dispositivo no responde al API.';
            } elseif (str_contains(strtolower($e->getMessage()), 'timeout')) {
                $hint = ' Verifica reachability (ping) y que el API Mikrotik esté habilitado (ip service print).';
            } elseif (str_contains(strtolower($e->getMessage()), 'invalid user name or password')) {
                $hint = ' Revisa: 1) Usuario existe en /user print. 2) Tiene policy api activada. 3) No hay espacios ocultos. 4) Si la contraseña contiene caracteres especiales prueba regenerarla sin ; ni # ni comillas.';
                \Log::warning('Fallo login Mikrotik user='.$this->username.' host='.$this->host.' port='.$this->port);
            }
            if (empty($this->password)) {
                $hint .= ' (La contraseña obtenida está vacía: vuelve a guardarla en el panel para re-encriptarla)';
            }
            return ['ok' => false, 'message' => $e->getMessage().$hint];
        }
    }
}
