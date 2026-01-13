<?php

declare(strict_types=1);

namespace App\Models\Configuracion;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class EstudianteModel extends Model
{
    use HasFactory;

    protected $table = 'estudiante';

    protected $fillable = [
        'nombres',
        'apellidos',
        'obsv',
        'grado',
        'seccion',
        'dni',
        'foto',
        'sexo',
        'correo_electronico',
        'codigo_estudiante',
        'fecha_nacimiento',
        'lav',
        'llaves',
        'pabellon',
        'ala',
        'cama_ropero',
        'duchas',
        'banos',
        'urinarios',
        'monitor_acompana',
        'lugar_nacimiento',
        'fecha_caducidad_dni',
        'num_telefonico',
        'religion',
        'region_domicilio_actual',
        'provincia_domicilio_actual',
        'distrito_domicilio_actual',
        'direccion_domicilio_actual',
        'referencia_domicilio_actual',
        'condicion_estudiante',
    ];

    protected $casts = [
        'id' => 'integer',
        'fecha_nacimiento' => 'date',
        'fecha_caducidad_dni' => 'date',
    ];

    #[Scope]
    protected function activos(Builder $query): Builder
    {
        return $query->where('condicion_estudiante', '!=', 'ELIMINADO');
    }
}
