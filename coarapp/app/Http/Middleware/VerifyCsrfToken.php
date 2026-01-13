<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken as Middleware;

final class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Las rutas API de Sanctum manejan el CSRF automáticamente
        // El middleware EnsureFrontendRequestsAreStateful se encarga de esto
    ];
}
