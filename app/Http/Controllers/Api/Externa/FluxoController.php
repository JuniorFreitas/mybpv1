<?php

namespace App\Http\Controllers\Api\Externa;

use App\Http\Controllers\Api\Externa\Concerns\ResolvesApiExternaContext;
use App\Http\Controllers\Controller;
use App\Services\ApiExterna\FluxoRhApiService;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FluxoController extends Controller
{
    use ResolvesApiExternaContext;

    public function __construct(private readonly FluxoRhApiService $service)
    {
    }

    public function show(Request $request, string $publicId): JsonResponse
    {
        $id = $this->decodePublicId($publicId);
        if ($id === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $data = $this->service->completo($this->empresaId($request), $id);
        if (! $data) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        return ApiExternaResponse::ok($data);
    }
}
