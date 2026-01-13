<?php

declare(strict_types=1);

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Configuracion\PersonalStoreRequest;
use App\Http\Requests\Configuracion\PersonalUpdateRequest;
use App\Models\Configuracion\PersonalModel;
use App\Services\CentinelaService;
use App\Services\ErrorHandlerService;
use App\Services\ImageUploadService;
use App\Support\AppContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

final class PersonalController extends BaseController
{
    private const string MODULO = 'CONFIGURACIÓN';

    private const string MENU = 'PERSONAL';

    public function __construct(
        private readonly CentinelaService $centinelaService,
    ) {}

    /**
     * Flujos del CRUD de personal:
     * - list(): Provee los registros para Tabulator filtrando por empresa.
     * - store(): Inserta un nuevo personal y registra la acción en centinela.
     * - update(): Actualiza un personal existente y audita el movimiento.
     * - destroy(): Marca como inactivo y registra la eliminación.
     */
    public function index(Request $request): JsonResponse
    {
        $this->validarPermisos('configuracion-personal', 'view');

        try {
            $personal = PersonalModel::query()
                ->select([
                    'personal.id',
                    'personal.numero_documento',
                    'personal.nombre',
                    'personal.apellido',
                    'personal.direccion',
                    'personal.tipo_contratacion',
                    'personal.estado',
                    'personal.imagen',
                    'personal.firma',
                    'personal.id_tipo_personal',
                    'personal.id_tipo_documento',
                    'personal.id_proveedor',
                    'personal.ubigeo',
                    'personal.comentario',
                ])
                ->selectRaw("COALESCE(tipo_personal.nombre, '') as nombre_tipo_personal")
                ->selectRaw("COALESCE(static_documento.nombre, '') as nombre_documento")
                ->selectRaw("COALESCE(proveedor.razon_social, '') as proveedor")
                ->selectRaw("COALESCE(CONCAT(static_ubigeo.distrito, ' - ', static_ubigeo.provincia, ' - ', static_ubigeo.departamento), '') as ubigeo_text")
                ->leftJoin('tipo_personal', 'tipo_personal.id', '=', 'personal.id_tipo_personal')
                ->leftJoin('static_documento', 'static_documento.id', '=', 'personal.id_tipo_documento')
                ->leftJoin('proveedor', 'proveedor.id', '=', 'personal.id_proveedor')
                ->leftJoin('static_ubigeo', 'static_ubigeo.id', '=', 'personal.ubigeo')
                ->where('personal.id_empresa', AppContext::ID_EMPRESA())
                ->orderByDesc('personal.id')
                ->get();

            return response()->json([
                'data' => $personal,
            ]);

        } catch (Throwable $e) {
            return ErrorHandlerService::handleCrudError($e, 'obtener', 'personal');
        }
    }

    public function store(PersonalStoreRequest $request): JsonResponse
    {
        $this->validarPermisos('configuracion-personal', 'new');

        $payload = $request->validated();

        $personal = DB::transaction(function () use ($payload, $request): PersonalModel {
            // Manejar subida de imágenes
            $imagen = ImageUploadService::upload($request->file('imagen'), 'personales');
            $firma = ImageUploadService::upload($request->file('imagen_firma'), 'personales');

            /** @var PersonalModel $personal */
            $personal = PersonalModel::query()->create([
                'id_tipo_personal' => (int) $payload['id_tipo_personal'],
                'id_tipo_documento' => (int) $payload['id_tipo_documento'],
                'numero_documento' => $payload['numero_documento'],
                'nombre' => $payload['nombre'],
                'apellido' => $payload['apellido'],
                'tipo_contratacion' => $payload['tipo_contratacion'],
                'direccion' => $payload['direccion'] ?? null,
                'ubigeo' => $payload['ubigeo'] ?? null,
                'comentario' => $payload['comentario'] ?? null,
                'id_proveedor' => ($payload['tipo_contratacion'] === 'TERCERO' && isset($payload['id_proveedor'])) ? (int) $payload['id_proveedor'] : null,
                'imagen' => $imagen,
                'firma' => $firma,
                'estado' => 1,
                'id_empresa' => AppContext::ID_EMPRESA(),
            ]);

            return $personal;
        });

        $this->centinelaService->registrarCambio(
            accion: 'NUEVO',
            descripcion: $personal->nombre.' '.$personal->apellido,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Personal registrado correctamente.',
            'data' => $personal->fresh(),
        ], 201);
    }

    public function update(PersonalUpdateRequest $request, PersonalModel $personal): JsonResponse
    {
        $this->validarPermisos('configuracion-personal', 'edit');

        $payload = $request->validated();

        DB::transaction(function () use ($personal, $payload, $request): void {
            // Manejar subida de imágenes (mantener las anteriores si no se sube nueva)
            $imagen = ImageUploadService::upload(
                $request->file('imagen'),
                'personales',
                $personal->imagen
            );
            $firma = ImageUploadService::upload(
                $request->file('imagen_firma'),
                'personales',
                $personal->firma
            );

            $updateData = [
                'id_tipo_personal' => (int) $payload['id_tipo_personal'],
                'id_tipo_documento' => (int) $payload['id_tipo_documento'],
                'numero_documento' => $payload['numero_documento'],
                'nombre' => $payload['nombre'],
                'apellido' => $payload['apellido'],
                'tipo_contratacion' => $payload['tipo_contratacion'],
                'direccion' => $payload['direccion'] ?? null,
                'ubigeo' => $payload['ubigeo'] ?? null,
                'comentario' => $payload['comentario'] ?? null,
                'id_proveedor' => ($payload['tipo_contratacion'] === 'TERCERO' && isset($payload['id_proveedor'])) ? (int) $payload['id_proveedor'] : null,
            ];

            if ($imagen !== null) {
                $updateData['imagen'] = $imagen;
            }

            if ($firma !== null) {
                $updateData['firma'] = $firma;
            }

            $personal->update($updateData);
        });

        $this->centinelaService->registrarCambio(
            accion: 'EDITAR',
            descripcion: $personal->fresh()->nombre.' '.$personal->fresh()->apellido,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Personal actualizado correctamente.',
            'data' => $personal->fresh(),
        ]);
    }

    public function destroy(PersonalModel $personal): JsonResponse
    {
        $this->validarPermisos('configuracion-personal', 'delete');

        DB::transaction(static function () use ($personal): void {
            $personal->update(['estado' => 0]);
        });

        $this->centinelaService->registrarCambio(
            accion: 'ELIMINAR',
            descripcion: $personal->nombre.' '.$personal->apellido,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Personal eliminado correctamente.',
        ]);
    }
}
