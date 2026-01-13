<?php

declare(strict_types=1);

namespace App\Models\Configuracion;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class RolModel extends Model
{
    use HasFactory;

    protected $table = 'rol';

    protected $fillable = [
        'nombre',
        'fl_no_dashboard',
        'estado',
        'id_empresa',
    ];

    protected $casts = [
        'id' => 'integer',
        'fl_no_dashboard' => 'boolean',
        'estado' => 'integer',
        'id_empresa' => 'integer',
    ];

    public function permisos(): HasMany
    {
        return $this->hasMany(PermisoModel::class, 'id_rol');
    }

    #[Scope]
    protected function activos(Builder $query): Builder
    {
        return $query->where('estado', '!=', 0);
    }
}
