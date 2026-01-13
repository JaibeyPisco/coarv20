<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class ErrorHandlerService
{
    /**
     * Maneja excepciones y retorna una respuesta JSON con mensaje user-friendly
     *
     * @param  string  $defaultMessage  Mensaje por defecto si no se puede determinar uno específico
     * @param  int  $defaultStatusCode  Código de estado HTTP por defecto
     */
    public static function handle(
        Throwable $exception,
        string $defaultMessage = 'Ha ocurrido un error. Por favor, intenta nuevamente.',
        int $defaultStatusCode = 500
    ): JsonResponse {
        // Log del error completo para debugging
        Log::error('Error en API: '.$exception->getMessage(), [
            'exception' => $exception::class,
            'trace' => $exception->getTraceAsString(),
        ]);

        // Manejar diferentes tipos de excepciones
        if ($exception instanceof ValidationException) {
            return response()->json([
                'message' => 'Los datos proporcionados no son válidos.',
                'errors' => $exception->errors(),
            ], 422);
        }

        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'message' => 'El recurso solicitado no fue encontrado.',
            ], 404);
        }

        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'La ruta solicitada no fue encontrada.',
            ], 404);
        }

        // Errores de base de datos
        if ($exception instanceof QueryException) {
            $errorCode = $exception->getCode();

            // Error de conexión
            if ($errorCode === 2002 || str_contains($exception->getMessage(), 'Connection refused')) {
                return response()->json([
                    'message' => 'Error de conexión con la base de datos. Por favor, intenta nuevamente en unos momentos.',
                ], 503);
            }

            // Error de duplicado
            if ($errorCode === 23000 || str_contains($exception->getMessage(), 'Duplicate entry')) {
                return response()->json([
                    'message' => 'Ya existe un registro con estos datos. Por favor, verifica la información.',
                ], 409);
            }

            // Error de foreign key
            if (str_contains($exception->getMessage(), 'foreign key constraint')) {
                return response()->json([
                    'message' => 'No se puede realizar esta acción porque el registro está siendo utilizado en otra parte del sistema.',
                ], 409);
            }

            return response()->json([
                'message' => 'Error al procesar la solicitud en la base de datos. Por favor, intenta nuevamente.',
            ], 500);
        }

        // Errores de archivos/storage
        if ($exception instanceof FileNotFoundException) {
            return response()->json([
                'message' => 'El archivo solicitado no fue encontrado.',
            ], 404);
        }

        // Errores de permisos
        if ($exception instanceof AuthorizationException) {
            return response()->json([
                'message' => 'No tienes permisos para realizar esta acción.',
            ], 403);
        }

        // Errores de autenticación
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'message' => 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.',
            ], 401);
        }

        // Error genérico con mensaje personalizado
        return response()->json([
            'message' => $defaultMessage,
        ], $defaultStatusCode);
    }

    /**
     * Maneja errores específicos de operaciones CRUD
     *
     * @param  string  $operation  Operación que se intentó realizar (crear, actualizar, eliminar, obtener)
     * @param  string  $resource  Nombre del recurso (ej: "empresa", "usuario")
     */
    public static function handleCrudError(
        Throwable $exception,
        string $operation,
        string $resource
    ): JsonResponse {
        $messages = [
            'crear' => "Error al crear el {$resource}. Por favor, verifica los datos e intenta nuevamente.",
            'actualizar' => "Error al actualizar el {$resource}. Por favor, verifica los datos e intenta nuevamente.",
            'eliminar' => "Error al eliminar el {$resource}. Por favor, intenta nuevamente.",
            'obtener' => "Error al cargar los datos del {$resource}. Por favor, intenta nuevamente.",
        ];

        $defaultMessage = $messages[$operation] ?? "Error al procesar la solicitud del {$resource}.";

        return self::handle($exception, $defaultMessage);
    }
}
