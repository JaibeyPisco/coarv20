<?php

declare(strict_types=1);

namespace App\Models\Configuracion;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PadresApoderadosModel extends Model
{
    protected $table = 'padres_apoderados';

    protected $fillable = [
        'tipo',
        'vive',
        'vive_con_estudiante',
        'nombres',
        'apellidos',
        'dni',
        'grado_instruccion',
        'ocupacion_actual',
        'telefono',
        'correo_electronico',
        'motivo_no_vive_con_estudiante',
        'parentesco_estudiante',
        'tipo_familia',
        'fl_legalizado',
        'foto',
        'id_estudiante',
    ];

    protected $casts = [
        'vive' => 'integer',
        'vive_con_estudiante' => 'integer',
        'fl_legalizado' => 'integer',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(EstudianteModel::class, 'id_estudiante');
    }
}

