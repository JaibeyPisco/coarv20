<?php

declare(strict_types=1);

namespace App\Http\Controllers\Configuracion\Estudiante;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Configuracion\Estudiante\ImportacionRequest;
use App\Models\Configuracion\EstudianteModel;
use App\Services\CentinelaService;

final class ImportacionController extends BaseController
{
    public function __construct(
        private readonly CentinelaService $centinelaService,
        private readonly EstudianteModel $estudianteMdel
    ) {}

    public function validar(ImportacionRequest $request)
    {
        // Si llega aquí, todos los datos cumplen las reglas
        $datos = $request->validated()['estudiantes'];

        return response()->json([
            'ok'    => true,
            'datos' => $datos,
        ]);
    }
}
