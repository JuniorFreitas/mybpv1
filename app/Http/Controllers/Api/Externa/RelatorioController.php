<?php

namespace App\Http\Controllers\Api\Externa;

use App\Http\Controllers\Api\Externa\Concerns\ResolvesApiExternaContext;
use App\Http\Controllers\Controller;
use App\Services\ApiExterna\RelatorioApiService;
use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RelatorioController extends Controller
{
    use ResolvesApiExternaContext;

    public function __construct(private readonly RelatorioApiService $service)
    {
    }

    public function index(): JsonResponse
    {
        $itens = collect($this->service->catalogo())->map(function (array $r) {
            $r['links'] = [
                'self' => url('/api/v3/relatorios/' . $r['slug']),
            ];

            return $r;
        })->all();

        return ApiExternaResponse::ok($itens);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        if (! $this->service->existe($slug)) {
            return ApiExternaResponse::error('Relatório não encontrado', 404);
        }

        $filtros = $this->filtros($request);
        if ($filtros === null) {
            return ApiExternaResponse::error('Filtro inválido', 404);
        }

        $resultado = $this->service->executar(
            $slug,
            $this->empresaId($request),
            $this->page($request),
            $this->perPage($request),
            $filtros
        );

        return ApiExternaResponse::paginated($resultado['paginator'], $resultado['context']);
    }

    private function filtros(Request $request): ?array
    {
        $f = [];
        foreach ([
            'q', 'cargo', 'funcao', 'tipo_admissao', 'tipo', 'causa', 'status', 'status_ferias',
            'data_admissao_de', 'data_admissao_ate', 'data_vencimento_de', 'data_vencimento_ate',
            'data_saida_de', 'data_saida_ate', 'data_solicitacao_de', 'data_solicitacao_ate',
            'data_de', 'data_ate',
        ] as $k) {
            if (is_string($request->query($k)) && trim($request->query($k)) !== '') {
                $f[$k] = trim($request->query($k));
            }
        }

        if ($request->has('mes')) {
            $f['mes'] = (int) $request->query('mes');
        }

        $somenteAlerta = $this->boolQuery($request, 'somente_alerta');
        if ($somenteAlerta !== null) {
            $f['somente_alerta'] = $somenteAlerta;
        }

        foreach ([
            'centro_custo_public_id' => 'centro_custo_id',
            'ciclo_public_id' => 'ciclo_id',
        ] as $qk => $fk) {
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
