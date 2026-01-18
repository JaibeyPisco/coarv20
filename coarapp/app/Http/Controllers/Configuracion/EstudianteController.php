<?php

declare(strict_types=1);

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Configuracion\EstudianteStoreRequest;
use App\Http\Requests\Configuracion\EstudianteUpdateRequest;
use App\Models\Configuracion\EstudianteModel;
use App\Models\Configuracion\PadresApoderadosModel;
use App\Services\CentinelaService;
use App\Services\ErrorHandlerService;
use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Throwable;
use App\Support\AppContext;

final class EstudianteController extends BaseController
{
    private const MODULO = "CONFIGURACIÓN";

    private const MENU = "ESTUDIANTES";

    public function __construct(
        private readonly CentinelaService $centinelaService,
    ) {}

    /**
     * Obtiene estudiantes para select con búsqueda
     */
    public function getSelect(Request $request): JsonResponse
    {
        try {
            $buscar = $request->query("buscar", "");

            $estudiantes = EstudianteModel::query()
                ->selectRaw(
                    "id, CONCAT(apellidos, ' ', nombres, ' | DNI ', dni, ' | GRADO Y SECCIÓN ', grado, '° ', seccion) as text"
                )
                ->where(function ($query) use ($buscar) {
                    $query
                        ->where("apellidos", "like", "%{$buscar}%")
                        ->orWhere("nombres", "like", "%{$buscar}%")
                        ->orWhere("dni", "like", "%{$buscar}%");
                })
                ->orderBy("apellidos")
                ->orderBy("nombres")
                ->get();

            return response()->json($estudiantes);
        } catch (Throwable $e) {
            return ErrorHandlerService::handleCrudError(
                $e,
                "obtener",
                "estudiante",
            );
        }
    }

    /**
     * Importa estudiantes desde un archivo Excel
     */
    // public function importar(Request $request): JsonResponse
    // {
    //     $this->validarPermisos("configuracion-estudiante", "new");

    //     try {
    //         $file = $request->file("fileexportar");

    //         if (!$file) {
    //             return response()->json(
    //                 [
    //                     "message" => "No se ha seleccionado ningún archivo.",
    //                 ],
    //                 400,
    //             );
    //         }

    //         $extension = $file->getClientOriginalExtension();
    //         if ($extension !== "xlsx" && $extension !== "xls") {
    //             return response()->json(
    //                 [
    //                     "message" => "El archivo debe ser Excel (.xlsx o .xls)",
    //                 ],
    //                 400,
    //             );
    //         }

    //         $spreadsheet = IOFactory::load($file->getRealPath());
    //         $worksheet = $spreadsheet->getActiveSheet();
    //         $highestRow = $worksheet->getHighestRow();

    //         $contador = 0;

    //         DB::beginTransaction();

    //         for ($i = 4; $i <= $highestRow; $i++) {
    //             $dni = trim((string) $worksheet->getCell("H" . $i)->getValue());

    //             if (empty($dni)) {
    //                 continue;
    //             }

    //             // Procesar fechas
    //             $fechaNacimiento = null;
    //             if ($worksheet->getCell("L" . $i)->getValue()) {
    //                 try {
    //                     $excelDate = $worksheet->getCell("L" . $i)->getValue();
    //                     if (is_numeric($excelDate)) {
    //                         $fechaNacimiento = Date::excelToDateTimeObject(
    //                             $excelDate,
    //                         )->format("Y-m-d");
    //                     }
    //                 } catch (\Exception $e) {
    //                     // Ignorar errores de fecha
    //                 }
    //             }

    //             $fechaCaducidadDNI = null;
    //             if ($worksheet->getCell("W" . $i)->getValue()) {
    //                 try {
    //                     $excelDate = $worksheet->getCell("W" . $i)->getValue();
    //                     if (is_numeric($excelDate)) {
    //                         $fechaCaducidadDNI = Date::excelToDateTimeObject(
    //                             $excelDate,
    //                         )->format("Y-m-d");
    //                     }
    //                 } catch (\Exception $e) {
    //                     // Ignorar errores de fecha
    //                 }
    //             }

