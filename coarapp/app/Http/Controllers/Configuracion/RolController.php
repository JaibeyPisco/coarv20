<?php

declare(strict_types=1);

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Configuracion\RolStoreRequest;
use App\Http\Requests\Configuracion\RolUpdateRequest;
use App\Models\Configuracion\PermisoModel;
use App\Models\Configuracion\RolModel;
use App\Services\CentinelaService;
use App\Services\ErrorHandlerService;
use App\Support\AppContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

final class RolController extends BaseController
{
    private const string MODULO = 'CONFIGURACIÓN';

    private const string MENU = 'ROLES Y PERMISOS';

    public function __construct(
        private readonly CentinelaService $centinelaService,
    ) {}

    /**
     * Flujos del CRUD de roles:
     * - list(): Provee los registros para Tabulator filtrando por empresa.
     * - store(): Inserta un nuevo rol con sus permisos y registra la acción en centinela.
     * - update(): Actualiza un rol existente con sus permisos y audita el movimiento.
     * - destroy(): Marca como inactivo y registra la eliminación.
     */
    public function index(Request $request): JsonResponse
    {
        $this->validarPermisos('configuracion-rol', 'view');

        try {
            $roles = RolModel::query()
                ->select([
                    'rol.id',
                    'rol.nombre',
                    'rol.fl_no_dashboard',

                ])
                ->where('rol.id_empresa', AppContext::ID_EMPRESA())
                ->orderByDesc('rol.id')
                ->get()
                ->map(fn (RolModel $rol): array => [
                    'id' => $rol->id,
                    'nombre' => $rol->nombre,
                    'fl_no_dashboard' => (bool) $rol->fl_no_dashboard,

                ]);

            return response()->json([
                'data' => $roles,
            ]);
        } catch (Throwable $e) {
            return ErrorHandlerService::handleCrudError($e, 'obtener', 'rol');
        }
    }

    public function show(int $rol): JsonResponse
    {
        try {
            $rolModel = RolModel::query()
                ->where('id', $rol)
                ->where('id_empresa', AppContext::ID_EMPRESA())
                ->firstOrFail();

            $permisos = PermisoModel::query()
                ->where('id_rol', $rolModel->id)
                ->get()
                ->map(fn (PermisoModel $permiso): array => [
                    'menu' => $permiso->menu,
                    'view' => (bool) $permiso->view,
                    'new' => (bool) $permiso->new,
                    'edit' => (bool) $permiso->edit,
                    'delete' => (bool) $permiso->delete,
                ]);

            return response()->json([
                'data' => [
                    'id' => $rolModel->id,
                    'nombre' => $rolModel->nombre,
                    'fl_no_dashboard' => (bool) $rolModel->fl_no_dashboard,

                    'permisos' => $permisos,
                ],
            ]);
        } catch (Throwable $e) {
            return ErrorHandlerService::handleCrudError($e, 'obtener', 'rol');
        }
    }

    public function store(RolStoreRequest $request): JsonResponse
    {
        $this->validarPermisos('configuracion-rol', 'new');

        $payload = $request->validated();

        $rol = DB::transaction(function () use ($payload): RolModel {
            /** @var RolModel $rol */
            $rol = RolModel::query()->create([
                'nombre' => $payload['nombre'],
                'fl_no_dashboard' => $payload['fl_no_dashboard'] ?? false,
                'estado' => 1,
                'id_empresa' => AppContext::ID_EMPRESA(),
            ]);

            // Guardar permisos
            if (isset($payload['permisos']) && is_array($payload['permisos'])) {
                foreach ($payload['permisos'] as $permisoData) {
                    PermisoModel::query()->create([
                        'id_rol' => $rol->id,
                        'menu' => $permisoData['menu'],
                        'view' => $permisoData['view'] ?? false,
                        'new' => $permisoData['new'] ?? false,
                        'edit' => $permisoData['edit'] ?? false,
                        'delete' => $permisoData['delete'] ?? false,
                    ]);
                }
            }

            return $rol;
        });

        $this->centinelaService->registrarCambio(
            accion: 'NUEVO',
            descripcion: $rol->nombre,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Rol registrado correctamente.',
            'data' => $rol->fresh(['permisos']),
        ], 201);
    }

    public function update(RolUpdateRequest $request, RolModel $rol): JsonResponse
    {
        $this->validarPermisos('configuracion-rol', 'edit');

        $payload = $request->validated();

        DB::transaction(function () use ($rol, $payload): void {
            $rol->update([
                'nombre' => $payload['nombre'],
                'fl_no_dashboard' => $payload['fl_no_dashboard'] ?? false,
            ]);

            // Eliminar permisos existentes
            PermisoModel::query()->where('id_rol', $rol->id)->delete();

            // Guardar nuevos permisos
            if (isset($payload['permisos']) && is_array($payload['permisos'])) {
                foreach ($payload['permisos'] as $permisoData) {
                    PermisoModel::query()->create([
                        'id_rol' => $rol->id,
                        'menu' => $permisoData['menu'],
                        'view' => $permisoData['view'] ?? false,
                        'new' => $permisoData['new'] ?? false,
                        'edit' => $permisoData['edit'] ?? false,
                        'delete' => $permisoData['delete'] ?? false,
                    ]);
                }
            }
        });

        $this->centinelaService->registrarCambio(
            accion: 'EDITAR',
            descripcion: $rol->fresh()->nombre,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Rol actualizado correctamente.',
            'data' => $rol->fresh(['permisos']),
        ]);
    }

    public function destroy(RolModel $rol): JsonResponse
    {
        $this->validarPermisos('configuracion-rol', 'delete');

        DB::transaction(static function () use ($rol): void {
            $rol->update(['estado' => 0]);
        });

        $this->centinelaService->registrarCambio(
            accion: 'ELIMINAR',
            descripcion: $rol->nombre,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Rol eliminado correctamente.',
        ]);
    }
}
