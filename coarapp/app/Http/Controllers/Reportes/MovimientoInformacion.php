<?php

declare(strict_types=1);

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Reportes\MovimientoInformacionRequest;
use App\Models\Centinela\CentinelaModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Ejemplo de controlador de Asistencia con validación de permisos
 *
 * Este controlador muestra cómo aplicar la validación de permisos
 * siguiendo el mismo patrón que el proyecto CodeIgniter original
 */
final class MovimientoInformacion extends BaseController
{
    public function index(MovimientoInformacionRequest $request): JsonResponse {
      
        $data =  $request->validated();
        
       $response  = CentinelaModel::query()
       ->select([
        'fecha', 'modulo', 'menu', 'accion', 'descripcion'
       ])
       ->addSelect('usuario.nombre as usuario')
     ->leftJoin('usuario', 'usuario.id', '=', 'centinela.id_usuario')

     ->whereBetween('fecha', [
        $data['fecha_inicio'],
        $data['fecha_fin']
    ])
        ->orderByDesc('fecha')
            ->get();


        return $this->successResponse(
            message: 'Usuarios obtenidos',
            data: $response
        );
    }
}