    //             // Datos del estudiante
    //             $dataEstudiante = [
    //                 "condicion_estudiante" =>
    //                     trim(
    //                         (string) $worksheet->getCell("B" . $i)->getValue(),
    //                     ) ?:
    //                     "ESTUDIANTE",
    //                 "apellidos" => trim(
    //                     (string) $worksheet->getCell("C" . $i)->getValue(),
    //                 ),
    //                 "nombres" => trim(
    //                     (string) $worksheet->getCell("D" . $i)->getValue(),
    //                 ),
    //                 "obsv" => trim(
    //                     (string) $worksheet->getCell("E" . $i)->getValue(),
    //                 ),
    //                 "grado" => trim(
    //                     (string) $worksheet->getCell("F" . $i)->getValue(),
    //                 ),
    //                 "seccion" => trim(
    //                     (string) $worksheet->getCell("G" . $i)->getValue(),
    //                 ),
    //                 "dni" => $dni,
    //                 "foto" =>
    //                     trim(
    //                         (string) $worksheet->getCell("I" . $i)->getValue(),
    //                     ) ?:
    //                     "sin_imagen.jpg",
    //                 "sexo" => trim(
    //                     (string) $worksheet->getCell("J" . $i)->getValue(),
    //                 ),
    //                 "correo_electronico" => trim(
    //                     (string) $worksheet->getCell("K" . $i)->getValue(),
    //                 ),
    //                 "fecha_nacimiento" => $fechaNacimiento,
    //                 "lav" => trim(
    //                     (string) $worksheet->getCell("M" . $i)->getValue(),
    //                 ),
    //                 "llaves" => trim(
    //                     (string) $worksheet->getCell("N" . $i)->getValue(),
    //                 ),
    //                 "pabellon" => trim(
    //                     (string) $worksheet->getCell("O" . $i)->getValue(),
    //                 ),
    //                 "ala" => trim(
    //                     (string) $worksheet->getCell("P" . $i)->getValue(),
    //                 ),
    //                 "cama_ropero" => trim(
    //                     (string) $worksheet->getCell("Q" . $i)->getValue(),
    //                 ),
    //                 "duchas" => trim(
    //                     (string) $worksheet->getCell("R" . $i)->getValue(),
    //                 ),
    //                 "banos" => trim(
    //                     (string) $worksheet->getCell("S" . $i)->getValue(),
    //                 ),
    //                 "urinarios" => trim(
    //                     (string) $worksheet->getCell("T" . $i)->getValue(),
    //                 ),
    //                 "monitor_acompana" => trim(
    //                     (string) $worksheet->getCell("U" . $i)->getValue(),
    //                 ),
    //                 "lugar_nacimiento" => trim(
    //                     (string) $worksheet->getCell("V" . $i)->getValue(),
    //                 ),
    //                 "fecha_caducidad_dni" => $fechaCaducidadDNI,
    //                 "num_telefonico" => trim(
    //                     (string) $worksheet->getCell("X" . $i)->getValue(),
    //                 ),
    //                 "religion" => trim(
    //                     (string) $worksheet->getCell("Y" . $i)->getValue(),
    //                 ),
    //                 "region_domicilio_actual" => trim(
    //                     (string) $worksheet->getCell("Z" . $i)->getValue(),
    //                 ),
    //                 "provincia_domicilio_actual" => trim(
    //                     (string) $worksheet->getCell("AA" . $i)->getValue(),
    //                 ),
    //                 "distrito_domicilio_actual" => trim(
    //                     (string) $worksheet->getCell("AB" . $i)->getValue(),
    //                 ),
    //                 "direccion_domicilio_actual" => trim(
    //                     (string) $worksheet->getCell("AC" . $i)->getValue(),
    //                 ),
    //                 "referencia_domicilio_actual" => trim(
    //                     (string) $worksheet->getCell("AD" . $i)->getValue(),
    //                 ),
    //             ];

    //             // Verificar si el estudiante ya existe
    //             $estudianteExistente = EstudianteModel::query()
    //                 ->where("dni", $dni)
    //                 ->first();
    //             if ($estudianteExistente) {
    //                 $dataEstudiante["id"] = $estudianteExistente->id;
    //                 $estudiante = $estudianteExistente;
    //                 $estudiante->update($dataEstudiante);
    //                 $idEstudiante = $estudiante->id;
    //             } else {
    //                 $estudiante = EstudianteModel::query()->create(
    //                     $dataEstudiante,
    //                 );
    //                 $idEstudiante = $estudiante->id;
    //             }

