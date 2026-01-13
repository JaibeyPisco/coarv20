<?php

declare(strict_types=1);

namespace App\Http\Requests\Configuracion;

use Illuminate\Foundation\Http\FormRequest;

final class TiposIncidenciaStoreRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:50'],
            'nivel_incidencia' => ['required', 'string', 'in:NEGATIVO,POSITIVA,NEUTRA'],
            'nivel_severidad' => ['required', 'string', 'in:BAJA,MEDIA,ALTA'],
            'derivacion_inmediata' => ['required', 'string', 'in:SI,NO'],
        ];
    }
}
