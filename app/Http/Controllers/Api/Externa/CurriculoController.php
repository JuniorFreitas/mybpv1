<?php

namespace App\Http\Controllers\Api\Externa;

use App\Http\Controllers\Api\Externa\Concerns\ResolvesApiExternaContext;
use App\Http\Controllers\Controller;
use App\Services\ApiExterna\CurriculoApiService;
use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurriculoController extends Controller
{
    use ResolvesApiExternaContext;

    public function __construct(private readonly CurriculoApiService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $filtros = $this->filtrosListagem($request);
        if ($filtros === null) {
            return ApiExternaResponse::error('Filtro inválido', 404);
        }

        return ApiExternaResponse::paginated(
            $this->service->listar(
                $this->empresaId($request),
                $this->page($request),
                $this->perPage($request),
                $filtros
            )
        );
    }

    public function buscar(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return ApiExternaResponse::error('Informe q com ao menos 2 caracteres', 422);
        }

        $filtros = $this->filtrosListagem($request);
        if ($filtros === null) {
            return ApiExternaResponse::error('Filtro inválido', 404);
        }

        $paginator = $this->service->buscar(
            $this->empresaId($request),
            $q,
            $this->page($request),
            $this->perPage($request),
            $filtros
        );

        if ($paginator === null) {
            return ApiExternaResponse::error('Informe q com ao menos 2 caracteres', 422);
        }

        return ApiExternaResponse::paginated($paginator);
    }

    public function show(Request $request, string $publicId): JsonResponse
    {
        $id = $this->decodePublicId($publicId);
        if ($id === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $data = $this->service->detalhe($this->empresaId($request), $id);
        if (! $data) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        return ApiExternaResponse::ok($data);
    }

    public function feedbacks(Request $request, string $publicId): JsonResponse
    {
        $id = $this->decodePublicId($publicId);
        if ($id === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $filtros = [];
        if (is_string($request->query('status')) && $request->query('status') !== '') {
            $filtros['status'] = $request->query('status');
        }
        if (is_string($request->query('selecionado')) && $request->query('selecionado') !== '') {
            $filtros['selecionado'] = $request->query('selecionado');
        }

        $paginator = $this->service->feedbacks(
            $this->empresaId($request),
            $id,
            $this->page($request),
            $this->perPage($request),
            $filtros
        );

        if ($paginator === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        return ApiExternaResponse::paginated($paginator);
    }

    public function documentos(Request $request, string $publicId): JsonResponse
    {
        return $this->nested($request, $publicId, fn (int $empresaId, int $id) => $this->service->documentos($empresaId, $id));
    }

    public function experiencias(Request $request, string $publicId): JsonResponse
    {
        return $this->nested($request, $publicId, fn (int $empresaId, int $id) => $this->service->experiencias($empresaId, $id));
    }

    /**
     * @param  callable(int, int): (?array)  $resolver
     */
    private function nested(Request $request, string $publicId, callable $resolver): JsonResponse
    {
        $id = $this->decodePublicId($publicId);
        if ($id === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $data = $resolver($this->empresaId($request), $id);
        if (! $data) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        return ApiExternaResponse::ok($data);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function filtrosListagem(Request $request): ?array
    {
        $filtros = [];

        $q = $request->query('q');
        if (is_string($q) && trim($q) !== '') {
            $filtros['q'] = trim($q);
        }

        $cpf = $request->query('cpf');
        if (is_string($cpf) && trim($cpf) !== '') {
            $filtros['cpf'] = trim($cpf);
        }

        $uf = $request->query('uf');
        if (is_string($uf) && trim($uf) !== '') {
            $filtros['uf'] = trim($uf);
        }

        $pcd = $this->boolQuery($request, 'pcd');
        if ($pcd !== null) {
            $filtros['pcd'] = $pcd;
        }

        $lido = $this->boolQuery($request, 'lido');
        if ($lido !== null) {
            $filtros['lido'] = $lido;
        }

        $vagaPid = $request->query('vaga_public_id');
        if (is_string($vagaPid) && $vagaPid !== '') {
            $decoded = ApiExternaId::decode($vagaPid);
            if ($decoded === null) {
                return null;
            }
            $filtros['vaga_id'] = $decoded;
        }

        foreach (['status', 'selecionado'] as $key) {
            $val = $request->query($key);
            if (is_string($val) && $val !== '') {
                $filtros[$key] = $val;
            }
        }

        foreach (['updated_de', 'updated_ate'] as $key) {
            $val = $request->query($key);
            if (is_string($val) && $val !== '') {
                $filtros[$key] = $val;
            }
        }

        return $filtros;
    }
}
