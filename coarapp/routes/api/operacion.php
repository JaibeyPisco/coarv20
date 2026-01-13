<?php

declare(strict_types=1);

use App\Http\Controllers\Operacion\IncidenciaController;
use Illuminate\Support\Facades\Route;

$prefix = "operacion";

// Rutas API para Areas
 
Route::prefix($prefix . "/incidencia")->group(function (): void {
    Route::get("/getInitial", [IncidenciaController::class, "getInitial"]);
});