    //             // Datos de la madre
    //             $dataMadre = [
    //                 "tipo" => "MADRE",
    //                 "vive" =>
    //                     strtoupper(
    //                         trim(
    //                             (string) $worksheet
    //                                 ->getCell("AE" . $i)
    //                                 ->getValue(),
    //                         ),
    //                     ) === "SI"
    //                         ? 1
    //                         : 0,
    //                 "vive_con_estudiante" =>
    //                     strtoupper(
    //                         trim(
    //                             (string) $worksheet
    //                                 ->getCell("AF" . $i)
    //                                 ->getValue(),
    //                         ),
    //                     ) === "SI"
    //                         ? 1
    //                         : 0,
    //                 "apellidos" => trim(
    //                     (string) $worksheet->getCell("AG" . $i)->getValue(),
    //                 ),
    //                 "nombres" => trim(
    //                     (string) $worksheet->getCell("AH" . $i)->getValue(),
    //                 ),
    //                 "dni" => trim(
    //                     (string) $worksheet->getCell("AI" . $i)->getValue(),
    //                 ),
    //                 "grado_instruccion" => trim(
    //                     (string) $worksheet->getCell("AJ" . $i)->getValue(),
    //                 ),
    //                 "ocupacion_actual" => trim(
    //                     (string) $worksheet->getCell("AK" . $i)->getValue(),
    //                 ),
    //                 "telefono" => trim(
    //                     (string) $worksheet->getCell("AL" . $i)->getValue(),
    //                 ),
    //                 "correo_electronico" => trim(
    //                     (string) $worksheet->getCell("AM" . $i)->getValue(),
    //                 ),
    //                 "motivo_no_vive_con_estudiante" => trim(
    //                     (string) $worksheet->getCell("AN" . $i)->getValue(),
    //                 ),
    //                 "id_estudiante" => $idEstudiante,
    //             ];

    //             $madreExistente = PadresApoderadosModel::query()
    //                 ->where("tipo", "MADRE")
    //                 ->where("dni", $dataMadre["dni"])
    //                 ->where("id_estudiante", $idEstudiante)
    //                 ->first();

    //             if ($madreExistente) {
    //                 $madreExistente->update($dataMadre);
    //             } elseif (!empty($dataMadre["dni"])) {
    //                 PadresApoderadosModel::query()->create($dataMadre);
    //             }

    //             // Datos del padre
    //             $dataPadre = [
    //                 "tipo" => "PADRE",
    //                 "vive" =>
    //                     strtoupper(
    //                         trim(
    //                             (string) $worksheet
    //                                 ->getCell("AO" . $i)
    //                                 ->getValue(),
    //                         ),
    //                     ) === "SI"
    //                         ? 1
    //                         : 0,
    //                 "vive_con_estudiante" =>
    //                     strtoupper(
    //                         trim(
    //                             (string) $worksheet
    //                                 ->getCell("AP" . $i)
    //                                 ->getValue(),
    //                         ),
    //                     ) === "SI"
    //                         ? 1
    //                         : 0,
    //                 "apellidos" => trim(
    //                     (string) $worksheet->getCell("AQ" . $i)->getValue(),
    //                 ),
    //                 "nombres" => trim(
    //                     (string) $worksheet->getCell("AR" . $i)->getValue(),
    //                 ),
    //                 "dni" => trim(
    //                     (string) $worksheet->getCell("AS" . $i)->getValue(),
    //                 ),
    //                 "grado_instruccion" => trim(
    //                     (string) $worksheet->getCell("AT" . $i)->getValue(),
    //                 ),
    //                 "ocupacion_actual" => trim(
    //                     (string) $worksheet->getCell("AU" . $i)->getValue(),
    //                 ),
    //                 "correo_electronico" => trim(
    //                     (string) $worksheet->getCell("AV" . $i)->getValue(),
    //                 ),
    //                 "telefono" => trim(
    //                     (string) $worksheet->getCell("AW" . $i)->getValue(),
    //                 ),
    //                 "motivo_no_vive_con_estudiante" => trim(
    //                     (string) $worksheet->getCell("AX" . $i)->getValue(),
    //                 ),
    //                 "id_estudiante" => $idEstudiante,
    //             ];

