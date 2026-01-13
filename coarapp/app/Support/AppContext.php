<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Configuracion\EmpresaModel;
use App\Models\User;

final class AppContext
{
    private static ?User $user = null;

    private static ?int $ID_USUARIO = null;

    private static ?int $ID_EMPRESA = null;

    /**
     * @internal Only call from context middleware.
     */
    public static function boot(?User $user): void
    {
        self::$user = $user;
        self::$ID_USUARIO = $user?->id;
        self::$ID_EMPRESA = $user?->id_empresa;
    }

    public static function user(): ?User
    {
        return self::$user;
    }

    public static function empresa(): ?EmpresaModel
    {
        return self::$user?->empresa;
    }

    public static function ID_USUARIO(): ?int
    {
        return self::$ID_USUARIO;
    }

    public static function ID_EMPRESA(): ?int
    {
        return self::$ID_EMPRESA;
    }

    public static function forget(): void
    {
        self::$user = null;
        self::$ID_USUARIO = null;
        self::$ID_EMPRESA = null;
    }
}
