<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Support\AppContext;

final readonly class SecuenciaService
{
    public function __construct() {}

    // MÉTODO PRIVADO BASE
    private static function buildSecuencia(
        string $table,
        ?string $serie = null,
    ): array {
        if (!Schema::hasTable($table)) {
            return [
                "error" => true,
                "message" => "La tabla '$table' no existe.",
            ];
        }

        $serie = $serie ?? date("Y");

        $row = DB::table($table)
            ->selectRaw("COALESCE(MAX(CAST(numero AS UNSIGNED)), 0) as numero")
            ->where("id_empresa", AppContext::ID_EMPRESA())
            ->where("serie", $serie)
            ->first();

        $actual = $row->numero ?? 0;

        $numero =
            $actual == 0
                ? "00000001"
                : str_pad($actual + 1, 8, "0", STR_PAD_LEFT);

        return [
            "serie" => $serie,
            "numero" => $numero,
        ];
    }

    // 1) SIEMPRE ARRAY
    public static function getSecuenciaArray(
        string $table,
        ?string $serie = null,
    ): array {
        return self::buildSecuencia($table, $serie);
    }

    // 2) SIEMPRE STRING "SERIE-NUMERO"
    public static function getSecuenciaString(
        string $table,
        ?string $serie = null,
    ): string {
        $data = self::buildSecuencia($table, $serie);
        return $data["serie"] . "-" . $data["numero"];
    }
}
