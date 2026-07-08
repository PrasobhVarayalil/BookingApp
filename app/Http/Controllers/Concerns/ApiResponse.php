<?php

declare(strict_types=1);

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function error(string $message, int $status): JsonResponse
    {
        return response()->json(['message' => $message], $status);
    }

    protected function noContent(): JsonResponse
    {
        return response()->json(status: 204);
    }
}
