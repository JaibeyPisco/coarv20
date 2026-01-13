<?php

declare(strict_types=1);

use App\Http\Middleware\ShareContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            ShareContext::class,
        ]);

        // Agregar ShareContext también a las rutas API para que AppContext funcione
        $middleware->api(append: [
            ShareContext::class,
        ]);

        // Para API con tokens de Sanctum, no necesitamos EnsureFrontendRequestsAreStateful
        // Los tokens se envían en el header Authorization: Bearer {token}
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
