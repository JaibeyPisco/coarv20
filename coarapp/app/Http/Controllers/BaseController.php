<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PermisoService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\App;

abstract class BaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    /**
     * Valida si el usuario tiene permisos para realizar una acción específica en un menú
     *
     * @param  string  $menu  Nombre del menú (ej: 'operacion-incidencias')
     * @param  string  $accion  Acción a validar: 'new', 'delete', 'edit', 'view'
     *
     * @throws HttpResponseException Si no tiene permisos
     */
    protected function validarPermisos(string $menu, string $accion): void
    {
        $permisoService = App::make(PermisoService::class);
        $permisoService->validarPermisos($menu, $accion);
    }
}
