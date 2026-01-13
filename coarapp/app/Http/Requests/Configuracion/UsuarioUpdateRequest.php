<?php

declare(strict_types=1);

namespace App\Http\Requests\Configuracion;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UsuarioUpdateRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $usuarioId = $this->route('usuario')->id ?? null;

        $rules = [
            'usuario' => ['required', 'string', 'max:50', Rule::unique('usuario', 'usuario')->ignore($usuarioId)],
            'email' => ['required', 'email', 'max:100', Rule::unique('usuario', 'email')->ignore($usuarioId)],
            'tipo_persona' => ['nullable', 'string', 'in:STANDARD,DOCENTE,ESTUDIANTE'],
            'id_rol' => ['nullable', 'integer', 'exists:rol,id'],
            'imagen' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
            'imagen_anterior' => ['nullable', 'string'],
            'fl_ver_informacion_privada' => ['boolean'],
        ];

        // Validaciones condicionales según tipo de persona
        $tipoPersona = $this->input('tipo_persona', 'STANDARD');

        if ($tipoPersona === 'DOCENTE') {
            $rules['id_personal'] = ['required', 'integer', 'exists:personal,id'];
        } elseif ($tipoPersona === 'ESTUDIANTE') {
            $rules['id_estudiante'] = ['required', 'integer'];
            // Aquí deberías validar exists:estudiante,id si existe el modelo
        } else {
            $rules['nombre'] = ['required', 'string', 'max:200'];
            $rules['apellido'] = ['required', 'string', 'max:200'];
        }

        return $rules;
    }
}
