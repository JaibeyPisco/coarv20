<?php

declare(strict_types=1);

namespace App\Http\Requests\Configuracion;

use Illuminate\Foundation\Http\FormRequest;

final class PersonalStoreRequest extends FormRequest
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

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir strings a enteros para campos numéricos
        if ($this->has('id_tipo_personal') && is_string($this->id_tipo_personal)) {
            $this->merge([
                'id_tipo_personal' => (int) $this->id_tipo_personal,
            ]);
        }

        if ($this->has('id_tipo_documento') && is_string($this->id_tipo_documento)) {
            $this->merge([
                'id_tipo_documento' => (int) $this->id_tipo_documento,
            ]);
        }

        if ($this->has('id_proveedor') && is_string($this->id_proveedor) && $this->id_proveedor !== '') {
            $this->merge([
                'id_proveedor' => (int) $this->id_proveedor,
            ]);
        }
    }
}
