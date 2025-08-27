<?php

namespace App\Http\Controllers;

use App\Models\RouterDevice;
use Illuminate\Http\Request;

class RouterLoginTemplateController extends Controller
{
    public function __invoke(Request $request, int $router)
    {
        $device = RouterDevice::findOrFail($router);
    // Token firmado para prevenir manipulación fácil del ID de router
    $token = hash_hmac('sha256', 'router:'.$device->id, config('app.key'));
    $action = rtrim(config('app.url'), '/').'/hotspot?router='.$device->id.'&rtoken='.$token;
        // Plantilla básica Mikrotik login.html para redirigir al portal educativo
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>Hotspot Educativo - Redirigiendo...</title>
    <meta http-equiv="Cache-control" content="no-cache" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <style>
        body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#0f172a;color:#fff;margin:0}
        .box{max-width:420px;padding:32px;background:#1e293b;border-radius:16px;box-shadow:0 8px 30px -4px rgba(0,0,0,.4)}
        h1{font-size:18px;margin:0 0 12px;font-weight:600}
        p{font-size:14px;line-height:1.4;margin:0 0 16px}
        .spinner{width:40px;height:40px;border:4px solid #334155;border-top-color:#6366f1;border-radius:50%;animation:spin 1s linear infinite;margin:16px auto}
        @keyframes spin{to{transform:rotate(360deg)}}
        code{font-size:12px;background:#334155;padding:2px 4px;border-radius:4px}
    </style>
</head>
<body>
    <div class="box">
        <h1>Portal educativo</h1>
        <p>Redirigiendo a la trivia para obtener acceso a Internet...</p>
        <div class="spinner"></div>
        <noscript><p>Activa JavaScript y vuelve a intentarlo.</p></noscript>
        <!-- Formulario oculto que envía parámetros del hotspot -->
        <form name="redirect" action="{$action}" method="get">
            <input type="hidden" name="mac" value="$(mac)">
            <input type="hidden" name="ip" value="$(ip)">
            <input type="hidden" name="username" value="$(username)">
            <input type="hidden" name="link-login" value="$(link-login)">
            <input type="hidden" name="link-orig" value="$(link-orig)">
            <input type="hidden" name="error" value="$(error)">
        </form>
        <script>
            // Auto submit inmediato
            document.redirect.submit();
        </script>
        <p style="margin-top:20px;font-size:11px;opacity:.6">Si no avanza automáticamente <a href="#" onclick="document.redirect.submit();return false;" style="color:#818cf8">haz clic aquí</a>.</p>
        <!-- Instrucciones (comentario) para el administrador:
    1. Sube este archivo como hotspot/login.html en el Mikrotik.
    2. Asegúrate que el portal tenga salida a: {$action}
    3. El parámetro rtoken se valida para evitar manipulación del router ID.
    4. Puedes personalizar estilos manteniendo los input hidden.
        -->
    </div>
</body>
</html>
HTML;
        $filename = 'login-router-'.$device->id.'.html';
        return response($html)
            ->header('Content-Type','text/html; charset=UTF-8')
            ->header('Content-Disposition','attachment; filename="'.$filename.'"');
    }
}
