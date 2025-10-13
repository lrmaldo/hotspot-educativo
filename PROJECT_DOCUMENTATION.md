# Brainet — Documentación del proyecto

Resumen
-------
Brainet es una solución desarrollada para un plantel educativo que permite a los alumnos obtener acceso temporal a Internet tras completar una trivia temática. La app se integra con portales cautivos MikroTik mediante plantillas `login.html` y soporta autenticación CHAP (MD5) cuando el portal lo requiere.

Objetivo del entregable
-----------------------
Proveer una documentación técnica y operativa para desplegar, probar y validar la integración entre MikroTik y la aplicación Laravel (Livewire). El documento explica el flujo: recepción del portal cautivo, presentación de trivia, generación de credenciales temporales y redirección automática al portal.

Arquitectura general
--------------------
- Framework backend: Laravel 12 (PHP 8.2)
- UI: Blade + Livewire (Livewire 3, Volt) y Tailwind CSS (v4)
- Componentes principales:
  - `RouterLoginTemplateController` — Genera el archivo `login.html` que se sube al MikroTik. Ese archivo envía un POST a `/hotspot` con variables del hotspot (mac, ip, chap-challenge, etc.).
  - `HotspotController` — Maneja GET y POST a `/hotspot`. Guarda parámetros de MikroTik en sesión (`mikrotik_params`) y muestra la vista que contiene el componente Livewire.
  - `App\Livewire\TriviaHotspot` — Lógica de la trivia: carga la pregunta, procesa el intento, crea credenciales temporales y determina si la redirección debe ser automática u offline.
  - `resources/views/livewire/trivia-hotspot.blade.php` — Vista principal que muestra la pregunta, credenciales y el formulario que finalmente envía credenciales al portal MikroTik (POST). Contiene JS para CHAP MD5 y auto-submit.

Flujo de datos (resumen)
------------------------
1. Administrador sube `login.html` generado por `RouterLoginTemplateController` al MikroTik.
2. Un cliente se conecta y el MikroTik muestra `login.html`. Éste envía un POST a `/hotspot` con parámetros del hotspot (mac, ip, chap-id, chap-challenge, link-login, link-login-only, link-orig, username, error).
3. `HotspotController@show` recibe y guarda los parámetros en sesión (`mikrotik_params`) y carga la vista `hotspot` que contiene el Livewire `TriviaHotspot`.
4. Usuario responde la trivia. El componente crea/solicita credenciales temporales y guarda el intento.
  - Regla de tiempos del plantel: si la respuesta es incorrecta se genera un acceso de perfil básico por **5 minutos**; si la respuesta es correcta se genera un acceso de **30 minutos**.
5. Vista `trivia-hotspot.blade.php` presenta usuario/clave y, usando JS (sin Alpine), ejecuta CHAP MD5 si el router lo exige y envía el formulario POST directamente al `link-login-only` o `link-login` del MikroTik.
6. MikroTik valida las credenciales y concede acceso al cliente.

Archivos clave y notas rápidas
-----------------------------
- `app/Http/Controllers/RouterLoginTemplateController.php` — Genera `login-router-{id}.html`. Revisa la variable `$action` (ruta `/hotspot?router={id}&rtoken={token}`) y confirma que el archivo se suba como `hotspot/login.html` en MikroTik.
- `app/Http/Controllers/HotspotController.php` — Define `show(Request $)` que acepta POST/GET y guarda `mikrotik_params` en sesión. También contiene `connect()` si la app procesa conexión internamente.
- `app/Livewire/TriviaHotspot.php` — Monta parámetros de Mikrotik desde el request o la sesión, resuelve router por `router`+`rtoken` (HMAC con `app.key`), y genera credenciales.
- `resources/views/livewire/trivia-hotspot.blade.php` — Vista interactiva con:
  - `hexMD5()` para CHAP
  - Formulario `name="login"` que envía a `link-login-only` o `link-login` (método POST)
  - JS nativo para auto-submit (countdown) y fallback manual

