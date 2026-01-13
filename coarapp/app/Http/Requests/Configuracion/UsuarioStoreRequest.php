<?php

declare(strict_types=1);

namespace App\Http\Requests\Configuracion;

use Illuminate\Foundation\Http\FormRequest;

final class UsuarioStoreRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'usuario' => ['required', 'string', 'max:50', 'unique:usuario,usuario'],
            'email' => ['required', 'email', 'max:100', 'unique:usuario,email'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
            'tipo_persona' => ['nullable', 'string', 'in:STANDARD,DOCENTE,ESTUDIANTE'],
            'id_rol' => ['nullable', 'integer', 'exists:rol,id'],
            'imagen' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
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
