<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Configuracion\PermisoModel;
use App\Models\User;
use App\Support\AppContext;
use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

final class PermisoService
{
    /**
     * Valida si el usuario tiene permisos para realizar una acción específica en un menú
     *
     * @param  string  $menu  Nombre del menú (ej: 'operacion-incidencias')
     * @param  string  $accion  Acción a validar: 'new', 'delete', 'edit', 'view'
     * @param  User|null  $user  Usuario a validar (si es null, usa el usuario del contexto)
     *
     * @throws HttpResponseException Si no tiene permisos
     */
    public function validarPermisos(string $menu, string $accion, ?User $user = null): void
    {
        $user ??= AppContext::user();

        if (! $user instanceof User) {
            $this->denegarAcceso($menu, $accion);
        }

        // Si es SUPER ADMINISTRADOR o SUPER USUARIO, tiene todos los permisos
        $tipoUsuario = $user->tipo ?? $user->tipo_persona ?? null;

        if (in_array($tipoUsuario, ['SUPER ADMINISTRADOR', 'SUPER USUARIO'], true)) {
            return;
        }

        // Validar permiso en la base de datos
        $permiso = PermisoModel::query()->where('menu', $menu)
            ->where($accion, 1)
            ->where('id_rol', $user->id_rol)
            ->first();

        if (! $permiso) {
            $this->denegarAcceso($menu, $accion);
        }
    }

    /**
     * Verifica si el usuario tiene un permiso específico (retorna boolean)
     *
     * @param  string  $menu  Nombre del menú
     * @param  string  $accion  Acción a validar
     * @param  User|null  $user  Usuario a validar
     */
    public function tienePermiso(string $menu, string $accion, ?User $user = null): bool
    {
        try {
            $this->validarPermisos($menu, $accion, $user);

            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Deniega el acceso y lanza una excepción HTTP con formato JSON compatible
     *
     * @param  string  $menu  Nombre del menú
     * @param  string  $accion  Acción que se intentó realizar
     */
    private function denegarAcceso(string $menu, string $accion): never
    {
        $acciones = [
            'new' => 'NUEVO',
            'delete' => 'ELIMINAR',
            'edit' => 'EDITAR',
            'view' => 'VER',
        ];

        $accionTexto = $acciones[$accion] ?? mb_strtoupper($accion);
        $mensaje = "No tienes permisos suficientes para <strong>{$menu}</strong> {$accionTexto}";

        // Usar abort con formato JSON compatible con el código original
        abort(response()->json([
            'tipo' => 'warning',
            'mensaje' => $mensaje,
        ], Response::HTTP_UNAUTHORIZED));
    }
}
