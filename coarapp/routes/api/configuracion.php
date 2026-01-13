<?php

declare(strict_types=1);

use App\Http\Controllers\Configuracion\AreaController;
use App\Http\Controllers\Configuracion\EmpresaController;
use App\Http\Controllers\Configuracion\EstadoMonitoreoController;
use App\Http\Controllers\Configuracion\EstudianteController;
use App\Http\Controllers\Configuracion\LugarController;
use App\Http\Controllers\Configuracion\PersonalController;
use App\Http\Controllers\Configuracion\RolController;
use App\Http\Controllers\Configuracion\SelectController;
use App\Http\Controllers\Configuracion\TipoPersonalController;
use App\Http\Controllers\Configuracion\TiposIncidenciaController;
use App\Http\Controllers\Configuracion\UsuarioController;
use Illuminate\Support\Facades\Route;

// Rutas API para Areas
Route::prefix('configuracion/areas')->group(function (): void {
    Route::get('/', [AreaController::class, 'index']);
    Route::post('/', [AreaController::class, 'store']);
    Route::post('/{area}', [AreaController::class, 'update']);
    Route::delete('/{area}', [AreaController::class, 'destroy']);
});

// Rutas API para Lugares
Route::prefix('configuracion/lugares')->group(function (): void {
    Route::get('/', [LugarController::class, 'index']);
    Route::post('/', [LugarController::class, 'store']);
    Route::post('/{lugar}', [LugarController::class, 'update']);
    Route::delete('/{lugar}', [LugarController::class, 'destroy']);
});

// Rutas API para Tipos de Incidencia
Route::prefix('configuracion/tipos-incidencia')->group(function (): void {
    Route::get('/', [TiposIncidenciaController::class, 'index']);
    Route::post('/', [TiposIncidenciaController::class, 'store']);
    Route::post('/{tipoIncidencia}', [TiposIncidenciaController::class, 'update']);
    Route::delete('/{tipoIncidencia}', [TiposIncidenciaController::class, 'destroy']);
});

// Rutas API para Estado de Monitoreo
Route::prefix('configuracion/estado-monitoreo')->group(function (): void {
    Route::get('/', [EstadoMonitoreoController::class, 'index']);
    Route::post('/', [EstadoMonitoreoController::class, 'store']);
    Route::post('/{estadoMonitoreo}', [EstadoMonitoreoController::class, 'update']);
    Route::delete('/{estadoMonitoreo}', [EstadoMonitoreoController::class, 'destroy']);
});

// Rutas API para Tipo de Personal
Route::prefix('configuracion/tipo-personal')->group(function (): void {
    Route::get('/', [TipoPersonalController::class, 'index']);
    Route::post('/', [TipoPersonalController::class, 'store']);
    Route::post('/{tipoPersonal}', [TipoPersonalController::class, 'update']);
    Route::delete('/{tipoPersonal}', [TipoPersonalController::class, 'destroy']);
});

// Rutas API para Personal
Route::prefix('configuracion/personal')->group(function (): void {
    Route::get('/', [PersonalController::class, 'index']);
    Route::post('/', [PersonalController::class, 'store']);
    Route::post('/{personal}', [PersonalController::class, 'update']);
    Route::delete('/{personal}', [PersonalController::class, 'destroy']);
});

// Rutas API para Roles y Permisos
Route::prefix('configuracion/rol')->group(function (): void {
    Route::get('/', [RolController::class, 'index']);
    Route::get('/{rol}', [RolController::class, 'show']);
    Route::post('/', [RolController::class, 'store']);
    Route::post('/{rol}', [RolController::class, 'update']);
    Route::delete('/{rol}', [RolController::class, 'destroy']);
});

// Rutas API para Empresa
Route::prefix('configuracion/empresa')->group(function (): void {
    Route::get('/', [EmpresaController::class, 'show']);
    Route::post('/', [EmpresaController::class, 'update']);
});

// Rutas API para Usuarios
Route::prefix('configuracion/usuario')->group(function (): void {
    Route::get('/', [UsuarioController::class, 'index']);
    Route::post('/', [UsuarioController::class, 'store']);
    Route::post('/{usuario}', [UsuarioController::class, 'update']);
    Route::delete('/{usuario}', [UsuarioController::class, 'destroy']);
    Route::post('/{usuario}/change-password', [UsuarioController::class, 'changePassword']);
    Route::post('/{usuario}/suspend', [UsuarioController::class, 'suspend']);
    Route::post('/{usuario}/activate', [UsuarioController::class, 'activate']);
});

// Rutas API para Estudiantes
Route::prefix('configuracion/estudiante')->group(function (): void {
    Route::get('/', [EstudianteController::class, 'index']);
    Route::get('/editar/{estudiante}', [EstudianteController::class, 'show']);
    Route::post('/save', [EstudianteController::class, 'save']);
    Route::post('/importar', [EstudianteController::class, 'importar']);
    Route::delete('/{estudiante}', [EstudianteController::class, 'destroy']);
});

// Rutas API para selects/dropdowns centralizados
Route::prefix('configuracion/selects')->group(function (): void {
    
    Route::get('/tipos-personal', [SelectController::class, 'tiposPersonal']);
    Route::get('/areas', [SelectController::class, 'areas']);
    Route::get('/lugares', [SelectController::class, 'lugares']);
    Route::get('/tipos-incidencia', [SelectController::class, 'tiposIncidencia']);
    Route::get('/estados-monitoreo', [SelectController::class, 'estadosMonitoreo']);
    Route::get('/tipos-documento', [SelectController::class, 'tiposDocumento']);
    Route::get('/proveedores', [SelectController::class, 'proveedores']);
    Route::get('/ubigeos', [SelectController::class, 'ubigeos']);
    Route::get('/personal', [SelectController::class, 'personal']);
    Route::get('/roles', [SelectController::class, 'roles']);
});
