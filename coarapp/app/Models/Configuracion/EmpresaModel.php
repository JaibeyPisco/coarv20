<?php

declare(strict_types=1);

namespace App\Models\Configuracion;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class EmpresaModel extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'empresa';

    protected $primaryKey = 'id';

    protected $fillable = [
        'numero_documento',
        'razon_social',
        'nombre_comercial',
        'direccion',
        'telefono',
        'email',
        'logo',
        'logo_factura',
    ];

    /**
     * @return HasMany<User>
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'id_empresa');
    }
}
