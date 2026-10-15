<?php

namespace App\Http\Controllers\Api\Externa;

use App\Http\Controllers\Api\Externa\Concerns\ResolvesApiExternaContext;
use App\Http\Controllers\Controller;
use App\Models\Arquivo;
use App\Services\ApiExterna\FornecedorApiService;
use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FornecedorController extends Controller
{
    use ResolvesApiExternaContext;

    public function __construct(private readonly FornecedorApiService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return ApiExternaResponse::paginated(
            $this->service->listar(
                $this->empresaId($request),
                $this->page($request),
                $this->perPage($request),
                $this->boolQuery($request, 'ativo'),
                $this->filtrosListagem($request)
            )
        );
    }

    public function buscar(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return ApiExternaResponse::error('Informe q com ao menos 2 caracteres', 422);
        }

        $paginator = $this->service->buscar(
            $this->empresaId($request),
            $q,
            $this->page($request),
            $this->perPage($request),
            array_merge($this->filtrosListagem($request), [
                'ativo' => $this->boolQuery($request, 'ativo'),
            ])
        );

        if ($paginator === null) {
            return ApiExternaResponse::error('Informe q com ao menos 2 caracteres', 422);
        }

        return ApiExternaResponse::paginated($paginator);
    }

    public function tiposServico(Request $request): JsonResponse
    {
        $ativo = $request->has('ativo')
            ? $this->boolQuery($request, 'ativo')
            : true;

        return ApiExternaResponse::ok($this->service->tiposServico($ativo));
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

    public function servicos(Request $request, string $publicId): JsonResponse
    {
        $id = $this->decodePublicId($publicId);
        if ($id === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $filtros = [
            'ativo' => $this->boolQuery($request, 'ativo'),
            'status' => is_string($request->query('status')) ? $request->query('status') : null,
        ];

        $tipoServicoPublicId = $request->query('tipo_servico_public_id');
        if (is_string($tipoServicoPublicId) && $tipoServicoPublicId !== '') {
            $tipoId = ApiExternaId::decode($tipoServicoPublicId);
            if ($tipoId === null) {
                return ApiExternaResponse::error('Tipo de serviço inválido', 404);
            }
            $filtros['tipo_servico_id'] = $tipoId;
        }

        $data = $this->service->listarServicos($this->empresaId($request), $id, $filtros);
        if ($data === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        return ApiExternaResponse::ok($data);
    }

    public function showServico(Request $request, string $publicId, string $servicoPublicId): JsonResponse
    {
        $fornecedorId = $this->decodePublicId($publicId);
        $servicoId = $this->decodePublicId($servicoPublicId);
        if ($fornecedorId === null || $servicoId === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $data = $this->service->servicoDetalhe(
            $this->empresaId($request),
            $fornecedorId,
            $servicoId
        );
        if (! $data) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        return ApiExternaResponse::ok($data);
    }

    public function downloadAnexo(Request $request, string $publicId, string $arquivoPublicId): Response|JsonResponse
    {
        return $this->responderAnexoFornecedor($request, $publicId, $arquivoPublicId, 'download');
    }

    public function arquivoAnexo(Request $request, string $publicId, string $arquivoPublicId): Response|JsonResponse
    {
        return $this->responderAnexoFornecedor($request, $publicId, $arquivoPublicId, 'show');
    }

    public function downloadAnexoServico(
        Request $request,
        string $publicId,
        string $servicoPublicId,
        string $arquivoPublicId
    ): Response|JsonResponse {
        return $this->responderAnexoServico($request, $publicId, $servicoPublicId, $arquivoPublicId, 'download');
    }

    public function arquivoAnexoServico(
        Request $request,
        string $publicId,
        string $servicoPublicId,
        string $arquivoPublicId
    ): Response|JsonResponse {
        return $this->responderAnexoServico($request, $publicId, $servicoPublicId, $arquivoPublicId, 'show');
    }

    private function responderAnexoFornecedor(
        Request $request,
        string $publicId,
        string $arquivoPublicId,
        string $modo
    ): Response|JsonResponse {
        $fornecedorId = $this->decodePublicId($publicId);
        $arquivoId = $this->decodePublicId($arquivoPublicId);
        if ($fornecedorId === null || $arquivoId === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $arquivo = $this->service->resolverArquivoFornecedor(
            $this->empresaId($request),
            $fornecedorId,
            $arquivoId
        );

        return $this->streamArquivo($arquivo, $modo, Arquivo::DISCO_FORNECEDOR);
    }

    private function responderAnexoServico(
        Request $request,
        string $publicId,
        string $servicoPublicId,
        string $arquivoPublicId,
        string $modo
    ): Response|JsonResponse {
        $fornecedorId = $this->decodePublicId($publicId);
        $servicoId = $this->decodePublicId($servicoPublicId);
        $arquivoId = $this->decodePublicId($arquivoPublicId);
        if ($fornecedorId === null || $servicoId === null || $arquivoId === null) {
            return ApiExternaResponse::error('Não encontrado', 404);
        }

        $arquivo = $this->service->resolverArquivoServico(
            $this->empresaId($request),
            $fornecedorId,
            $servicoId,
            $arquivoId
        );

        return $this->streamArquivo($arquivo, $modo, Arquivo::DISCO_SERVICO_FORNECEDOR);
    }

    private function streamArquivo(?object $arquivo, string $modo, string $discoDefault): Response|JsonResponse
    {
        if (! $arquivo || empty($arquivo->file)) {
            return ApiExternaResponse::error('Arquivo não encontrado', 404);
        }

        $disco = $arquivo->disco ?: $discoDefault;
        $response = $modo === 'download'
            ? Arquivo::anexoDownload($disco, $arquivo->file)
            : Arquivo::anexoShow($disco, $arquivo->file);

        if (method_exists($response, 'headers')) {
            $response->headers->set('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }

    /**
     * @return array{q?:string,tipo?:string,tipo_pessoa?:string,uf?:string,tipo_servico_id?:int}
     */
    private function filtrosListagem(Request $request): array
    {
        $filtros = [];

        foreach (['q', 'tipo', 'tipo_pessoa', 'uf'] as $key) {
            $value = $request->query($key);
            if (is_string($value) && trim($value) !== '') {
                $filtros[$key] = trim($value);
            }
        }

        $tipoServicoPublicId = $request->query('tipo_servico_public_id');
        if (is_string($tipoServicoPublicId) && $tipoServicoPublicId !== '') {
            $tipoId = ApiExternaId::decode($tipoServicoPublicId);
            if ($tipoId !== null) {
                $filtros['tipo_servico_id'] = $tipoId;
            }
        }

        return $filtros;
    }
}
