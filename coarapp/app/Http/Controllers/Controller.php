<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;

abstract class Controller extends BaseController
{
    use ValidatesRequests;

    public function successResponse(
        string $message,
        Collection|array $data,
        int $code = 200,
    ): JsonResponse {
        return response()->json([
            "message" => $message,
            "code" => $code,
            "data" => $data,
        ]);
    }

    public function failResponse(string $message, int $code = 400): JsonResponse
    {
        return response()->json([
            "message" => $message,
            "code" => $code,
            "data" => [],
        ]);
    }
}
