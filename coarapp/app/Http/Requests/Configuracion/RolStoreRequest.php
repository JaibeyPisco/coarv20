<?php

declare(strict_types=1);

namespace App\Http\Requests\Configuracion;

use Illuminate\Foundation\Http\FormRequest;

final class RolStoreRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:200'],
            'fl_no_dashboard' => ['nullable', 'boolean'],
            'permisos' => ['required', 'array', 'min:1'],
            'permisos.*.menu' => ['required', 'string'],
            'permisos.*.view' => ['nullable', 'boolean'],
            'permisos.*.new' => ['nullable', 'boolean'],
            'permisos.*.edit' => ['nullable', 'boolean'],
            'permisos.*.delete' => ['nullable', 'boolean'],
        ];
    }
}