    //             $padreExistente = PadresApoderadosModel::query()
    //                 ->where("tipo", "PADRE")
    //                 ->where("dni", $dataPadre["dni"])
    //                 ->where("id_estudiante", $idEstudiante)
    //                 ->first();

    //             if ($padreExistente) {
    //                 $padreExistente->update($dataPadre);
    //             } elseif (!empty($dataPadre["dni"])) {
    //                 PadresApoderadosModel::query()->create($dataPadre);
    //             }

    //             // Datos del apoderado rol padre/madre
    //             $dataApoderadoRolPadreMadre = [
    //                 "tipo" => "PADRE_APODERADO",
    //                 "parentesco_estudiante" => trim(
    //                     (string) $worksheet->getCell("AY" . $i)->getValue(),
    //                 ),
    //                 "apellidos" => trim(
    //                     (string) $worksheet->getCell("AZ" . $i)->getValue(),
    //                 ),
    //                 "nombres" => trim(
    //                     (string) $worksheet->getCell("BA" . $i)->getValue(),
    //                 ),
    //                 "dni" => trim(
    //                     (string) $worksheet->getCell("BB" . $i)->getValue(),
    //                 ),
    //                 "telefono" => trim(
    //                     (string) $worksheet->getCell("BC" . $i)->getValue(),
    //                 ),
    //                 "tipo_familia" => trim(
    //                     (string) $worksheet->getCell("BD" . $i)->getValue(),
    //                 ),
    //                 "id_estudiante" => $idEstudiante,
    //             ];

    //             $apoderadoRolExistente = PadresApoderadosModel::query()
    //                 ->where("tipo", "PADRE_APODERADO")
    //                 ->where("dni", $dataApoderadoRolPadreMadre["dni"])
    //                 ->where("id_estudiante", $idEstudiante)
    //                 ->first();

    //             if ($apoderadoRolExistente) {
    //                 $apoderadoRolExistente->update($dataApoderadoRolPadreMadre);
    //             } elseif (!empty($dataApoderadoRolPadreMadre["dni"])) {
    //                 PadresApoderadosModel::query()->create(
    //                     $dataApoderadoRolPadreMadre,
    //                 );
    //             }

    //             // Procesar apoderados (múltiples)
    //             $apoderadosCols = [
    //                 ["BE", "BF", "BG", "BH", "BI", "BJ"], // Apoderado 1
    //                 ["BK", "BL", "BM", "BN", "BO", "BP"], // Apoderado 2
    //                 ["BQ", "BR", "BS", "BT", "BU", "BV"], // Apoderado 3
    //                 ["BW", "BX", "BY", "BZ", "CA", "CB"], // Apoderado 4
    //                 ["CC", "CD", "CE", "CF", "CG", "CH"], // Apoderado 5
    //                 ["CI", "CJ", "CK", "CL", "CM", "CN"], // Apoderado 6
    //                 ["CO", "CP", "CQ", "CR", "CS", "CT"], // Apoderado 7
    //             ];

    //             $dataApoderados = [];
    //             foreach ($apoderadosCols as $cols) {
    //                 $dniApoderado = trim(
    //                     (string) $worksheet->getCell($cols[2] . $i)->getValue(),
    //                 );
    //                 if (!empty($dniApoderado)) {
    //                     $dataApoderados[] = [
    //                         "tipo" => "APODERADO",
    //                         "apellidos" => trim(
    //                             (string) $worksheet
    //                                 ->getCell($cols[0] . $i)
    //                                 ->getValue(),
    //                         ),
    //                         "nombres" => trim(
    //                             (string) $worksheet
    //                                 ->getCell($cols[1] . $i)
    //                                 ->getValue(),
    //                         ),
    //                         "dni" => $dniApoderado,
    //                         "telefono" => trim(
    //                             (string) $worksheet
    //                                 ->getCell($cols[3] . $i)
    //                                 ->getValue(),
    //                         ),
    //                         "parentesco_estudiante" => trim(
    //                             (string) $worksheet
    //                                 ->getCell($cols[4] . $i)
    //                                 ->getValue(),
    //                         ),
    //                         "fl_legalizado" =>
    //                             strtoupper(
    //                                 trim(
    //                                     (string) $worksheet
    //                                         ->getCell($cols[5] . $i)
    //                                         ->getValue(),
    //                                 ),
    //                             ) === "SI"
    //                                 ? 1
    //                                 : 0,
    //                         "id_estudiante" => $idEstudiante,
    //                     ];
    //                 }
    //             }

