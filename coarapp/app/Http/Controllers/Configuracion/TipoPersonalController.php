<?php

declare(strict_types=1);

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Configuracion\TipoPersonalStoreRequest;
use App\Http\Requests\Configuracion\TipoPersonalUpdateRequest;
use App\Models\Configuracion\TipoPersonalModel;
use App\Services\CentinelaService;
use App\Support\AppContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class TipoPersonalController extends BaseController
{
    private const string MODULO = 'CONFIGURACIÓN';

    private const string MENU = 'TIPO PERSONAL';

    public function __construct(
        private readonly CentinelaService $centinelaService,
    ) {}

    /**
     * Flujos del CRUD de tipos de personal:
     * - list(): Provee los registros para Tabulator filtrando por empresa.
     * - store(): Inserta un nuevo tipo de personal y registra la acción en centinela.
     * - update(): Actualiza un tipo de personal existente y audita el movimiento.
     * - destroy(): Marca como inactivo y registra la eliminación.
     */
    public function index(Request $request): JsonResponse
    {
        $this->validarPermisos('configuracion-tipo_personal', 'view');

        $tiposPersonal = TipoPersonalModel::query()
            ->select(['id', 'nombre', 'descripcion', 'estado'])
            ->where('id_empresa', AppContext::ID_EMPRESA())
            ->activos()
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $tiposPersonal,
        ]);
    }

    public function store(TipoPersonalStoreRequest $request): JsonResponse
    {
        $this->validarPermisos('configuracion-tipo_personal', 'new');

        $payload = $request->validated();

        $tipoPersonal = DB::transaction(function () use ($payload): TipoPersonalModel {
            /** @var TipoPersonalModel $tipoPersonal */
            $tipoPersonal = TipoPersonalModel::query()->create([
                'nombre' => $payload['nombre'],
                'descripcion' => $payload['descripcion'] ?? null,
                'estado' => 1,
                'id_usuario' => AppContext::ID_USUARIO(),
                'id_empresa' => AppContext::ID_EMPRESA(),
            ]);

            return $tipoPersonal;
        });

        $this->centinelaService->registrarCambio(
            accion: 'NUEVO',
            descripcion: $tipoPersonal->nombre,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Tipo de personal registrado correctamente.',
            'data' => $tipoPersonal->fresh(),
        ], 201);
    }

    public function update(TipoPersonalUpdateRequest $request, TipoPersonalModel $tipoPersonal): JsonResponse
    {
        $this->validarPermisos('configuracion-tipo_personal', 'edit');

        $payload = $request->validated();

        DB::transaction(static function () use ($tipoPersonal, $payload): void {
            $tipoPersonal->update([
                'nombre' => $payload['nombre'],
                'descripcion' => $payload['descripcion'] ?? null,
            ]);
        });

        $this->centinelaService->registrarCambio(
            accion: 'EDITAR',
            descripcion: $tipoPersonal->fresh()->nombre,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Tipo de personal actualizado correctamente.',
            'data' => $tipoPersonal->fresh(),
        ]);
    }

    public function destroy(TipoPersonalModel $tipoPersonal): JsonResponse
    {
        $this->validarPermisos('configuracion-tipo_personal', 'delete');

        DB::transaction(static function () use ($tipoPersonal): void {
            $tipoPersonal->update(['estado' => 0]);
        });

        $this->centinelaService->registrarCambio(
            accion: 'ELIMINAR',
            descripcion: $tipoPersonal->nombre,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Tipo de personal eliminado correctamente.',
        ]);
    }
}
