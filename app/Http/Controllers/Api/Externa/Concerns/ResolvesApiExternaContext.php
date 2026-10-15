<?php

namespace App\Http\Controllers\Api\Externa\Concerns;

use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Http\Request;

trait ResolvesApiExternaContext
{
    protected function empresaId(Request $request): int
    {
        return (int) $request->user()->empresa_id;
    }

    protected function page(Request $request): int
    {
        return max(1, (int) $request->query('page', 1));
    }

    protected function perPage(Request $request): int
    {
        return ApiExternaResponse::perPage(
            $request->query('per_page') !== null ? (int) $request->query('per_page') : null
        );
    }

    protected function boolQuery(Request $request, string $key): ?bool
    {
        if (! $request->has($key)) {
            return null;
        }

        return filter_var($request->query($key), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    protected function decodePublicId(string $publicId): ?int
    {
        return ApiExternaId::decode($publicId);
    }
}
