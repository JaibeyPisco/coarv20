<?php

declare(strict_types=1);

namespace App\Models\Configuracion;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class EstadoMonitoreoModel extends Model
{
    use HasFactory;

    protected $table = 'estado_monitoreo';

    protected $fillable = [
        'nombre',
        'tipo',
        'color_bg',
        'color_text',
        'id_empresa',
    ];

    protected $casts = [
        'id' => 'integer',
        'id_empresa' => 'integer',
    ];

    #[Scope]
    protected function activos(Builder $query): Builder
    {
        return $query;
    }
}
