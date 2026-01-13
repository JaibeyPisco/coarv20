<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

final class ImageUploadService
{
    /**
     * Maneja la subida de imágenes
     *
     * @param  UploadedFile|null  $file  Archivo a subir
     * @param  string  $folder  Carpeta donde guardar (ej: 'usuarios', 'empresas')
     * @param  string|null  $oldFile  Nombre del archivo anterior a eliminar
     * @return string|null Retorna null si no hay archivo nuevo (para mantener el anterior), o el nuevo nombre de archivo
     */
    public static function upload(?UploadedFile $file, string $folder, ?string $oldFile = null): ?string
    {
        if (! $file || ! $file->isValid()) {
            return null;
        }

        try {
            $directory = storage_path('app/public/'.$folder);
            if (! is_dir($directory)) {
                Storage::disk('public')->makeDirectory($folder);
            }

            if ($oldFile && Storage::disk('public')->exists($folder.'/'.$oldFile)) {
                Storage::disk('public')->delete($folder.'/'.$oldFile);
            }

            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs($folder, $filename, 'public');

            if (! Storage::disk('public')->exists($folder.'/'.$filename)) {
                Log::error("Error al guardar imagen: {$folder}/{$filename}");

                return null;
            }

            return basename($path);
        } catch (Exception $e) {
            Log::error('Error en ImageUploadService::upload: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Genera la URL pública de una imagen
     *
     * @param  string|null  $filename  Nombre del archivo
     * @param  string  $folder  Carpeta donde está el archivo (ej: 'usuarios', 'empresas')
     * @return string|null URL completa de la imagen o null si no hay archivo
     */
    public static function url(?string $filename, string $folder): ?string
    {
        if (! $filename) {
            return null;
        }

        $path = $folder.'/'.$filename;

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        // Usar asset() para generar la URL correcta
        return asset('storage/'.$path);
    }
}
