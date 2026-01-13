<?php

declare(strict_types=1);

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Configuracion\UsuarioChangePasswordRequest;
use App\Http\Requests\Configuracion\UsuarioStoreRequest;
use App\Http\Requests\Configuracion\UsuarioUpdateRequest;
use App\Models\Configuracion\PersonalModel;
use App\Models\User;
use App\Services\CentinelaService;
use App\Services\ErrorHandlerService;
use App\Services\ImageUploadService;
use App\Support\AppContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class UsuarioController extends BaseController
{
    private const string MODULO = 'CONFIGURACIÓN';

    private const string MENU = 'USUARIOS';

    public function __construct(
        private readonly CentinelaService $centinelaService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->validarPermisos('configuracion-usuario', 'view');

        try {
            $usuarios = User::query()
                ->select([
                    'usuario.id',
                    'usuario.nombre',
                    'usuario.apellido',
                    'usuario.usuario',
                    'usuario.email',
                    'usuario.tipo_persona',
                    'usuario.id_rol',
                    'usuario.id_personal',
                    'usuario.id_estudiante',
                    'usuario.imagen',
                    'usuario.fl_ver_informacion_privada',
                    'usuario.fl_suspendido',
                ])
                ->selectRaw("COALESCE(rol.nombre, 'SUPER ADMINISTRADOR') as rol")
                ->selectRaw("COALESCE(CONCAT(personal.nombre, ' ', personal.apellido), '') as personal_nombre")
                ->selectRaw("'' as estudiante_nombre")
                ->leftJoin('rol', 'rol.id', '=', 'usuario.id_rol')
                ->leftJoin('personal', 'personal.id', '=', 'usuario.id_personal')
                ->where('usuario.id_empresa', AppContext::ID_EMPRESA())
                ->orderByDesc('usuario.id')
                ->get()
                ->map(fn (User $usuario): array => [
                    'id' => $usuario->id,
                    'nombre' => $usuario->nombre ?? '',
                    'apellido' => $usuario->apellido ?? '',
                    'usuario' => $usuario->usuario ?? '',
                    'email' => $usuario->email ?? '',
                    'tipo_persona' => $usuario->tipo_persona ?? 'STANDARD',
                    'id_rol' => $usuario->id_rol,
                    'rol' => $usuario->rol ?? 'SUPER ADMINISTRADOR',
                    'id_personal' => $usuario->id_personal,
                    'personal_nombre' => $usuario->personal_nombre ?? '',
                    'id_estudiante' => $usuario->id_estudiante,
                    'estudiante_nombre' => $usuario->estudiante_nombre ?? '',
                    'imagen' => $usuario->imagen,
                    'imagen_url' => ImageUploadService::url($usuario->imagen, 'usuarios'),
                    'fl_ver_informacion_privada' => (bool) $usuario->fl_ver_informacion_privada,
                    'fl_suspendido' => (bool) $usuario->fl_suspendido,
                ]);

            return response()->json([
                'data' => $usuarios,
            ]);
        } catch (Throwable $e) {
            return ErrorHandlerService::handleCrudError($e, 'obtener', 'usuario');
        }
    }

    public function store(UsuarioStoreRequest $request): JsonResponse
    {
        $this->validarPermisos('configuracion-usuario', 'new');

        $payload = $request->validated();

        $usuario = DB::transaction(function () use ($payload, $request): User {
            // Manejar subida de imagen
            $imagen = ImageUploadService::upload($request->file('imagen'), 'usuarios');

            // Determinar nombre y apellido según tipo de persona
            $nombre = $payload['nombre'] ?? null;
            $apellido = $payload['apellido'] ?? null;
            $id_personal = null;
            $id_estudiante = null;

            if ($payload['tipo_persona'] === 'DOCENTE' && isset($payload['id_personal'])) {
                $id_personal = (int) $payload['id_personal'];
                // Obtener datos del personal
                $personal = PersonalModel::query()->find($id_personal);
                if ($personal) {
                    $nombre = $personal->nombre;
                    $apellido = $personal->apellido;
                }
            } elseif ($payload['tipo_persona'] === 'ESTUDIANTE' && isset($payload['id_estudiante'])) {
                $id_estudiante = (int) $payload['id_estudiante'];
                // Aquí deberías obtener datos del estudiante si existe el modelo
            }

            /** @var User $usuario */
            $usuario = User::query()->create([
                'usuario' => $payload['usuario'],
                'email' => $payload['email'],
                'password' => Hash::make($payload['password']),
                'nombre' => $nombre,
                'apellido' => $apellido,
                'tipo_persona' => $payload['tipo_persona'] ?? 'STANDARD',
                'id_personal' => $id_personal,
                'id_estudiante' => $id_estudiante,
                'id_rol' => isset($payload['id_rol']) && $payload['id_rol'] ? (int) $payload['id_rol'] : null,
                'imagen' => $imagen,
                'fl_ver_informacion_privada' => $payload['fl_ver_informacion_privada'] ?? false,
                'fl_suspendido' => false,
                'id_empresa' => AppContext::ID_EMPRESA(),
            ]);

            return $usuario;
        });

        $this->centinelaService->registrarCambio(
            accion: 'NUEVO',
            descripcion: $usuario->usuario,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Usuario registrado correctamente.',
            'data' => $usuario->fresh(),
        ], 201);
    }

    public function update(UsuarioUpdateRequest $request, User $usuario): JsonResponse
    {
        $this->validarPermisos('configuracion-usuario', 'edit');

        $payload = $request->validated();

        DB::transaction(function () use ($usuario, $payload, $request): void {
            // Manejar subida de imagen
            $imagen = ImageUploadService::upload(
                $request->file('imagen'),
                'usuarios',
                $payload['imagen_anterior'] ?? $usuario->imagen
            );

            // Determinar nombre y apellido según tipo de persona
            $nombre = $payload['nombre'] ?? $usuario->nombre;
            $apellido = $payload['apellido'] ?? $usuario->apellido;
            $id_personal = $usuario->id_personal;
            $id_estudiante = $usuario->id_estudiante;

            if ($payload['tipo_persona'] === 'DOCENTE' && isset($payload['id_personal'])) {
                $id_personal = (int) $payload['id_personal'];
                $id_estudiante = null;
                // Obtener datos del personal
                $personal = PersonalModel::query()->find($id_personal);
                if ($personal) {
                    $nombre = $personal->nombre;
                    $apellido = $personal->apellido;
                }
            } elseif ($payload['tipo_persona'] === 'ESTUDIANTE' && isset($payload['id_estudiante'])) {
                $id_estudiante = (int) $payload['id_estudiante'];
                $id_personal = null;
                // Aquí deberías obtener datos del estudiante si existe el modelo
            } elseif ($payload['tipo_persona'] === 'STANDARD' || empty($payload['tipo_persona'])) {
                $id_personal = null;
                $id_estudiante = null;
            }

            $usuario->update([
                'usuario' => $payload['usuario'],
                'email' => $payload['email'],
                'nombre' => $nombre,
                'apellido' => $apellido,
                'tipo_persona' => $payload['tipo_persona'] ?? 'STANDARD',
                'id_personal' => $id_personal,
                'id_estudiante' => $id_estudiante,
                'id_rol' => isset($payload['id_rol']) && $payload['id_rol'] ? (int) $payload['id_rol'] : null,
                'imagen' => $imagen ?? $usuario->imagen,
                'fl_ver_informacion_privada' => $payload['fl_ver_informacion_privada'] ?? false,
            ]);
        });

        $this->centinelaService->registrarCambio(
            accion: 'EDITAR',
            descripcion: $usuario->fresh()->usuario,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'data' => $usuario->fresh(),
        ]);
    }

    public function destroy(User $usuario): JsonResponse
    {
        $this->validarPermisos('configuracion-usuario', 'delete');

        // Eliminar imagen si existe
        if ($usuario->imagen && Storage::disk('public')->exists('usuarios/'.$usuario->imagen)) {
            Storage::disk('public')->delete('usuarios/'.$usuario->imagen);
        }

        $usuario->delete();

        $this->centinelaService->registrarCambio(
            accion: 'ELIMINAR',
            descripcion: $usuario->usuario,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Usuario eliminado correctamente.',
        ]);
    }

    public function changePassword(UsuarioChangePasswordRequest $request, User $usuario): JsonResponse
    {
        $this->validarPermisos('configuracion-usuario', 'edit');

        $usuario->update([
            'password' => Hash::make($request->validated()['password']),
        ]);

        $this->centinelaService->registrarCambio(
            accion: 'CAMBIAR CONTRASEÑA',
            descripcion: $usuario->usuario,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.',
        ]);
    }

    public function suspend(User $usuario): JsonResponse
    {
        $this->validarPermisos('configuracion-usuario', 'edit');

        $usuario->update([
            'fl_suspendido' => true,
        ]);

        $this->centinelaService->registrarCambio(
            accion: 'SUSPENDER',
            descripcion: $usuario->usuario,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Usuario suspendido correctamente.',
        ]);
    }

    public function activate(User $usuario): JsonResponse
    {
        $this->validarPermisos('configuracion-usuario', 'edit');

        $usuario->update([
            'fl_suspendido' => false,
        ]);

        $this->centinelaService->registrarCambio(
            accion: 'ACTIVAR',
            descripcion: $usuario->usuario,
            menu: self::MENU,
            modulo: self::MODULO,
        );

        return response()->json([
            'message' => 'Usuario activado correctamente.',
        ]);
    }
}
