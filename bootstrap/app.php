<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Excluir rutas del hotspot de verificación CSRF
        // MikroTik no puede enviar tokens CSRF en sus formularios
        $middleware->validateCsrfTokens(except: [
            'hotspot', // POST desde login.html de MikroTik
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
