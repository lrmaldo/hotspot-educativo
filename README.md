# Hotspot Educativo

Implementación inicial de trivia + generación de credenciales Mikrotik.

## Migraciones
Ejecutar:
```
php artisan migrate
```

## Variables de entorno (.env)
Agregar:
```
MIKROTIK_HOST=192.168.88.1
MIKROTIK_USER=admin
MIKROTIK_PASS=secret
MIKROTIK_PORT=8728
MIKROTIK_MINUTES_CORRECT=30
MIKROTIK_MINUTES_INCORRECT=5
```

Instalar librería API RouterOS (ejemplo):
```
composer require evilfreelancer/routeros-api-php
```

## Uso
Visitar `/hotspot` desde la red del hotspot. Mikrotik debe redirigir incluyendo parámetros `mac`, `gw` e `ip` si se desea identificación más robusta. Al responder se generan credenciales temporales.

## Pendientes / Mejoras
- Validar MAC address real (usar parámetro `mac` que Mikrotik puede anexar a la URL o cabeceras).
- Añadir rotación diaria de trivias y seeding.
- Manejar errores de conexión a Mikrotik con mensajes amigables.
- Limitar intentos abusivos por IP con RateLimiter.
