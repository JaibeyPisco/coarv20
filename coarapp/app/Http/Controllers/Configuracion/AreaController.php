<?php

declare(strict_types=1);

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Configuracion\AreaStoreRequest;
use App\Http\Requests\Configuracion\AreaUpdateRequest;
use App\Models\Configuracion\AreaModel;
use App\Services\CentinelaService;
use App\Support\AppContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class AreaController extends BaseController
{
    private const string MODULO = 'CONFIGURACIÓN';

    private const string MENU = 'ÁREA';

    public function __construct(
        private readonly CentinelaService $centinelaService,
    ) {}

    /**
     * Obtener lista de áreas para la tabla
     */
    public function index(Request $request): JsonResponse
    {
        $this->validarPermisos('configuracion-area', 'view');

        $areas = AreaModel::query()
            ->select(['id', 'nombre', 'descripcion', 'estado'])
            ->where('id_empresa', AppContext::ID_EMPRESA())
            ->activos()
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $areas,
        ]);
    }

    /**
     * Crear nueva área
     */
    public function store(AreaStoreRequest $request): JsonResponse
    {
        $this->validarPermisos('configuracion-area', 'new');

        $payload = $request->validated();

        $area = DB::transaction(function () use ($payload): AreaModel {
            /** @var AreaModel $area */
            $area = AreaModel::query()->create([
                'nombre' => $payload['nombre'],
                'descripcion' => $payload['descripcion'] ?? null,
                'estado' => 1,
                'id_usuario' => AppContext::ID_USUARIO(),
                'id_empresa' => AppContext::ID_EMPRESA(),
            ]);

            return $area;
        });

        $this->centinelaService->registrarCambio(
            accion: 'NUEVO',
            descripcion: $area->nombre,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Área registrada correctamente.',
            'data' => $area->fresh(),
        ], 201);
    }

    /**
     * Actualizar área existente
     */
    public function update(AreaUpdateRequest $request, AreaModel $area): JsonResponse
    {
        $this->validarPermisos('configuracion-area', 'edit');

        $payload = $request->validated();

        DB::transaction(static function () use ($area, $payload): void {
            $area->update([
                'nombre' => $payload['nombre'],
                'descripcion' => $payload['descripcion'] ?? null,
            ]);
        });

        $this->centinelaService->registrarCambio(
            accion: 'EDITAR',
            descripcion: $area->fresh()->nombre,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Área actualizada correctamente.',
            'data' => $area->fresh(),
        ]);
    }

    /**
     * Eliminar área (soft delete)
     */
    public function destroy(AreaModel $area): JsonResponse
    {
        $this->validarPermisos('configuracion-area', 'delete');

        DB::transaction(static function () use ($area): void {
            $area->update(['estado' => 0]);
        });

        $this->centinelaService->registrarCambio(
            accion: 'ELIMINAR',
            descripcion: $area->nombre,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Área eliminada correctamente.',
        ]);
    }
}
