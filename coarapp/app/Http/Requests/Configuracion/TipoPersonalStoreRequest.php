<?php

declare(strict_types=1);

namespace App\Http\Requests\Configuracion;

use App\Support\AppContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class TipoPersonalStoreRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tipo_personal', 'nombre')->where('id_empresa', AppContext::ID_EMPRESA()),
            ],
            'descripcion' => ['nullable', 'string', 'max:555'],
        ];
    }
}