    //             // Guardar apoderados
    //             if (!empty($dataApoderados)) {
    //                 $existingApoderados = PadresApoderadosModel::query()
    //                     ->where("id_estudiante", $idEstudiante)
    //                     ->where("tipo", "APODERADO")
    //                     ->get();

    //                 $existingIds = $existingApoderados->pluck("id")->toArray();
    //                 $receivedIds = [];

    //                 foreach ($dataApoderados as $apoderadoData) {
    //                     $apoderadoExistente = PadresApoderadosModel::query()
    //                         ->where("tipo", "APODERADO")
    //                         ->where("dni", $apoderadoData["dni"])
    //                         ->where("id_estudiante", $idEstudiante)
    //                         ->first();

    //                     if ($apoderadoExistente) {
    //                         $apoderadoExistente->update($apoderadoData);
    //                         $receivedIds[] = $apoderadoExistente->id;
    //                     } else {
    //                         $nuevoApoderado = PadresApoderadosModel::query()->create(
    //                             $apoderadoData,
    //                         );
    //                         $receivedIds[] = $nuevoApoderado->id;
    //                     }
    //                 }

    //                 // Eliminar apoderados que ya no están en el Excel
    //                 $idsToDelete = array_diff($existingIds, $receivedIds);
    //                 if (!empty($idsToDelete)) {
    //                     PadresApoderadosModel::query()
    //                         ->whereIn("id", $idsToDelete)
    //                         ->delete();
    //                 }
    //             }

    //             $contador++;
    //         }

    //         DB::commit();

    //         $this->centinelaService->registrarCambio(
    //             accion: "IMPORTAR",
    //             descripcion: "Se importaron {$contador} estudiantes",
    //             menu: self::MENU,
    //             modulo: self::MODULO,
    //         );

    //         return response()->json(
    //             [
    //                 "message" => "Se han importado correctamente {$contador} registros.",
    //             ],
    //             200,
    //         );
    //     } catch (Throwable $e) {
    //         DB::rollBack();
    //         return ErrorHandlerService::handleCrudError(
    //             $e,
    //             "importar",
    //             "estudiante",
    //         );
    //     }
    // }

    public function index(): JsonResponse
    {
        $this->validarPermisos("configuracion-estudiante", "view");

        try {
            $estudiantes = EstudianteModel::query()
                ->selectRaw(
                    'id, CONCAT(grado, "° ", seccion) as grado_seccion, CONCAT(apellidos, " ", nombres) as estudiante, correo_electronico, dni, condicion_estudiante'
                )
                ->orderByDesc("id")
                ->get();

            return response()->json([
                "data" => $estudiantes,
            ]);
        } catch (Throwable $e) {
            return ErrorHandlerService::handleCrudError(
                $e,
                "obtener",
                "estudiante",
            );
        }
    }

    /**
     * Obtiene un estudiante con todos sus datos relacionados
     */
    public function show(int $estudiante): JsonResponse
    {
        $this->validarPermisos("configuracion-estudiante", "view");

        try {
            $estudianteModel = EstudianteModel::query()
                ->where("id", $estudiante)
                ->firstOrFail();

            $estudianteData = $estudianteModel->toArray();

            // Obtener apoderados
            $estudianteData["apoderados"] = PadresApoderadosModel::query()
                ->where("id_estudiante", $estudiante)
                ->where("tipo", "APODERADO")
                ->get()
                ->toArray();

            // Obtener padre
            $estudianteData["padre"] = PadresApoderadosModel::query()
                ->where("id_estudiante", $estudiante)
                ->where("tipo", "PADRE")
                ->first();

            // Obtener madre
            $estudianteData["madre"] = PadresApoderadosModel::query()
                ->where("id_estudiante", $estudiante)
                ->where("tipo", "MADRE")
                ->first();

            // Obtener padre_apoderado
            $estudianteData["padre_apoderado"] = PadresApoderadosModel::query()
                ->where("id_estudiante", $estudiante)
                ->where("tipo", "PADRE_APODERADO")
                ->first();

            return response()->json($estudianteData);
        } catch (Throwable $e) {
            return ErrorHandlerService::handleCrudError(
                $e,
                "obtener",
                "estudiante",
            );
        }
    }

