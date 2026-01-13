<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

// Rutas públicas de autenticación (sin middleware auth)
require __DIR__ . "/api/auth.php";

// Todas las rutas API requieren autenticación con Sanctum
// NO usamos middleware 'web' - solo 'auth:sanctum' para APIs
Route::middleware(["auth:sanctum"])->group(function (): void {
    // Cargar rutas API por módulos
    require __DIR__ . "/api/configuracion.php";
    require __DIR__ . "/api/operacion.php";
    require __DIR__ . "/api/reportes.php";
});
