<?php

declare(strict_types=1);

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\BaseController;
use App\Models\Configuracion\AreaModel;
use App\Models\Configuracion\EstadoMonitoreoModel;
use App\Models\Configuracion\LugarModel;
use App\Models\Configuracion\PersonalModel;
use App\Models\Configuracion\ProveedorModel;
use App\Models\Configuracion\RolModel;
use App\Models\Configuracion\TipoPersonalModel;
use App\Models\Configuracion\TiposIncidenciaModel;
use App\Models\Static\StaticDocumentoModel;
use App\Models\Static\StaticUbigeoModel;
use App\Support\AppContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final  class SelectController extends BaseController
{
    /**
     * Obtiene tipos de personal activos para dropdown
     */
    public function tiposPersonal(): JsonResponse
    {
        $data = TipoPersonalModel::query()
            ->select(['id', 'nombre as text'])
            ->where('id_empresa', AppContext::ID_EMPRESA())
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return response()->json($data);
    }

    /**
     * Obtiene áreas activas para dropdown
     */
    public function areas(): JsonResponse
    {
        $data = AreaModel::query()
            ->select(['id', 'nombre as text'])
            ->where('id_empresa', AppContext::ID_EMPRESA())
            ->where('estado', '!=', 0)
            ->orderBy('nombre')
            ->get();

        return response()->json($data);
    }

    /**
     * Obtiene lugares activos para dropdown
     */
    public function lugares(): JsonResponse
    {
        $data = LugarModel::query()
            ->select(['id', 'nombre as text'])
            ->where('id_empresa', AppContext::ID_EMPRESA())
            ->where('estado', '!=', 0)
            ->orderBy('nombre')
            ->get();

        return response()->json($data);
    }

    /**
     * Obtiene tipos de incidencias activos para dropdown
     */
    public function tiposIncidencia(): JsonResponse
    {
        $data = TiposIncidenciaModel::query()
            ->select(['id', 'nombre as text'])
            ->where('id_empresa', AppContext::ID_EMPRESA())
            ->where('estado', 1)
            ->orderBy('nombre')
            ->get();

        return response()->json($data);
    }

    /**
     * Obtiene estados de monitoreo para dropdown
     */
    public function estadosMonitoreo(Request $request): JsonResponse
    {
        $tipo = $request->query('tipo');

        $query = EstadoMonitoreoModel::query()
            ->select(['id', 'nombre as text'])
            ->where('id_empresa', AppContext::ID_EMPRESA());

        if ($tipo !== null && $tipo !== 'TODOS') {
            $query->where('tipo', $tipo);
        }

        $data = $query->orderBy('nombre')->get();

        return response()->json($data);
    }

    /**
     * Obtiene tipos de documento para dropdown
     */
    public function tiposDocumento(): JsonResponse
    {
        $data = StaticDocumentoModel::query()
            ->select(['id', 'nombre as text'])
            ->orderBy('nombre')
            ->get();

        return response()->json($data);
    }

    /**
     * Obtiene proveedores activos para dropdown
     */
    public function proveedores(): JsonResponse
    {
        $data = ProveedorModel::query()
            ->select(['id', 'razon_social as text'])
            ->where('id_empresa', AppContext::ID_EMPRESA())
            ->activos()
            ->orderBy('razon_social')
            ->get();

        return response()->json($data);
    }

    /**
     * Obtiene ubigeos para dropdown
     */
    public function ubigeos(): JsonResponse
    {
        $data = StaticUbigeoModel::query()
            ->selectRaw("id, CONCAT(distrito, ' - ', provincia, ' - ', departamento) as text")
            ->orderBy('departamento')
            ->orderBy('provincia')
            ->orderBy('distrito')
            ->get();

        return response()->json($data);
    }

    /**
     * Obtiene personal activo para dropdown
     */
    public function personal(): JsonResponse
    {
        $data = PersonalModel::query()
            ->selectRaw("id, CONCAT(nombre, ' ', apellido) as text")
            ->where('id_empresa', AppContext::ID_EMPRESA())
            ->where('estado', 1)
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();

        return response()->json($data);
    }

    /**
     * Obtiene roles activos para dropdown
     */
    public function roles(): JsonResponse
    {
        $data = RolModel::query()
            ->select(['id', 'nombre as text'])
            ->where('id_empresa', AppContext::ID_EMPRESA())
            // ->where('estado', '!=', 0)
            ->orderBy('nombre')
            ->get();

        return response()->json($data);
    }
}
