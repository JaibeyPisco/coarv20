<?php

declare(strict_types=1);

namespace App\Models\Configuracion;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class TipoPersonalModel extends Model
{
    use HasFactory;

    protected $table = 'tipo_personal';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'id_usuario',
        'id_empresa',
    ];

    protected $casts = [
        'id' => 'integer',
        'estado' => 'integer',
        'id_usuario' => 'integer',
        'id_empresa' => 'integer',
    ];

    #[Scope]
    protected function activos(Builder $query): Builder
    {
        return $query->where('estado', '!=', 0);
    }
}
