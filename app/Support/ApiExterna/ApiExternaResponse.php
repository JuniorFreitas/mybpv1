<?php

namespace App\Support\ApiExterna;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

final class ApiExternaResponse
{
    public static function ok(mixed $data, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
        ], $status);
    }

    public static function paginated(LengthAwarePaginator $paginator, array $context = []): JsonResponse
    {
        $payload = [
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ];

        if ($context !== []) {
            $payload['context'] = $context;
        }

        return response()->json($payload);
    }

    public static function error(string $msg, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'msg' => $msg,
        ], $status);
    }

    public static function mascararCpf(?string $cpf): ?string
    {
        if ($cpf === null || $cpf === '') {
            return $cpf;
        }

        $digits = preg_replace('/\D+/', '', $cpf) ?? '';
        if (strlen($digits) < 4) {
            return '***.***.***-**';
        }

        return '***.***.***-' . substr($digits, -2);
    }

    public static function perPage(?int $perPage): int
    {
        $max = (int) config('services.api_externa.max_per_page', 50);
        $value = $perPage ?: $max;

        return max(1, min($max, $value));
    }
}
