<?php

namespace App\Http\Controllers\Api\Externa;

use App\Http\Controllers\Api\Externa\Concerns\ResolvesApiExternaContext;
use App\Http\Controllers\Controller;
use App\Services\ApiExterna\AvaliacaoApiService;
use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvaliacaoController extends Controller
{
    use ResolvesApiExternaContext;

    public function __construct(private readonly AvaliacaoApiService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return ApiExternaResponse::paginated(
            $this->service->listar(
                $this->empresaId($request),
                $this->page($request),
                $this->perPage($request),
                is_string($request->query('status')) ? $request->query('status') : null,
                $this->boolQuery($request, 'ativo'),
                $this->filtrosCampanha($request)
            )
        );
    }

    public function tipos(Request $request): JsonResponse
    {
        $ativo = $request->has('ativo')
            ? $this->boolQuery($request, 'ativo')
            : true;

        return ApiExternaResponse::ok(
            $this->service->tipos(
                $this->empresaId($request),
                $ativo,
                $this->boolQuery($request, 'tipo_pj')
            )
        );
    }

    public function tiposAvaliador(Request $request): JsonResponse
    {
        $ativo = $request->has('ativo')
            ? $this->boolQuery($request, 'ativo')
            : true;

        return ApiExternaResponse::ok(
            $this->service->tiposAvaliador(
                $this->empresaId($request),
                $ativo,
                $this->boolQuery($request, 'tipo_pj')
            )
        );
    }

    public function topicos(Request $request, string $tipoPublicId): JsonResponse
    {
        $tipoId = $this->decodePublicId($tipoPublicId);
        if ($tipoId === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $ativo = $request->has('ativo')
            ? $this->boolQuery($request, 'ativo')
            : true;

        $data = $this->service->topicosDoTipo($this->empresaId($request), $tipoId, $ativo);
        if (! $data) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        return ApiExternaResponse::ok($data);
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

        $filtros = $this->filtrosFeedback($request);
        if ($filtros === null) {
            return ApiExternaResponse::error('Filtro inválido', 404);
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

    public function showFeedback(Request $request, string $publicId, string $feedbackPublicId): JsonResponse
    {
        $avaliacaoId = $this->decodePublicId($publicId);
        $feedbackId = $this->decodePublicId($feedbackPublicId);
        if ($avaliacaoId === null || $feedbackId === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $data = $this->service->feedbackDetalhe(
            $this->empresaId($request),
            $avaliacaoId,
            $feedbackId
        );

        if (! $data) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        return ApiExternaResponse::ok($data);
    }

    public function feedbackResultado(Request $request, string $publicId, string $feedbackPublicId): JsonResponse
    {
        $avaliacaoId = $this->decodePublicId($publicId);
        $feedbackId = $this->decodePublicId($feedbackPublicId);
        if ($avaliacaoId === null || $feedbackId === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $data = $this->service->feedbackResultado(
            $this->empresaId($request),
            $avaliacaoId,
            $feedbackId
        );

        if (! $data) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        return ApiExternaResponse::ok($data);
    }

    public function colaboradorFeedbacks(
        Request $request,
        string $publicId,
        string $funcionarioPublicId
    ): JsonResponse {
        $avaliacaoId = $this->decodePublicId($publicId);
        $funcionarioId = $this->decodePublicId($funcionarioPublicId);
        if ($avaliacaoId === null || $funcionarioId === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $data = $this->service->colaboradorFeedbacks(
            $this->empresaId($request),
            $avaliacaoId,
            $funcionarioId
        );

        if (! $data) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        return ApiExternaResponse::ok($data);
    }

    /**
     * @return array{q?:string,ano_avaliacao?:int,tipo_pj?:?bool,avaliacao_tipo_id?:int}
     */
    private function filtrosCampanha(Request $request): array
    {
        $filtros = [];

        $q = $request->query('q');
        if (is_string($q) && trim($q) !== '') {
            $filtros['q'] = trim($q);
        }

        if ($request->filled('ano_avaliacao')) {
            $filtros['ano_avaliacao'] = (int) $request->query('ano_avaliacao');
        }

        $tipoPj = $this->boolQuery($request, 'tipo_pj');
        if ($tipoPj !== null) {
            $filtros['tipo_pj'] = $tipoPj;
        }

        $tipoPublicId = $request->query('avaliacao_tipo_public_id');
        if (is_string($tipoPublicId) && $tipoPublicId !== '') {
            $tipoId = ApiExternaId::decode($tipoPublicId);
            if ($tipoId !== null) {
                $filtros['avaliacao_tipo_id'] = $tipoId;
            }
        }

        return $filtros;
    }

    /**
     * @return array<string, mixed>|null  null = filtro public_id inválido
     */
    private function filtrosFeedback(Request $request): ?array
    {
        $filtros = [];

        if (is_string($request->query('status')) && $request->query('status') !== '') {
            $filtros['status'] = $request->query('status');
        }

        if (is_string($request->query('origem_feedback')) && $request->query('origem_feedback') !== '') {
            $filtros['origem_feedback'] = $request->query('origem_feedback');
        }

        $principal = $this->boolQuery($request, 'principal');
        if ($principal !== null) {
            $filtros['principal'] = $principal;
        }

        $q = $request->query('q');
        if (is_string($q) && trim($q) !== '') {
            $filtros['q'] = trim($q);
        }

        foreach ([
            'funcionario_public_id' => 'funcionario_id',
            'avaliador_public_id' => 'avaliador_id',
            'tipo_avaliador_public_id' => 'tipo_avaliador_id',
        ] as $queryKey => $filterKey) {
            $pid = $request->query($queryKey);
            if (! is_string($pid) || $pid === '') {
                continue;
            }
            $decoded = ApiExternaId::decode($pid);
            if ($decoded === null) {
                return null;
            }
            $filtros[$filterKey] = $decoded;
        }

        return $filtros;
    }
}
