<?php

declare(strict_types=1);

use App\Http\Controllers\Reportes\MovimientoInformacion;
use Illuminate\Support\Facades\Route;

$prefix = 'reportes';

// Rutas API para Areas


Route::prefix($prefix.'/movimiento_informacion')->group(function (): void {
    Route::get('/', [MovimientoInformacion::class, 'index']);
});