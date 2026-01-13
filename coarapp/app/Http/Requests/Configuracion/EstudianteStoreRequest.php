<?php

declare(strict_types=1);

namespace App\Http\Requests\Configuracion;

use Illuminate\Foundation\Http\FormRequest;

final class EstudianteStoreRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'dni' => ['required', 'string', 'max:20', 'unique:estudiante,dni'],
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:100'],
            'grado' => ['required', 'string', 'max:10'],
            'seccion' => ['required', 'string', 'max:10'],
            'sexo' => ['required', 'string', 'in:MASCULINO,FEMENINO'],
            'correo_electronico' => ['required', 'email', 'max:255'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'codigo_estudiante' => ['nullable', 'string', 'max:255'],
            'condicion_estudiante' => ['required', 'string', 'in:ESTUDIANTE,EGRESADO'],
            'obsv' => ['nullable', 'string', 'max:255'],
            'lugar_nacimiento' => ['nullable', 'string', 'max:255'],
            'fecha_caducidad_dni' => ['nullable', 'date'],
            'num_telefonico' => ['nullable', 'string', 'max:20'],
            'religion' => ['nullable', 'string', 'max:50'],
            'region_domicilio_actual' => ['nullable', 'string', 'max:50'],
            'provincia_domicilio_actual' => ['nullable', 'string', 'max:50'],
            'distrito_domicilio_actual' => ['nullable', 'string', 'max:50'],
            'direccion_domicilio_actual' => ['nullable', 'string', 'max:255'],
            'referencia_domicilio_actual' => ['nullable', 'string', 'max:255'],
            'lav' => ['nullable', 'string', 'max:10'],
            'llaves' => ['nullable', 'string', 'max:10'],
            'pabellon' => ['nullable', 'string', 'max:10'],
            'ala' => ['nullable', 'string', 'max:10'],
            'banos' => ['nullable', 'string', 'max:10'],
            'monitor_acompana' => ['nullable', 'string', 'max:255'],
            'cama_ropero' => ['nullable', 'string', 'max:10'],
            'duchas' => ['nullable', 'string', 'max:10'],
            'urinarios' => ['nullable', 'string', 'max:10'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,jpg,png,gif', 'max:2048'],
        ];
    }
}
