<?php

declare(strict_types=1);

namespace App\Http\Requests\Configuracion;

use Illuminate\Foundation\Http\FormRequest;

final class PersonalUpdateRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id_tipo_personal' => ['required', 'integer', 'exists:tipo_personal,id'],
            'id_tipo_documento' => ['required', 'integer', 'exists:static_documento,id'],
            'numero_documento' => ['required', 'string', 'max:20'],
            'nombre' => ['required', 'string', 'max:200'],
            'apellido' => ['required', 'string', 'max:200'],
            'tipo_contratacion' => ['required', 'string', 'in:DIRECTA,TERCERO'],
            'direccion' => ['nullable', 'string', 'max:100'],
            'ubigeo' => ['nullable', 'string', 'max:6'],
            'comentario' => ['nullable', 'string', 'max:100'],
            'id_proveedor' => [
                'nullable',
                'integer',
                'exists:proveedor,id',
                'required_if:tipo_contratacion,TERCERO',
            ],
            'imagen' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
            'imagen_firma' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
        ];
    }
}
