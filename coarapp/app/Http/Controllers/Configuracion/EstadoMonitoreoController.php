<?php

declare(strict_types=1);

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Configuracion\EstadoMonitoreoStoreRequest;
use App\Http\Requests\Configuracion\EstadoMonitoreoUpdateRequest;
use App\Models\Configuracion\EstadoMonitoreoModel;
use App\Services\CentinelaService;
use App\Support\AppContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class EstadoMonitoreoController extends BaseController
{
    private const string MODULO = 'CONFIGURACIÓN';

    private const string MENU = 'ESTADO MONITOREO';

    public function __construct(
        private readonly CentinelaService $centinelaService,
    ) {}

    /**
     * Flujos del CRUD de estados de monitoreo:
     * - list(): Provee los registros para Tabulator filtrando por empresa.
     * - store(): Inserta un nuevo estado de monitoreo y registra la acción en centinela.
     * - update(): Actualiza un estado de monitoreo existente y audita el movimiento.
     * - destroy(): Elimina el registro y registra la eliminación.
     */
    public function index(Request $request): JsonResponse
    {
        $this->validarPermisos('configuracion-estado_monitoreo', 'view');

        $estadosMonitoreo = EstadoMonitoreoModel::query()
            ->select(['id', 'nombre', 'tipo', 'color_bg', 'color_text'])
            ->where('id_empresa', AppContext::ID_EMPRESA())
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'data' => $estadosMonitoreo,
        ]);
    }

    public function store(EstadoMonitoreoStoreRequest $request): JsonResponse
    {
        $this->validarPermisos('configuracion-estado_monitoreo', 'new');

        $payload = $request->validated();

        $estadoMonitoreo = DB::transaction(function () use ($payload): EstadoMonitoreoModel {
            // Calcular color_text basado en si el color_bg es oscuro o claro
            $colorText = $this->calculateTextColor($payload['color_bg']);

            /** @var EstadoMonitoreoModel $estadoMonitoreo */
            $estadoMonitoreo = EstadoMonitoreoModel::query()->create([
                'nombre' => $payload['nombre'],
                'tipo' => $payload['tipo'],
                'color_bg' => $payload['color_bg'],
                'color_text' => $colorText,
                'id_empresa' => AppContext::ID_EMPRESA(),
            ]);

            return $estadoMonitoreo;
        });

        $this->centinelaService->registrarCambio(
            accion: 'NUEVO',
            descripcion: $estadoMonitoreo->nombre.', '.$estadoMonitoreo->tipo,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Estado de monitoreo registrado correctamente.',
            'data' => $estadoMonitoreo->fresh(),
        ], 201);
    }

    public function update(EstadoMonitoreoUpdateRequest $request, EstadoMonitoreoModel $estadoMonitoreo): JsonResponse
    {
        $this->validarPermisos('configuracion-estado_monitoreo', 'edit');

        $payload = $request->validated();

        DB::transaction(static function () use ($estadoMonitoreo, $payload): void {
            // Calcular color_text basado en si el color_bg es oscuro o claro
            $colorText = self::calculateTextColorStatic($payload['color_bg']);

            $estadoMonitoreo->update([
                'nombre' => $payload['nombre'],
                'tipo' => $payload['tipo'],
                'color_bg' => $payload['color_bg'],
                'color_text' => $colorText,
            ]);
        });

        $this->centinelaService->registrarCambio(
            accion: 'EDITAR',
            descripcion: $estadoMonitoreo->fresh()->nombre.', '.$estadoMonitoreo->fresh()->tipo,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Estado de monitoreo actualizado correctamente.',
            'data' => $estadoMonitoreo->fresh(),
        ]);
    }

    public function destroy(EstadoMonitoreoModel $estadoMonitoreo): JsonResponse
    {
        $this->validarPermisos('configuracion-estado_monitoreo', 'delete');

        $nombre = $estadoMonitoreo->nombre;
        $tipo = $estadoMonitoreo->tipo;

        DB::transaction(static function () use ($estadoMonitoreo): void {
            $estadoMonitoreo->delete();
        });

        $this->centinelaService->registrarCambio(
            accion: 'ELIMINAR',
            descripcion: $nombre.', '.$tipo,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Estado de monitoreo eliminado correctamente.',
        ]);
    }

    private static function calculateTextColorStatic(string $hexColor): string
    {
        // Remover el # si existe
        $hexColor = mb_ltrim($hexColor, '#');

        // Convertir a RGB
        $r = hexdec(mb_substr($hexColor, 0, 2));
        $g = hexdec(mb_substr($hexColor, 2, 2));
        $b = hexdec(mb_substr($hexColor, 4, 2));

        // Calcular el brillo relativo
        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        // Si el color es oscuro, usar texto blanco, si es claro usar texto negro
        return $brightness < 128 ? '#ffffff' : '#000000';
    }

    /**
     * Calcula el color del texto basado en el color de fondo (oscuro o claro)
     */
    private function calculateTextColor(string $hexColor): string
    {
        return self::calculateTextColorStatic($hexColor);
    }
}
