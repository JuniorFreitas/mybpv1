<?php

namespace App\Http\Controllers\Api\Externa;

use App\Http\Controllers\Api\Externa\Concerns\ResolvesApiExternaContext;
use App\Http\Controllers\Controller;
use App\Services\ApiExterna\ProcessoApiService;
use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProcessoController extends Controller
{
    use ResolvesApiExternaContext;

    public function __construct(private readonly ProcessoApiService $service)
    {
    }

    public function ativos(Request $request): JsonResponse
    {
        $filtros = $this->filtrosProcesso($request);
        if ($filtros === null) {
            return ApiExternaResponse::error('Filtro inválido', 404);
        }

        return ApiExternaResponse::paginated(
            $this->service->ativos(
                $this->empresaId($request),
                $this->page($request),
                $this->perPage($request),
                $filtros
            )
        );
    }

    public function emAndamento(Request $request): JsonResponse
    {
        $filtros = $this->filtrosProcesso($request);
        if ($filtros === null) {
            return ApiExternaResponse::error('Filtro inválido', 404);
        }

        return ApiExternaResponse::paginated(
            $this->service->emAndamento(
                $this->empresaId($request),
                $this->page($request),
                $this->perPage($request),
                $filtros
            )
        );
    }

    public function demitidos(Request $request): JsonResponse
    {
        $filtros = $this->filtrosProcesso($request, incluirDemissao: true);
        if ($filtros === null) {
            return ApiExternaResponse::error('Filtro inválido', 404);
        }

        return ApiExternaResponse::paginated(
            $this->service->demitidos(
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

        $filtros = $this->filtrosProcesso($request);
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

    public function etapas(Request $request, string $publicId): JsonResponse
    {
        return $this->nested($request, $publicId, fn (int $empresaId, int $id) => $this->service->etapas($empresaId, $id));
    }

    public function exames(Request $request, string $publicId): JsonResponse
    {
        return $this->nested($request, $publicId, fn (int $empresaId, int $id) => $this->service->exames($empresaId, $id));
    }

    public function documentos(Request $request, string $publicId): JsonResponse
    {
        return $this->nested($request, $publicId, fn (int $empresaId, int $id) => $this->service->documentos($empresaId, $id));
    }

    public function ferias(Request $request, string $publicId): JsonResponse
    {
        return $this->nested($request, $publicId, fn (int $empresaId, int $id) => $this->service->ferias($empresaId, $id));
    }

    public function preAdmissao(Request $request, string $publicId): JsonResponse
    {
        return $this->nested($request, $publicId, fn (int $empresaId, int $id) => $this->service->preAdmissao($empresaId, $id));
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
    private function filtrosProcesso(Request $request, bool $incluirDemissao = false): ?array
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

        foreach ([
            'empresa_public_id' => 'empresa_filtro_id',
            'vaga_aberta_public_id' => 'vaga_aberta_id',
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

        $uf = $request->query('uf');
        if (is_string($uf) && trim($uf) !== '') {
            $filtros['uf'] = trim($uf);
        }

        foreach (['tipo_admissao', 'status_admissao'] as $key) {
            $val = $request->query($key);
            if (is_string($val) && $val !== '') {
                $filtros[$key] = $val;
            }
        }

        foreach (['data_admissao_de', 'data_admissao_ate', 'parecer_de', 'parecer_ate'] as $key) {
            $val = $request->query($key);
            if (is_string($val) && $val !== '') {
                $filtros[$key] = $val;
            }
        }

        if ($incluirDemissao) {
            foreach (['data_desmobilizacao_de', 'data_desmobilizacao_ate'] as $key) {
                $val = $request->query($key);
                if (is_string($val) && $val !== '') {
                    $filtros[$key] = $val;
                }
            }
        }

        return $filtros;
    }
}
