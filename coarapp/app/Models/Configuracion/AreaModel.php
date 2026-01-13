<?php

declare(strict_types=1);

namespace App\Models\Configuracion;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class AreaModel extends Model
{
    use HasFactory;

    protected $table = 'area';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'id_usuario',
        'id_empresa',
        'id_encargado',
    ];

    protected $casts = [
        'id' => 'integer',
        'estado' => 'integer',
        'id_usuario' => 'integer',
        'id_empresa' => 'integer',
        'id_encargado' => 'integer',
    ];

    #[Scope]
    protected function activos(Builder $query): Builder
    {
        return $query->where('estado', '!=', 0);
    }
}
