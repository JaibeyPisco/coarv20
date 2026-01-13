<?php

declare(strict_types=1);

namespace App\Http\Requests\Configuracion;

use Illuminate\Foundation\Http\FormRequest;

final class EstadoMonitoreoStoreRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'tipo' => ['required', 'string', 'in:INCIDENCIA,DERIVACION'],
            'color_bg' => ['required', 'string', 'max:45'],
            'color_text' => ['nullable', 'string', 'max:45'],
        ];
    }
}
