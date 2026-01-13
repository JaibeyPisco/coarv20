<?php

declare(strict_types=1);

namespace App\Models\Static;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class StaticUbigeoModel extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $table = 'static_ubigeo';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'departamento',
        'provincia',
        'distrito',
    ];
}
