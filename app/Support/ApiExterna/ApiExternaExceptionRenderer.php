<?php

namespace App\Support\ApiExterna;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Envelope JSON padronizado para /api/v3 (success/msg), sem página HTML.
 */
final class ApiExternaExceptionRenderer
{
    public static function matches(Request $request): bool
    {
        return $request->is('api/v3') || $request->is('api/v3/*');
    }

    public static function render(Throwable $e, Request $request): ?JsonResponse
    {
        if (! self::matches($request)) {
            return null;
        }

        if ($e instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'msg' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);
        }

        if ($e instanceof AuthenticationException) {
            return ApiExternaResponse::error('Não autorizado', 401);
        }

        if ($e instanceof AuthorizationException) {
            return ApiExternaResponse::error('Escopo insuficiente ou acesso negado', 403);
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return ApiExternaResponse::error('Recurso não encontrado', 404);
        }

        if ($e instanceof ThrottleRequestsException) {
            return ApiExternaResponse::error('Muitas requisições. Tente novamente em breve.', 429);
        }

        $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

        if ($status >= 500) {
            $msg = config('app.debug')
                ? ($e->getMessage() !== '' ? $e->getMessage() : 'Erro interno do servidor')
                : 'Erro interno do servidor';

            return ApiExternaResponse::error($msg, 500);
        }

        $msg = $e->getMessage() !== '' ? $e->getMessage() : 'Erro na requisição';

        return ApiExternaResponse::error($msg, $status);
    }
}
