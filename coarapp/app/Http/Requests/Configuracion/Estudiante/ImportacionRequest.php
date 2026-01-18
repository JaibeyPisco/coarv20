<?php

declare(strict_types=1);

namespace App\Http\Requests\Configuracion\Estudiante;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

final class ImportacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Decodifica JSON (FormData)
        if (is_string($this->estudiantes)) {
            $this->merge([
                'estudiantes' => json_decode($this->estudiantes, true) ?? [],
            ]);
        }

        // Normaliza: "", "NO REGISTRA", "N/A", "-" => null (para que nullable funcione)
        $rows = $this->input('estudiantes', []);
        if (!is_array($rows)) {
            return;
        }

        $nullablesToNull = ['','NO REGISTRA','NOREGISTRA','N/A','NA','-','--'];

        $normalized = [];
        foreach ($rows as $i => $row) {
            if (!is_array($row)) {
                $normalized[$i] = $row;
                continue;
            }

            foreach ($row as $k => $v) {
                if (is_string($v)) {
                    $vTrim = trim($v);
                    $vUpper = mb_strtoupper($vTrim);
                    if (in_array($vUpper, $nullablesToNull, true)) {
                        $row[$k] = null;
                    } else {
                        $row[$k] = $vTrim;
                    }
                }
            }

            $normalized[$i] = $row;
        }

        $this->merge(['estudiantes' => $normalized]);
    }

    public function rules(): array
    {
        return [
            'estudiantes' => ['required', 'array', 'min:1'],

            // ---------- BÁSICOS (requeridos) ----------
            'estudiantes.*.numero'   => ['required', 'integer', 'min:1'],
            'estudiantes.*.estado'   => ['required', 'string', 'max:30'],
            'estudiantes.*.apellidos'=> ['required', 'string', 'max:120'],
            'estudiantes.*.nombre'   => ['required', 'string', 'max:120'],
            'estudiantes.*.grado'    => ['required', 'integer', 'min:1', 'max:6'],
            'estudiantes.*.seccion'  => ['required', 'string', 'max:2'],
            'estudiantes.*.dni'      => ['required', 'digits:8'],
            'estudiantes.*.sexo'     => ['required', 'string', 'in:FEMENINO,MASCULINO'],

            // ---------- BÁSICOS (opcionales, si vienen deben cumplir tipo) ----------
            'estudiantes.*.obsv'     => ['nullable', 'string', 'max:50'],
            'estudiantes.*.foto'     => ['nullable', 'url', 'max:255'],
            'estudiantes.*.email'    => ['nullable', 'email', 'max:150'],

            // Excel date serial (si lo manejas como entero)
            'estudiantes.*.fecha_nacimiento'   => ['required', 'integer', 'between:1,80000'],
            'estudiantes.*.fecha_caducidad_dni'=> ['nullable', 'integer', 'between:1,80000'],

            // Teléfonos (solo dígitos)
            'estudiantes.*.telefono' => ['nullable', 'digits_between:6,15'],

            // Números varios
            'estudiantes.*.lav'      => ['nullable', 'integer', 'min:0'],
            'estudiantes.*.llaves'   => ['nullable', 'integer', 'min:0'],
            'estudiantes.*.duchas'   => ['nullable', 'integer', 'min:0'],
            'estudiantes.*.banos'    => ['nullable', 'integer', 'min:0'],
            'estudiantes.*.urinarios'=> ['nullable', 'integer', 'min:0'],

            // Textos varios
            'estudiantes.*.pabellon'            => ['nullable', 'string', 'max:10'],
            'estudiantes.*.ala'                 => ['nullable', 'string', 'max:10'],
            'estudiantes.*.cama_ropero'         => ['nullable', 'string', 'max:20'],
            'estudiantes.*.monitor'             => ['nullable', 'string', 'max:120'],
            'estudiantes.*.lugar_nacimiento'    => ['nullable', 'string', 'max:150'],
            'estudiantes.*.religion'            => ['nullable', 'string', 'max:50'],
            'estudiantes.*.region'              => ['nullable', 'string', 'max:60'],
            'estudiantes.*.provincia'           => ['nullable', 'string', 'max:60'],
            'estudiantes.*.distrito'            => ['nullable', 'string', 'max:60'],
            'estudiantes.*.direccion_domicilio' => ['nullable', 'string', 'max:255'],
            'estudiantes.*.referencia_domicilio'=> ['nullable', 'string', 'max:255'],

            // ---------- MADRE ----------
            'estudiantes.*.madre_viva'                 => ['nullable', 'string', 'in:SI,NO'],
            'estudiantes.*.estudiante_vive_con_madre'  => ['nullable', 'string', 'in:SI,NO'],
            'estudiantes.*.apellidos_madre'            => ['nullable', 'string', 'max:120'],
            'estudiantes.*.nombres_madre'              => ['nullable', 'string', 'max:120'],
            'estudiantes.*.dni_madre'                  => ['nullable', 'digits_between:8,12'],
            'estudiantes.*.grado_instruccion_madre'    => ['nullable', 'string', 'max:60'],
            'estudiantes.*.ocupacion_madre'            => ['nullable', 'string', 'max:120'],
            'estudiantes.*.telefono_madre'             => ['nullable', 'digits_between:6,15'],
            'estudiantes.*.email_madre'                => ['nullable', 'email', 'max:150'],
            'estudiantes.*.motivo_estudiante_no_con_vive_madre' => ['nullable', 'string', 'max:255'],

            // ---------- PADRE ----------
            'estudiantes.*.padre_vivo'                 => ['nullable', 'string', 'in:SI,NO'],
            'estudiantes.*.estudiante_vive_con_padre'  => ['nullable', 'string', 'in:SI,NO'],
            'estudiantes.*.apellidos_padre'            => ['nullable', 'string', 'max:120'],
            'estudiantes.*.nombres_padre'              => ['nullable', 'string', 'max:120'],
            'estudiantes.*.dni_padre'                  => ['nullable', 'digits_between:8,12'],
            'estudiantes.*.grado_instruccion_padre'    => ['nullable', 'string', 'max:60'],
            'estudiantes.*.ocupacion_padre'            => ['nullable', 'string', 'max:120'],
            'estudiantes.*.telefono_padre'             => ['nullable', 'digits_between:6,15'],
            'estudiantes.*.email_padre'                => ['nullable', 'email', 'max:150'],
            'estudiantes.*.motivo_estudiante_no_con_vive_padre' => ['nullable', 'string', 'max:255'],

            // ---------- APODERADO PRINCIPAL ----------
            'estudiantes.*.parentesco_apoderado'   => ['nullable', 'string', 'max:30'],
            'estudiantes.*.apellido_apoderado'     => ['nullable', 'string', 'max:120'],
            'estudiantes.*.nombre_apoderado'       => ['nullable', 'string', 'max:120'],
            'estudiantes.*.dni_apoderado'          => ['nullable', 'digits_between:8,12'],
            'estudiantes.*.telefono_apoderado'     => ['nullable', 'digits_between:6,15'],
            'estudiantes.*.tipo_familia_apoderado' => ['nullable', 'string', 'max:60'],

            // ---------- APODERADO 1..7 (todas las columnas del Excel que mostraste) ----------
            ...$this->apoderadoRules(1),
            ...$this->apoderadoRules(2),
            ...$this->apoderadoRules(3),
            ...$this->apoderadoRules(4),
            ...$this->apoderadoRules(5),
            ...$this->apoderadoRules(6),
            ...$this->apoderadoRules(7),
        ];
    }

    private function apoderadoRules(int $n): array
    {
        return [
            "estudiantes.*.apellido_apoderado{$n}"   => ['nullable', 'string', 'max:120'],
            "estudiantes.*.nombre_apoderado{$n}"     => ['nullable', 'string', 'max:120'],
          
            "estudiantes.*.dni_apoderado{$n}"        => ['nullable', 'digits:8'],
            "estudiantes.*.telefono_apoderado{$n}"   => ['nullable', 'digits:9'],
            "estudiantes.*.parentesco_apoderado{$n}" => ['nullable', 'string', 'max:30'],
            "estudiantes.*.legalizado_apoderado{$n}" => ['nullable', 'string', 'in:SI,NO'],
        ];
    }

    public function messages(): array
    {
        return [
            'estudiantes.required' => 'Debe enviar el listado de estudiantes.',
            'estudiantes.array'    => 'El formato de estudiantes es inválido.',

            'estudiantes.*.dni.digits' => 'El DNI debe tener 8 dígitos.',
            'estudiantes.*.email.email' => 'El email del estudiante no es válido.',
            'estudiantes.*.foto.url' => 'La URL de la foto no es válida.',
            'estudiantes.*.telefono.digits_between' => 'El teléfono debe tener entre 6 y 15 dígitos.',

            'estudiantes.*.email_madre.email' => 'El email de la madre no es válido.',
            'estudiantes.*.telefono_madre.digits_between' => 'El teléfono de la madre debe tener entre 6 y 15 dígitos.',

            'estudiantes.*.email_padre.email' => 'El email del padre no es válido.',
            'estudiantes.*.telefono_padre.digits_between' => 'El teléfono del padre debe tener entre 6 y 15 dígitos.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $rawErrors = $validator->errors()->toArray();
        $erroresPorFila = [];
        $erroresGlobales = [];

        foreach ($rawErrors as $key => $mensajes) {
            if (preg_match('/^estudiantes\.(\d+)\.(.+)$/', $key, $m)) {
                $index = (int) $m[1];
                $campo = $m[2];
                $filaExcel = $index + 4; // ajusta a tu Excel

                $erroresPorFila[$index] ??= [
                    'fila'    => $filaExcel,
                    'indice'  => $index,
                    'errores' => [],
                ];

                $erroresPorFila[$index]['errores'][$campo] = $mensajes;
            } else {
                $erroresGlobales[$key] = $mensajes;
            }
        }

        throw new HttpResponseException(
            response()->json([
                'errores'         => array_values($erroresPorFila),
                'erroresGlobales' => $erroresGlobales,
            ], 422)
        );
    }
}