    /**
     * Guarda o actualiza un estudiante
     * Si viene 'id' en el request, actualiza; si no, crea uno nuevo
     */
    public function save(Request $request): JsonResponse
    {
        $dataRequest = $request->all();
        $tieneId = isset($dataRequest["id"]) && !empty($dataRequest["id"]);

        // Validar permisos según si es creación o actualización
        if ($tieneId) {
            $this->validarPermisos("configuracion-estudiante", "edit");
        } else {
            $this->validarPermisos("configuracion-estudiante", "new");
        }

        try {
            $estudianteModel = null;

            // Validar según si es creación o actualización
            if ($tieneId) {
                $estudianteModel = EstudianteModel::query()->findOrFail(
                    $dataRequest["id"],
                );
                // Obtener reglas de validación y ajustar para el ID actual
                $updateRequest = new EstudianteUpdateRequest();
                $rules = $updateRequest->rules();
                // Reemplazar el route parameter con el modelo encontrado
                $rules["dni"][3] = \Illuminate\Validation\Rule::unique(
                    "estudiante",
                    "dni",
                )->ignore($estudianteModel->id);
                $validator = Validator::make($dataRequest, $rules);
                $payload = $validator->validate();
            } else {
                $storeRequest = new EstudianteStoreRequest();
                $validator = Validator::make($dataRequest, $storeRequest->rules());
                $payload = $validator->validate();
            }

            $estudiante = DB::transaction(function () use (
                $payload,
                $request,
                $tieneId,
                $dataRequest,
                $estudianteModel,
            ) {
                $estudiante = null;
                $fotoAnterior = null;

                // Si es actualización, usar el estudiante ya obtenido
                if ($tieneId) {
                    $estudiante = $estudianteModel;
                    $fotoAnterior = $estudiante->foto;
                }

                // Manejar imagen
                $foto = ImageUploadService::upload(
                    $request->file("foto"),
                    "estudiante",
                    $fotoAnterior,
                );

                if ($foto) {
                    $payload["foto"] = $foto;
                } elseif (!$tieneId) {
                    // Solo asignar foto por defecto si es creación
                    $payload["foto"] = "sin_imagen.jpg";
                }

                // Generar código de estudiante si no existe (solo en creación)
                if (!$tieneId && empty($payload["codigo_estudiante"])) {
                    $payload["codigo_estudiante"] =
                        $this->regenerateCodigoEstudiante();
                }

                // Crear o actualizar estudiante
                if ($tieneId) {
                    $estudiante->update($payload);
                    $idEstudiante = $estudiante->id;
                } else {
                    $estudiante = EstudianteModel::query()->create($payload);
                    $idEstudiante = $estudiante->id;
                }

                // Guardar/actualizar padre
                $this->savePadre($dataRequest, $idEstudiante);

                // Guardar/actualizar madre
                $this->saveMadre($dataRequest, $idEstudiante);

                // Guardar/actualizar apoderado rol padre/madre
                $this->saveApoderadoRolPadreMadre(
                    $dataRequest,
                    $idEstudiante,
                );

                // Guardar/actualizar apoderados
                $this->saveApoderados($dataRequest, $idEstudiante);

                return $estudiante;
            });

            $this->centinelaService->registrarCambio(
                accion: $tieneId ? "EDITAR" : "NUEVO",
                descripcion:
                    $estudiante->nombres . " " . $estudiante->apellidos,
                menu: self::MENU,
                modulo: self::MODULO,
            );

            $mensaje = $tieneId
                ? "Estudiante actualizado correctamente."
                : "Estudiante registrado correctamente.";

            return response()->json(
                [
                    "message" => $mensaje,
                    "data" => $estudiante->fresh(),
                ],
                $tieneId ? 200 : 201,
            );
        } catch (Throwable $e) {
            return ErrorHandlerService::handleCrudError(
                $e,
                $tieneId ? "actualizar" : "crear",
                "estudiante",
            );
        }
    }