Rutas relevantes
----------------
- `GET|POST /hotspot` → `HotspotController@show`  (recibe POST desde MikroTik o GET directo)
- `POST /hotspot/connect` → `HotspotController@connect` (opcional; usado si la app envía credenciales a través de Laravel en vez de directo al MikroTik)
- `GET /hotspot/preview` → Vista de preview
- Rutas administrativas Livewire bajo `admin/*` (Livewire components auto-registered)

Comandos de desarrollo / pruebas
--------------------------------
- Iniciar servidor local: `php artisan serve --host=127.0.0.1 --port=8000`
- Ejecutar tests: `php artisan test` o `vendor/bin/phpunit`
- Limpiar cache: `php artisan config:clear && php artisan route:clear`
- Ejecutar Pint (formateo): `vendor/bin/pint`
- Frontend (vite/npm): `npm install && npm run dev` o `npm run build` para producción

Integraciones y consideraciones importantes
-----------------------------------------
- CHAP MD5: la vista incluye una implementación JS `hexMD5()` que calcula el hash necesario cuando el router envía `chap-id` y `chap-challenge`. El frontend modifica el campo `password` antes de submit.
- CSRF: El `login.html` generado por MikroTik hace POST directo a `/hotspot` y no incluye token CSRF. Por eso el middleware CSRF del framework se debe excluir para esta ruta (o aceptar POST sin token). Revisa `app/Http/Middleware/VerifyCsrfToken.php` o la configuración en `bootstrap/app.php`.
- Seguridad del token de router: El `RouterLoginTemplateController` firma el ID del router con `hash_hmac('sha256','router:'.$id, config('app.key'))` y espera validarlo en `TriviaHotspot::resolveRouterFromRequest()`.

Convenciones y patrones del proyecto
-----------------------------------
- Livewire/Volt (v3/v1) se usa para UI interactiva. Los componentes Livewire viven en `app/Livewire` y son referenciados en rutas como `hotspot.trivia`.
- Evitar Alpine para el login flow: el proyecto migró de Alpine a JS vanilla para evitar problemas de inicialización y popups bloqueados.
- Usar sesiones para pasar parámetros Mikrotik entre la recepción POST inicial y el envío de credenciales.
- Los controladores deben usar `Request` DI y métodos explícitos (`show`, `connect`) en lugar de `__invoke` si manejan múltiples verbos.

Checklist de entrega (verificable)
----------------------------------
- [ ] Generar `login-router-{id}.html` y subirlo a MikroTik como `hotspot/login.html`.
- [ ] Acceder al SSID, confirmar que `login.html` envía POST a `/hotspot` y que `HotspotController@show` guarda `mikrotik_params` en sesión.
- [ ] Completar la trivia, recibir credenciales, y confirmar que el formulario se envía (auto-submit) y que MikroTik concede acceso.
- [ ] Verificar la política de tiempos: respuestas incorrectas generan credenciales de **5 minutos**; respuestas correctas generan credenciales de **30 minutos**. Comprobar que los minutos se reflejan en `$credentials['minutes']` y en el comportamiento del router.
- [ ] Si el router usa CHAP: verificar que `hexMD5()` produce el valor correcto y que el campo `password` se reemplaza antes de enviar.
- [ ] Ejecutar `php artisan test --filter=NameOfTest` para pruebas relacionadas.

Notas de debugging rápidas
-------------------------
- Si ves "Page expired" al llegar desde MikroTik: es CSRF. Excluir `/hotspot` del middleware CSRF o aceptar POST sin token.
- Si el formulario no se envía automáticamente: abrir DevTools, ver logs `Countdown:` y comprobar que `document.forms.login` existe.
- Si vars del router aparecen como `$(link-login-only)` literalmente: significa que MikroTik no sustituyó las variables; revisar configuración de MikroTik y subir login.html correctamente.

Contacto / Seguimiento
----------------------
Si falta algo o prefieres una versión más orientada al equipo operativo (con scripts, comandos de export del login.html y checklist exacto de configuración MikroTik), dime y lo agrego.
