<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rutas públicas de autenticación API
// Usamos tokens de API de Sanctum (no sesiones, no CSRF)

// Login - ruta pública, sin autenticación
// Devuelve un token de API que el frontend debe guardar y enviar en cada petición
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

// Rutas protegidas - requieren autenticación con Sanctum usando tokens
Route::middleware(['auth:sanctum'])->group(function (): void {
    // Obtener usuario autenticado con información de empresa y permisos
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        $user->load('empresa');

        return response()->json([
            'id' => $user->id,
            'nombre' => $user->nombre,
            'apellido' => $user->apellido,
            'email' => $user->email,
            'usuario' => $user->usuario,
            'imagen' => $user->imagen,
            'id_empresa' => $user->id_empresa,
            'id_rol' => $user->id_rol,
            'tipo' => $user->tipo,
            'name' => $user->name,
            'initials' => $user->initials,
            'avatar_url' => $user->avatar_url,
            'permisos' => $user->permisos,
            'empresa' => $user->empresa ? [
                'id' => $user->empresa->id,
                'numero_documento' => $user->empresa->numero_documento,
                'razon_social' => $user->empresa->razon_social,
                'nombre_comercial' => $user->empresa->nombre_comercial,
                'direccion' => $user->empresa->direccion,
                'telefono' => $user->empresa->telefono,
                'email' => $user->empresa->email,
                'logo_url' => $user->empresa->logo ? '/storage/empresas/'.$user->empresa->logo : null,
            ] : null,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);
    });

    // Logout - revoca el token actual
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
});