    /**
     * Crea un nuevo estudiante (mantiene compatibilidad)
     */
    public function store(EstudianteStoreRequest $request): JsonResponse
    {
        return $this->save($request);
    }

    /**
     * Actualiza un estudiante existente (mantiene compatibilidad)
     */
    public function update(
        EstudianteUpdateRequest $request,
        EstudianteModel $estudiante,
    ): JsonResponse {
        $request->merge(["id" => $estudiante->id]);
        return $this->save($request);
    }

    /**
     * Elimina un estudiante
     */
    public function destroy(EstudianteModel $estudiante): JsonResponse
    {
        $this->validarPermisos("configuracion-estudiante", "delete");

        try {
            $nombreCompleto =
                $estudiante->nombres . " " . $estudiante->apellidos;

            DB::transaction(function () use ($estudiante) {
                // Eliminar padres y apoderados relacionados
                PadresApoderadosModel::query()
                    ->where("id_estudiante", $estudiante->id)
                    ->delete();

                // Eliminar estudiante
                $estudiante->delete();
            });

            $this->centinelaService->registrarCambio(
                accion: "ELIMINAR",
                descripcion: $nombreCompleto,
                menu: self::MENU,
                modulo: self::MODULO,
            );

            return response()->json([
                "message" => "Estudiante eliminado correctamente.",
            ]);
        } catch (Throwable $e) {
            return ErrorHandlerService::handleCrudError(
                $e,
                "eliminar",
                "estudiante",
            );
        }
    }

    /**
     * Guarda o actualiza los datos del padre
     */
    private function savePadre(array $data, int $idEstudiante): void
    {
        $padreData = [
            "tipo" => "PADRE",
            "vive" => isset($data["padre_vivo"]) ? 1 : 0,
            "vive_con_estudiante" => isset($data["padre_con_estudiante"])
                ? 1
                : 0,
            "nombres" => $data["nombres_padre"] ?? null,
            "apellidos" => $data["apellidos_padre"] ?? null,
            "dni" => $data["dni_padre"] ?? null,
            "grado_instruccion" => $data["grado_instruccion_padre"] ?? null,
            "ocupacion_actual" => $data["ocupacion_actual_padre"] ?? null,
            "telefono" => $data["num_celular_padre"] ?? null,
            "correo_electronico" => $data["correo_electronico_padre"] ?? null,
            "motivo_no_vive_con_estudiante" =>
                $data["motivo_padre_no_vive_con_estudiante"] ?? null,
            "id_estudiante" => $idEstudiante,
        ];

        if (isset($data["padre_id"]) && !empty($data["padre_id"])) {
            PadresApoderadosModel::query()
                ->where("id", $data["padre_id"])
                ->update($padreData);
        } elseif (!empty($padreData["dni"])) {
            PadresApoderadosModel::query()->create($padreData);
        }
    }

    /**
     * Guarda o actualiza los datos de la madre
     */
    private function saveMadre(array $data, int $idEstudiante): void
    {
        $madreData = [
            "tipo" => "MADRE",
            "vive" => isset($data["madre_viva"]) ? 1 : 0,
            "vive_con_estudiante" => isset($data["madre_con_estudiante"])
                ? 1
                : 0,
            "nombres" => $data["nombres_madre"] ?? null,
            "apellidos" => $data["apellidos_madre"] ?? null,
            "dni" => $data["dni_madre"] ?? null,
            "grado_instruccion" => $data["grado_instruccion_madre"] ?? null,
            "ocupacion_actual" => $data["ocupacion_actual_madre"] ?? null,
            "telefono" => $data["num_celular_madre"] ?? null,
            "correo_electronico" => $data["correo_electronico_madre"] ?? null,
            "motivo_no_vive_con_estudiante" =>
                $data["motivo_madre_no_vive_con_estudiante"] ?? null,
            "id_estudiante" => $idEstudiante,
        ];

        if (isset($data["madre_id"]) && !empty($data["madre_id"])) {
            PadresApoderadosModel::query()
                ->where("id", $data["madre_id"])
                ->update($madreData);
        } elseif (!empty($madreData["dni"])) {
            PadresApoderadosModel::query()->create($madreData);
        }
    }

