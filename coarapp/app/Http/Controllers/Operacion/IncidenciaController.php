<?php

declare(strict_types=1);

namespace App\Http\Controllers\Operacion;

use App\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Operacion\IncidenciaModel;
use App\Models\Configuracion\TiposIncidenciaModel;
use App\Models\Configuracion\LugarModel;
use App\Models\Configuracion\AreaModel;

use App\Support\AppContext;

/**
 * Ejemplo de controlador de Operación con validación de permisos
 *
 * Este controlador muestra cómo aplicar la validación de permisos
 * siguiendo el mismo patrón que el proyecto CodeIgniter original
 */
final class IncidenciaController extends BaseController
{
    public function __construct(
        private IncidenciaModel $IncidenciaModel,
        private TiposIncidenciaModel $TiposIncidenciaModel,
        private LugarModel $LugarModel,
        private AreaModel $AreaModel,
    ) {}

    public function getInitial(): JsonResponse
    {
        $tipoIncidencias = $this->TiposIncidenciaModel
            ->select("id", "nombre as text")
            ->where("estado", 1)
            ->where("id_empresa", AppContext::ID_EMPRESA())
            ->get();

        $lugares = $this->LugarModel
            ->select("id", "nombre as text")
            ->where("estado", 1)
            ->where("id_empresa", AppContext::ID_EMPRESA())
            ->get();

        $areas = $this->AreaModel
            ->select("id", "nombre as text")
            ->where("estado", 1)
            ->where("id_empresa", AppContext::ID_EMPRESA())
            ->get();

        $response = [
            "secuencia" => \App\Services\SecuenciaService::getSecuenciaString(
                table: "incidencias",
            ),
            "tipoIncidencias" => $tipoIncidencias,
            "lugares" => $lugares,
            "areas" => $areas,
        ];

        return $this->successResponse(
            message: "Datos Iniciales",
            data: $response,
        );
    }
}
