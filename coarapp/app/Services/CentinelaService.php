<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Centinela\CentinelaModel;
use App\Support\AppContext;
use Illuminate\Database\Eloquent\Model;

final readonly class CentinelaService
{
    public function __construct(
        private CentinelaModel $centinela,
    ) {}

    /**
     * Registra un movimiento en el centinela para auditar las operaciones
     * del sistema. Siempre se completa con el usuario y empresa activos.
     */
    public function registrarCambio(
        string $accion,
        string $descripcion,
        string $menu,
        string $modulo,
    ): Model {
        return $this->centinela->newQuery()->create([
            'modulo' => $modulo,
            'menu' => $menu,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'id_usuario' => AppContext::ID_USUARIO(),
            'id_empresa' => AppContext::ID_EMPRESA(),

        ]);
    }
}
