<?php

declare(strict_types=1);

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Configuracion\LugarStoreRequest;
use App\Http\Requests\Configuracion\LugarUpdateRequest;
use App\Models\Configuracion\LugarModel;
use App\Services\CentinelaService;
use App\Support\AppContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class LugarController extends BaseController
{
    private const string MODULO = 'CONFIGURACIÓN';

    private const string MENU = 'LUGAR';

    public function __construct(
        private readonly CentinelaService $centinelaService,
    ) {
        
    }

    /**
     * Flujos del CRUD de lugares:
     * - list(): Provee los registros para Tabulator filtrando por empresa.
     * - store(): Inserta un nuevo lugar y registra la acción en centinela.
     * - update(): Actualiza un lugar existente y audita el movimiento.
     * - destroy(): Marca como inactivo y registra la eliminación.
     */
    public function index(Request $request): JsonResponse
    {
        $this->validarPermisos('configuracion-lugar', 'view');

        $lugares = LugarModel::query()
            ->select(['id', 'nombre', 'referencia', 'estado'])
            ->where('id_empresa', AppContext::ID_EMPRESA())
            ->activos()
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $lugares,
        ]);
    }

    public function store(LugarStoreRequest $request): JsonResponse
    {
        $this->validarPermisos('configuracion-lugar', 'new');

        $payload = $request->validated();

        $lugar = DB::transaction(function () use ($payload): LugarModel {
            /** @var LugarModel $lugar */
            $lugar = LugarModel::query()->create([
                'nombre' => $payload['nombre'],
                'referencia' => $payload['referencia'] ?? null,
                'estado' => 1,
                'id_usuario' => AppContext::ID_USUARIO(),
                'id_empresa' => AppContext::ID_EMPRESA(),
            ]);

            return $lugar;
        });

        $this->centinelaService->registrarCambio(
            accion: 'NUEVO',
            descripcion: $lugar->nombre,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Lugar registrado correctamente.',
            'data' => $lugar->fresh(),
        ], 201);
    }

    public function update(LugarUpdateRequest $request, LugarModel $lugar): JsonResponse
    {
        $this->validarPermisos('configuracion-lugar', 'edit');

        $payload = $request->validated();

        DB::transaction(static function () use ($lugar, $payload): void {
            $lugar->update([
                'nombre' => $payload['nombre'],
                'referencia' => $payload['referencia'] ?? null,
            ]);
        });

        $this->centinelaService->registrarCambio(
            accion: 'EDITAR',
            descripcion: $lugar->fresh()->nombre,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Lugar actualizado correctamente.',
            'data' => $lugar->fresh(),
        ]);
    }

    public function destroy(LugarModel $lugar): JsonResponse
    {
        $this->validarPermisos('configuracion-lugar', 'delete');

        DB::transaction(static function () use ($lugar): void {
            $lugar->update(['estado' => 0]);
        });

        $this->centinelaService->registrarCambio(
            accion: 'ELIMINAR',
            descripcion: $lugar->nombre,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Lugar eliminado correctamente.',
        ]);
    }
}
