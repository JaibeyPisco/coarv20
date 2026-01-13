<?php

declare(strict_types=1);

namespace App\Models\Configuracion;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class ProveedorModel extends Model
{
    use HasFactory;

    protected $table = 'proveedor';

    protected $fillable = [
        'id_documento',
        'numero_documento',
        'razon_social',
        'correo',
        'direccion',
        'telefono',
        'id_area',
        'id_usuario',
        'estado',
        'contacto_nombre',
        'imagen',
        'id_ubigeo',
        'id_empresa',
    ];

    protected $casts = [
        'id' => 'integer',
        'id_documento' => 'integer',
        'id_area' => 'integer',
        'id_usuario' => 'integer',
        'id_ubigeo' => 'integer',
        'id_empresa' => 'integer',
    ];

    #[Scope]
    protected function activos(Builder $query): Builder
    {
        return $query->where('estado', '!=', 0);
    }
}
