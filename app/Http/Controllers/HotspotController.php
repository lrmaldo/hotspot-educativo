<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HotspotController extends Controller
{
    /**
     * Muestra la página del hotspot con la trivia (wrapper de Livewire component).
     * Mikrotik puede anexar parámetros como gw, mac, ip al redirect.
     * Maneja tanto GET (acceso directo) como POST (desde login.html de MikroTik).
     */
    public function show(Request $request)
    {
        // Si viene por POST desde MikroTik, los parámetros vienen en el body
        // Si viene por GET, pueden venir en query string
        $mikrotikParams = $request->method() === 'POST'
            ? $request->all()
            : $request->query();

        // Guardar los parámetros en la sesión para uso posterior
        if (!empty($mikrotikParams)) {
            session(['mikrotik_params' => $mikrotikParams]);
        }

        // Los parámetros se pueden usar en el componente vía request()
        return view('hotspot');
    }

    /**
     * Maneja el envío POST del formulario de conexión al hotspot.
     * Redirige al portal de MikroTik con las credenciales.
     */
    public function connect(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'dst' => 'nullable|string',
            'popup' => 'nullable|string',
        ]);

        // Obtener los parámetros del hotspot desde la sesión o request
        $mikrotik = session('mikrotik_params', []);

        // Si no hay parámetros en sesión, intentar obtenerlos de la request
        if (empty($mikrotik)) {
            $mikrotik = [
                'link-login' => $request->get('link-login'),
                'link-login-only' => $request->get('link-login-only'),
                'chap-id' => $request->get('chap-id'),
                'chap-challenge' => $request->get('chap-challenge'),
                'link-orig' => $request->get('link-orig'),
                'mac' => $request->get('mac'),
                'ip' => $request->get('ip'),
                'username' => $request->get('username'),
            ];
        }

        // Determinar la URL del portal de MikroTik
        $loginUrl = $mikrotik['link-login-only'] ?? $mikrotik['link-login'] ?? null;

        if (!$loginUrl) {
            return back()->withErrors(['error' => 'No se encontró la URL del portal de hotspot']);
        }

        // Limpiar la URL (quitar parámetros existentes)
        $portalUrl = preg_replace('/\?.*$/', '', $loginUrl);

        // Preparar los datos para enviar al MikroTik
        $formData = [
            'username' => $request->username,
            'password' => $request->password,
            'dst' => $request->dst ?: ($mikrotik['link-orig'] ?? ''),
            'popup' => $request->popup ?: 'true',
        ];

        // Si hay autenticación CHAP, el password ya viene hasheado desde el frontend
        // No necesitamos procesarlo aquí

        // Crear el formulario HTML que se auto-envía al portal de MikroTik
        $formHtml = $this->buildAutoSubmitForm($portalUrl, $formData);

        return response($formHtml);
    }

    /**
     * Construye un formulario HTML que se auto-envía al portal de MikroTik
     */
    private function buildAutoSubmitForm($action, $data)
    {
        $inputs = '';
        foreach ($data as $name => $value) {
            $inputs .= sprintf('<input type="hidden" name="%s" value="%s">',
                htmlspecialchars($name),
                htmlspecialchars($value)
            );
        }

        return sprintf('
<!DOCTYPE html>
<html>
<head>
    <title>Conectando al Hotspot...</title>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%%, #764ba2 100%%);
            color: white;
        }
        .container {
            text-align: center;
            padding: 2rem;
            background: rgba(255,255,255,0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        .spinner {
            border: 4px solid rgba(255,255,255,0.3);
            border-radius: 50%%;
            border-top: 4px solid #fff;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0%% { transform: rotate(0deg); }
            100%% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🌐 Conectando al Hotspot</h2>
        <div class="spinner"></div>
        <p>Serás redirigido automáticamente al portal...</p>
        <form id="hotspotForm" method="post" action="%s">
            %s
        </form>
        <script>
            setTimeout(function() {
                document.getElementById("hotspotForm").submit();
            }, 1500);
        </script>
    </div>
</body>
</html>',
            htmlspecialchars($action),
            $inputs
        );
    }
}
