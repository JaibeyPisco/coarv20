<?php

declare(strict_types=1);

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Configuracion\TiposIncidenciaStoreRequest;
use App\Http\Requests\Configuracion\TiposIncidenciaUpdateRequest;
use App\Models\Configuracion\TiposIncidenciaModel;
use App\Services\CentinelaService;
use App\Support\AppContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class TiposIncidenciaController extends BaseController
{
    private const string MODULO = 'CONFIGURACIÓN';

    private const string MENU = 'TIPO INCIDENCIA';

    public function __construct(
        private readonly CentinelaService $centinelaService,
    ) {}

    /**
     * Flujos del CRUD de tipos de incidencias:
     * - list(): Provee los registros para Tabulator filtrando por empresa.
     * - store(): Inserta un nuevo tipo de incidencia y registra la acción en centinela.
     * - update(): Actualiza un tipo de incidencia existente y audita el movimiento.
     * - destroy(): Marca como inactivo y registra la eliminación.
     */
    public function index(Request $request): JsonResponse
    {
        $this->validarPermisos('configuracion-tipos_incidencia', 'view');

        $tiposIncidencia = TiposIncidenciaModel::query()
            ->select(['id', 'nombre', 'nivel_incidencia', 'nivel_severidad', 'derivacion_inmediata', 'estado'])
            ->where('id_empresa', AppContext::ID_EMPRESA())
            ->activos()
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $tiposIncidencia,
        ]);
    }

    public function store(TiposIncidenciaStoreRequest $request): JsonResponse
    {
        $this->validarPermisos('configuracion-tipos_incidencia', 'new');

        $payload = $request->validated();

        $tipoIncidencia = DB::transaction(function () use ($payload): TiposIncidenciaModel {
            /** @var TiposIncidenciaModel $tipoIncidencia */
            $tipoIncidencia = TiposIncidenciaModel::query()->create([
                'nombre' => $payload['nombre'],
                'nivel_incidencia' => $payload['nivel_incidencia'],
                'nivel_severidad' => $payload['nivel_severidad'],
                'derivacion_inmediata' => $payload['derivacion_inmediata'],
                'estado' => 1,
                'id_usuario' => AppContext::ID_USUARIO(),
                'id_empresa' => AppContext::ID_EMPRESA(),
            ]);

            return $tipoIncidencia;
        });

        $this->centinelaService->registrarCambio(
            accion: 'NUEVO',
            descripcion: $tipoIncidencia->nombre,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Tipo de incidencia registrado correctamente.',
            'data' => $tipoIncidencia->fresh(),
        ], 201);
    }

    public function update(TiposIncidenciaUpdateRequest $request, TiposIncidenciaModel $tipoIncidencia): JsonResponse
    {
        $this->validarPermisos('configuracion-tipos_incidencia', 'edit');

        $payload = $request->validated();

        DB::transaction(static function () use ($tipoIncidencia, $payload): void {
            $tipoIncidencia->update([
                'nombre' => $payload['nombre'],
                'nivel_incidencia' => $payload['nivel_incidencia'],
                'nivel_severidad' => $payload['nivel_severidad'],
                'derivacion_inmediata' => $payload['derivacion_inmediata'],
            ]);
        });

        $this->centinelaService->registrarCambio(
            accion: 'EDITAR',
            descripcion: $tipoIncidencia->fresh()->nombre,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Tipo de incidencia actualizado correctamente.',
            'data' => $tipoIncidencia->fresh(),
        ]);
    }

    public function destroy(TiposIncidenciaModel $tipoIncidencia): JsonResponse
    {
        $this->validarPermisos('configuracion-tipos_incidencia', 'delete');

        DB::transaction(static function () use ($tipoIncidencia): void {
            $tipoIncidencia->update(['estado' => 0]);
        });

        $this->centinelaService->registrarCambio(
            accion: 'ELIMINAR',
            descripcion: $tipoIncidencia->nombre,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Tipo de incidencia eliminado correctamente.',
        ]);
    }
}
