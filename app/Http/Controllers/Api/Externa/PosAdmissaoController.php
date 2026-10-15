<?php

namespace App\Http\Controllers\Api\Externa;

use App\Http\Controllers\Api\Externa\Concerns\ResolvesApiExternaContext;
use App\Http\Controllers\Controller;
use App\Services\ApiExterna\PosAdmissaoApiService;
use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosAdmissaoController extends Controller
{
    use ResolvesApiExternaContext;

    public function __construct(private readonly PosAdmissaoApiService $service)
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

    public function show(Request $request, string $publicId): JsonResponse
    {
        return $this->one($request, $publicId, fn ($e, $id) => $this->service->detalhe($e, $id));
    }

    public function entrevistaDesligamento(Request $request, string $publicId): JsonResponse
    {
        return $this->one($request, $publicId, fn ($e, $id) => $this->service->entrevistaDesligamento($e, $id));
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
        foreach (['demitido', 'com_entrevista'] as $k) {
            $b = $this->boolQuery($request, $k);
            if ($b !== null) {
                $f[$k] = $b;
            }
        }
        foreach (['vaga_public_id' => 'vaga_id'] as $qk => $fk) {
            $pid = $request->query($qk);
            if (! is_string($pid) || $pid === '') {
                continue;
            }
            $decoded = ApiExternaId::decode($pid);
            if ($decoded === null) {
                return null;
            }
            $f[$fk] = $decoded;
        }

        return $f;
    }
}
