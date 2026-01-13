<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operacion;

use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Ejemplo de controlador de Asistencia con validación de permisos
 *
 * Este controlador muestra cómo aplicar la validación de permisos
 * siguiendo el mismo patrón que el proyecto CodeIgniter original
 */
final class AsistenciaController extends BaseController
{
    /**
     * Guardar nueva asistencia escolar
     *
     * Ejemplo de uso: $this->validarPermisos('operacion-nueva_asistencia-escolar', 'new');
     */
    public function store(Request $request): JsonResponse
    {
        // Validar permiso antes de procesar la petición
        $this->validarPermisos('operacion-nueva_asistencia-escolar', 'new');

        // Aquí iría la lógica para guardar la asistencia
        // ...

        return response()->json([
            'tipo' => 'success',
            'mensaje' => 'Asistencia guardada correctamente',
        ], 200);
    }
}
