<?php

declare(strict_types=1);

namespace App\Models\Configuracion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PermisoModel extends Model
{
    use HasFactory;

    public $incrementing = true;

    protected $table = 'permiso';

    protected $primaryKey = 'id';

    protected $fillable = [
        'id_rol',
        'menu',
        'view',
        'new',
        'edit',
        'delete',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'id_rol' => 'integer',
        'view' => 'boolean',
        'new' => 'boolean',
        'edit' => 'boolean',
        'delete' => 'boolean',
    ];

    public function rol(): BelongsTo
    {
        return $this->belongsTo(RolModel::class, 'id_rol');
    }
}
