<?php

declare(strict_types=1);

namespace App\Models\Centinela;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class CentinelaModel extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'centinela';

    protected $fillable = [
        'fecha',
        'id_usuario',
        'modulo',
        'descripcion',
        'id_empresa',
        'menu',
        'accion',
    ];

    protected $casts = [
        'id' => 'integer',
        'fecha' => 'datetime',
        'id_usuario' => 'integer',
        'id_empresa' => 'integer',
    ];
}
