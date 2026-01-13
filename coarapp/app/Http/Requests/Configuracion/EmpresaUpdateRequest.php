<?php

declare(strict_types=1);

namespace App\Http\Requests\Configuracion;

use Illuminate\Foundation\Http\FormRequest;

final class EmpresaUpdateRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'numero_documento' => ['required', 'string', 'max:20'],
            'razon_social' => ['required', 'string', 'max:200'],
            'nombre_comercial' => ['required', 'string', 'max:200'],
            'direccion' => ['required', 'string', 'max:200'],
            'telefono' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
            'logo_factura' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
            'logo_anterior' => ['nullable', 'string'],
            'logo_factura_anterior' => ['nullable', 'string'],
        ];
    }
}
