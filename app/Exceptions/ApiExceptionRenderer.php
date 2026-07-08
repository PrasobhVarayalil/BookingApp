<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Exceptions\Contracts\HasHttpStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Turns any exception thrown on an API route into a consistent
 * { "message": "..." } JSON body with the right status code. Registered from
 * bootstrap/app.php, so controllers never need their own try/catch.
 */
class ApiExceptionRenderer
{
    public function __invoke(Throwable $e, Request $request): ?JsonResponse
    {
        if (! $request->is('api/*')) {
            return null;
        }

        // Validation already returns its own {message, errors} payload.
        if ($e instanceof ValidationException) {
            return null;
        }

        [$status, $message] = $this->map($e);

        return response()->json(['message' => $message], $status);
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function map(Throwable $e): array
    {
        return match (true) {
            $e instanceof HasHttpStatus => [$e->httpStatus(), $e->getMessage()],
            $e instanceof AuthenticationException => [Response::HTTP_UNAUTHORIZED, 'Unauthenticated.'],
            $e instanceof AuthorizationException => [Response::HTTP_FORBIDDEN, 'This action is not authorized.'],
            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException => [Response::HTTP_NOT_FOUND, 'Resource not found.'],
            $e instanceof HttpExceptionInterface => [$e->getStatusCode(), $e->getMessage() ?: 'Request failed.'],
            default => [Response::HTTP_INTERNAL_SERVER_ERROR, $this->safeMessage($e)],
        };
    }

    private function safeMessage(Throwable $e): string
    {
        return config('app.debug') ? $e->getMessage() : 'Something went wrong. Please try again later.';
    }
}
