<?php

declare(strict_types=1);

namespace App\Models\Static;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class StaticDocumentoModel extends Model
{
    use HasFactory;

    protected $table = 'static_documento';

    protected $fillable = [
        'nombre',
        'codigo_sunat',
        'tipo',
    ];

    protected $casts = [
        'id' => 'integer',
    ];
}
