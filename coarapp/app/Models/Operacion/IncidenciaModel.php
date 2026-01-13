<?php

declare(strict_types=1);

namespace App\Models\Operacion;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class IncidenciaModel extends Model
{
    use HasFactory;

    protected $table = "incidencias";

    protected $fillable = [
        "id_usuario",

        "descripcion",
        "id_tipo_incidencia",
        "id_lugar_incidencia",
        "fecha",
        "fl_estado",
        "serie",
        "numero",
        "id_estudiante",
        "id_usuario",
        "motivo_anulacion",
        "motivo_derivacion",
        "motivo_finalizacion",
        "estado",
        "id_personal_derivado",
        "id_area",
        "id_usuario_derivador",
        "id_usuario_finalizador",
    ];

    // protected $casts = [
    //     "id" => "integer",
    //     "fecha_nacimiento" => "date",
    //     "fecha_caducidad_dni" => "date",
    // ];

    // #[Scope]
    // protected function activos(Builder $query): Builder
    // {
    //     return $query->where("condicion_estudiante", "!=", "ELIMINADO");
    // }
}
