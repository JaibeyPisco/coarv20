<?php

declare(strict_types=1);

namespace App\Models\Configuracion;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PersonalModel extends Model
{
    use HasFactory;

    protected $table = 'personal';

    protected $fillable = [
        'numero_documento',
        'id_tipo_personal',
        'imagen',
        'firma',
        'codigo',
        'nombre',
        'apellido',
        'telefono',
        'estado',
        'id_tipo_documento',
        'tipo_contratacion',
        'direccion',
        'ubigeo',
        'comentario',
        'id_proveedor',
        'id_empresa',
    ];

    protected $casts = [
        'id' => 'integer',
        'numero_documento' => 'integer',
        'id_tipo_personal' => 'integer',
        'estado' => 'integer',
        'id_tipo_documento' => 'integer',
        'id_proveedor' => 'integer',
        'id_empresa' => 'integer',
    ];

    public function tipoPersonal(): BelongsTo
    {
        return $this->belongsTo(TipoPersonalModel::class, 'id_tipo_personal');
    }

    #[Scope]
    protected function activos(Builder $query): Builder
    {
        return $query->where('estado', '!=', 0);
    }
}
