<?php

declare(strict_types=1);

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Configuracion\EmpresaUpdateRequest;
use App\Models\Configuracion\EmpresaModel;
use App\Services\CentinelaService;
use App\Services\ErrorHandlerService;
use App\Services\ImageUploadService;
use App\Support\AppContext;
use Illuminate\Http\JsonResponse;
use Throwable;

final class EmpresaController extends BaseController
{
    private const string MODULO = 'CONFIGURACIÓN';

    private const string MENU = 'EMPRESA';

    public function __construct(
        private readonly CentinelaService $centinelaService,
    ) {}

    public function show(): JsonResponse
    {
        try {
            $empresa = EmpresaModel::query()
                ->where('id', AppContext::ID_EMPRESA())
                ->firstOrFail();

            return response()->json([
                'data' => [
                    'id' => $empresa->id,
                    'numero_documento' => $empresa->numero_documento,
                    'razon_social' => $empresa->razon_social,
                    'nombre_comercial' => $empresa->nombre_comercial,
                    'direccion' => $empresa->direccion,
                    'telefono' => $empresa->telefono,
                    'email' => $empresa->email,
                    'logo' => $empresa->logo,
                    'logo_factura' => $empresa->logo_factura,
                    'logo_url' => ImageUploadService::url($empresa->logo, 'empresas'),
                    'logo_factura_url' => ImageUploadService::url($empresa->logo_factura, 'empresas'),
                ],
            ]);
        } catch (Throwable $e) {
            return ErrorHandlerService::handleCrudError($e, 'obtener', 'empresa');
        }
    }

    public function update(EmpresaUpdateRequest $request): JsonResponse
    {
        $this->validarPermisos('configuracion-empresa', 'edit');

        $payload = $request->validated();

        try {
            $empresa = EmpresaModel::query()
                ->where('id', AppContext::ID_EMPRESA())
                ->firstOrFail();

            // Manejar logo
            $logoFilename = ImageUploadService::upload(
                $request->file('logo'),
                'empresas',
                $payload['logo_anterior'] ?? $empresa->logo
            );

            // Manejar logo_factura
            $logoFacturaFilename = ImageUploadService::upload(
                $request->file('logo_factura'),
                'empresas',
                $payload['logo_factura_anterior'] ?? $empresa->logo_factura
            );

            // Actualizar empresa
            $empresa->update([
                'numero_documento' => $payload['numero_documento'],
                'razon_social' => $payload['razon_social'],
                'nombre_comercial' => $payload['nombre_comercial'],
                'direccion' => $payload['direccion'],
                'telefono' => $payload['telefono'],
                'email' => $payload['email'],
                'logo' => $logoFilename ?? $empresa->logo,
                'logo_factura' => $logoFacturaFilename ?? $empresa->logo_factura,
            ]);

            $this->centinelaService->registrarCambio(
                accion: 'EDITAR',
                descripcion: $empresa->nombre_comercial,
                menu: self::MENU,
                modulo: self::MODULO,
            );

            return response()->json([
                'message' => 'Empresa actualizada correctamente.',
                'data' => [
                    'id' => $empresa->id,
                    'numero_documento' => $empresa->numero_documento,
                    'razon_social' => $empresa->razon_social,
                    'nombre_comercial' => $empresa->nombre_comercial,
                    'direccion' => $empresa->direccion,
                    'telefono' => $empresa->telefono,
                    'email' => $empresa->email,
                    'logo' => $empresa->logo,
                    'logo_factura' => $empresa->logo_factura,
                    'logo_url' => ImageUploadService::url($empresa->logo, 'empresas'),
                    'logo_factura_url' => ImageUploadService::url($empresa->logo_factura, 'empresas'),
                ],
            ]);
        } catch (Throwable $e) {
            return ErrorHandlerService::handleCrudError($e, 'actualizar', 'empresa');
        }
    }
}
