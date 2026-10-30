<?php

namespace App\Http\Controllers\Api\Externa;

use App\Http\Controllers\Api\Externa\Concerns\ResolvesApiExternaContext;
use App\Http\Controllers\Controller;
use App\Services\ApiExterna\WeeklyApiService;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeeklyController extends Controller
{
    use ResolvesApiExternaContext;

    public function __construct(private readonly WeeklyApiService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return ApiExternaResponse::paginated(
            $this->service->listar(
                $this->empresaId($request),
                $this->page($request),
                $this->perPage($request),
                $this->filtrosQuadro($request)
            )
        );
    }

    public function show(Request $request, string $publicId): JsonResponse
    {
        return $this->one($request, $publicId, fn (int $empresaId, int $id) => $this->service->detalhe($empresaId, $id));
    }

    public function listas(Request $request, string $publicId): JsonResponse
    {
        return $this->one($request, $publicId, fn (int $empresaId, int $id) => $this->service->listas($empresaId, $id));
    }

    public function tarefas(Request $request, string $publicId, string $listaPublicId): JsonResponse
    {
        $quadroId = $this->decodePublicId($publicId);
        $listaId = $this->decodePublicId($listaPublicId);
        if ($quadroId === null || $listaId === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $pagina = $this->service->tarefas(
            $this->empresaId($request),
            $quadroId,
            $listaId,
            $this->page($request),
            $this->perPage($request),
            $this->filtrosTarefa($request)
        );

        return $pagina
            ? ApiExternaResponse::paginated($pagina)
            : ApiExternaResponse::error('Não encontrado', 404);
    }

    public function showTarefa(Request $request, string $publicId, string $tarefaPublicId): JsonResponse
    {
        $quadroId = $this->decodePublicId($publicId);
        $tarefaId = $this->decodePublicId($tarefaPublicId);
        if ($quadroId === null || $tarefaId === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $data = $this->service->detalheTarefa($this->empresaId($request), $quadroId, $tarefaId);

        return $data
            ? ApiExternaResponse::ok($data)
            : ApiExternaResponse::error('Não encontrado', 404);
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

    /**
     * @return array<string, string>
     */
    private function filtrosQuadro(Request $request): array
    {
        $filtros = [];
        $q = $request->query('q');
        if (is_string($q) && trim($q) !== '') {
            $filtros['q'] = trim($q);
        }

        return $filtros;
    }

    /**
     * @return array<string, mixed>
     */
    private function filtrosTarefa(Request $request): array
    {
        $filtros = [];
        $q = $request->query('q');
        if (is_string($q) && trim($q) !== '') {
            $filtros['q'] = trim($q);
        }

        if ($request->has('concluido')) {
            $concluido = $this->boolQuery($request, 'concluido');
            if ($concluido !== null) {
                $filtros['concluido'] = $concluido;
            }
        }

        return $filtros;
    }
}
