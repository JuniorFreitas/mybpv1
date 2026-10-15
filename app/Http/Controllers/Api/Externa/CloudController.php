<?php

namespace App\Http\Controllers\Api\Externa;

use App\Http\Controllers\Api\Externa\Concerns\ResolvesApiExternaContext;
use App\Http\Controllers\Controller;
use App\Models\Arquivo;
use App\Services\ApiExterna\CloudApiService;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CloudController extends Controller
{
    use ResolvesApiExternaContext;

    public function __construct(private readonly CloudApiService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $q = $request->query('q');
        $q = is_string($q) ? trim($q) : null;

        return ApiExternaResponse::paginated(
            $this->service->listar(
                $this->empresaId($request),
                $this->page($request),
                $this->perPage($request),
                $this->boolQuery($request, 'ativo'),
                $q
            )
        );
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

    public function itens(Request $request, string $publicId): JsonResponse
    {
        $id = $this->decodePublicId($publicId);
        if ($id === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $pastaId = $this->decodeOptionalPublicId($request->query('pasta_public_id'));
        if ($request->filled('pasta_public_id') && $pastaId === null) {
            return ApiExternaResponse::error('Pasta não encontrada', 404);
        }

        $paginator = $this->service->itens(
            $this->empresaId($request),
            $id,
            $this->page($request),
            $this->perPage($request),
            $pastaId,
            $this->filtrosItens($request)
        );

        if ($paginator === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        return ApiExternaResponse::paginated($paginator);
    }

    public function showItem(Request $request, string $publicId, string $itemPublicId): JsonResponse
    {
        $cloudId = $this->decodePublicId($publicId);
        $itemId = $this->decodePublicId($itemPublicId);
        if ($cloudId === null || $itemId === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $data = $this->service->itemDetalhe($this->empresaId($request), $cloudId, $itemId);
        if (! $data) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        return ApiExternaResponse::ok($data);
    }

    public function buscar(Request $request, string $publicId): JsonResponse
    {
        $id = $this->decodePublicId($publicId);
        if ($id === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return ApiExternaResponse::error('Informe q com ao menos 2 caracteres', 422);
        }

        $paginator = $this->service->buscar(
            $this->empresaId($request),
            $id,
            $q,
            $this->page($request),
            $this->perPage($request),
            $this->filtrosItens($request)
        );

        if ($paginator === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        return ApiExternaResponse::paginated($paginator);
    }

    public function pastas(Request $request, string $publicId): JsonResponse
    {
        $id = $this->decodePublicId($publicId);
        if ($id === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $pastaId = $this->decodeOptionalPublicId($request->query('pasta_public_id'));
        if ($request->filled('pasta_public_id') && $pastaId === null) {
            return ApiExternaResponse::error('Pasta não encontrada', 404);
        }

        $data = $this->service->pastas($this->empresaId($request), $id, $pastaId);
        if ($data === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        return ApiExternaResponse::ok($data);
    }

    public function resolverPath(Request $request, string $publicId): JsonResponse
    {
        $id = $this->decodePublicId($publicId);
        if ($id === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $path = trim((string) $request->query('path', ''), '/');
        $data = $this->service->resolverPath($this->empresaId($request), $id, $path);
        if ($data === null) {
            return ApiExternaResponse::error('Caminho não encontrado', 404);
        }

        return ApiExternaResponse::ok($data);
    }

    public function downloadItem(Request $request, string $publicId, string $itemPublicId): Response|JsonResponse
    {
        return $this->responderArquivoItem($request, $publicId, $itemPublicId, 'download');
    }

    public function arquivoItem(Request $request, string $publicId, string $itemPublicId): Response|JsonResponse
    {
        return $this->responderArquivoItem($request, $publicId, $itemPublicId, 'show');
    }

    public function thumbItem(Request $request, string $publicId, string $itemPublicId): Response|JsonResponse
    {
        return $this->responderArquivoItem($request, $publicId, $itemPublicId, 'thumb');
    }

    private function responderArquivoItem(
        Request $request,
        string $publicId,
        string $itemPublicId,
        string $modo
    ): Response|JsonResponse {
        $cloudId = $this->decodePublicId($publicId);
        $itemId = $this->decodePublicId($itemPublicId);
        if ($cloudId === null || $itemId === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $arquivo = $this->service->resolverArquivoDoItem(
            $this->empresaId($request),
            $cloudId,
            $itemId
        );

        if (! $arquivo || empty($arquivo->file)) {
            return ApiExternaResponse::error('Arquivo não encontrado', 404);
        }

        $disco = $arquivo->disco ?: Arquivo::DISCO_CLOUD;
        $path = match ($modo) {
            'thumb' => ($arquivo->imagem && ! empty($arquivo->thumb))
                ? $arquivo->thumb
                : $arquivo->file,
            default => $arquivo->file,
        };

        $response = $modo === 'download'
            ? Arquivo::anexoDownload($disco, $path)
            : Arquivo::anexoShow($disco, $path);

        if (method_exists($response, 'headers')) {
            $response->headers->set('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }

    /**
     * @return array{tipo?:string,aprovado?:?bool,revisado?:?bool,recursivo?:bool}
     */
    private function filtrosItens(Request $request): array
    {
        $filtros = [];
        $tipo = $request->query('tipo');
        if (is_string($tipo) && in_array($tipo, ['pasta', 'arquivo'], true)) {
            $filtros['tipo'] = $tipo;
        }

        $filtros['aprovado'] = $this->boolQuery($request, 'aprovado');
        $filtros['revisado'] = $this->boolQuery($request, 'revisado');

        if ($request->boolean('recursivo')) {
            $filtros['recursivo'] = true;
        }

        $q = $request->query('q');
        if (is_string($q) && trim($q) !== '') {
            $filtros['q'] = trim($q);
        }

        return $filtros;
    }

    private function decodeOptionalPublicId(mixed $value): ?int
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return $this->decodePublicId($value);
    }
}