    /**
     * Guarda o actualiza los datos del apoderado rol padre/madre
     */
    private function saveApoderadoRolPadreMadre(
        array $data,
        int $idEstudiante,
    ): void {
        $apoderadoRolData = [
            "tipo" => "PADRE_APODERADO",
            "parentesco_estudiante" =>
                $data["parentesco_con_apoderado"] ?? null,
            "nombres" => $data["nombres_apoderado"] ?? null,
            "apellidos" => $data["apellidos_apoderado"] ?? null,
            "dni" => $data["dni_apoderado"] ?? null,
            "telefono" => $data["num_celular_apoderado"] ?? null,
            "tipo_familia" => $data["tipo_familia"] ?? null,
            "id_estudiante" => $idEstudiante,
        ];

        if (
            isset($data["apoderado_rol_padre_madre_id"]) &&
            !empty($data["apoderado_rol_padre_madre_id"])
        ) {
            PadresApoderadosModel::query()
                ->where("id", $data["apoderado_rol_padre_madre_id"])
                ->update($apoderadoRolData);
        } elseif (!empty($apoderadoRolData["dni"])) {
            PadresApoderadosModel::query()->create($apoderadoRolData);
        }
    }

    /**
     * Guarda o actualiza los apoderados
     */
    private function saveApoderados(array $data, int $idEstudiante): void
    {
        if (!isset($data["apoderados"])) {
            return;
        }

        $apoderados = json_decode($data["apoderados"], true);

        if (!is_array($apoderados)) {
            return;
        }

        // Obtener IDs existentes
        $existingIds = PadresApoderadosModel::query()
            ->where("id_estudiante", $idEstudiante)
            ->where("tipo", "APODERADO")
            ->pluck("id")
            ->toArray();

        $receivedIds = [];

        foreach ($apoderados as $apoderado) {
            $apoderadoData = [
                "tipo" => "APODERADO",
                "apellidos" => $apoderado["apellidos"] ?? null,
                "nombres" => $apoderado["nombres"] ?? null,
                "dni" => $apoderado["dni"] ?? null,
                "telefono" => $apoderado["numero_telefonico"] ?? null,
                "parentesco_estudiante" =>
                    $apoderado["grado_parentesco"] ?? null,
                "fl_legalizado" => isset($apoderado["legalizado"]) &&
                $apoderado["legalizado"]
                    ? 1
                    : 0,
                "id_estudiante" => $idEstudiante,
            ];

            if (
                isset($apoderado["id"]) &&
                !empty($apoderado["id"]) &&
                is_numeric($apoderado["id"])
            ) {
                $receivedIds[] = $apoderado["id"];
                PadresApoderadosModel::query()
                    ->where("id", $apoderado["id"])
                    ->update($apoderadoData);
            } else {
                $nuevoApoderado = PadresApoderadosModel::query()->create(
                    $apoderadoData,
                );
                $receivedIds[] = $nuevoApoderado->id;
            }
        }

        // Eliminar apoderados que ya no están en la lista
        $idsToDelete = array_diff($existingIds, $receivedIds);
        if (!empty($idsToDelete)) {
            PadresApoderadosModel::query()
                ->whereIn("id", $idsToDelete)
                ->delete();
        }
    }

    /**
     * Genera un código aleatorio para el estudiante
     */
    private function regenerateCodigoEstudiante(): string
    {
        $chars =
            "0123456789abcdefghijklmnopqrstuvwxyz!@#$%^&*()ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $passwordLength = 12;
        $password = "";

        for ($i = 0; $i < $passwordLength; $i++) {
            $randomNumber = mt_rand(0, strlen($chars) - 1);
            $password .= substr($chars, $randomNumber, 1);
        }

        return $password;
    }
}
