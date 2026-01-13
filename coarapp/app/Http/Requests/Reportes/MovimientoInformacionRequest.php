<?php

declare(strict_types=1);

namespace App\Http\Requests\Reportes;

use Illuminate\Foundation\Http\FormRequest;

final class MovimientoInformacionRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'fecha_inicio' => ['required', 'date'],
             'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
        ];
    }

    public function messages():array
    {
        return [
           'fecha_inicio.required' => 'La fecha inicio es obligatoria',
            'fecha_inicio.date' => 'La fecha inicio no es válida',
            'fecha_fin.required' => 'La fecha fin es obligatoria',
            'fecha_fin.date' => 'La fecha fin no es válida',
            'fecha_fin.after_or_equal' => 'La fecha fin debe ser mayor o igual a la fecha inicio'
        ];
    }

   public function attributes():array
   {
    return [
        'fecha_inicio' => 'Fecha inicio',
        'fecha_fin' => 'Fecha fin'
    ];
   }
}
