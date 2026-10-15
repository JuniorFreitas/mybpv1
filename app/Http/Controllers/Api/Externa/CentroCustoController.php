<?php

namespace App\Http\Controllers\Api\Externa;

use App\Http\Controllers\Api\Externa\Concerns\ResolvesApiExternaContext;
use App\Http\Controllers\Controller;
use App\Services\ApiExterna\CentroCustoApiService;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CentroCustoController extends Controller
{
    use ResolvesApiExternaContext;

    public function __construct(private readonly CentroCustoApiService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filtros = $this->filtros($request);
        if ($filtros === null) {
            return ApiExternaResponse::error('Filtro inválido', 404);
        }

        return ApiExternaResponse::paginated(
            $this->service->listar($this->empresaId($request), $this->page($request), $this->perPage($request), $filtros)
        );
    }

    public function buscar(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return ApiExternaResponse::error('Informe q com ao menos 2 caracteres', 422);
        }
        $filtros = $this->filtros($request);
        if ($filtros === null) {
            return ApiExternaResponse::error('Filtro inválido', 404);
        }
        $paginator = $this->service->buscar($this->empresaId($request), $q, $this->page($request), $this->perPage($request), $filtros);

        return $paginator
            ? ApiExternaResponse::paginated($paginator)
            : ApiExternaResponse::error('Informe q com ao menos 2 caracteres', 422);
    }

    public function show(Request $request, string $publicId): JsonResponse
    {
        return $this->one($request, $publicId, fn ($e, $id) => $this->service->detalhe($e, $id));
    }

    public function filiais(Request $request, string $publicId): JsonResponse
    {
        return $this->one($request, $publicId, fn ($e, $id) => $this->service->filiais($e, $id));
    }

    public function gestores(Request $request, string $publicId): JsonResponse
    {
        return $this->one($request, $publicId, fn ($e, $id) => $this->service->gestores($e, $id));
    }

    private function one(Request $request, string $publicId, callable $fn): JsonResponse
    {
        $id = $this->decodePublicId($publicId);
        if ($id === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }
        $data = $fn($this->empresaId($request), $id);

        return $data ? ApiExternaResponse::ok($data) : ApiExternaResponse::error('Não encontrado', 404);
    }

    private function filtros(Request $request): ?array
    {
        $f = [];
        foreach (['q'] as $k) {
            if (is_string($request->query($k)) && trim($request->query($k)) !== '') {
                $f[$k] = trim($request->query($k));
            }
        }
        foreach (['ativo'] as $k) {
            $b = $this->boolQuery($request, $k);
            if ($b !== null) {
                $f[$k] = $b;
            }
        }

        return $f;
    }
}
