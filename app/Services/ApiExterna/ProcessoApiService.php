<?php

namespace App\Services\ApiExterna;

use App\Models\Admissao;
use App\Support\ApiExterna\ApiExternaId;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * API v3 Processos/Admissão (somente leitura): listagens filtráveis,
 * busca, demitidos e nested (etapas, exames, documentos, férias, pré-admissão).
 */
class ProcessoApiService
{
    public function __construct(
        private readonly AdmissaoApiAssembler $assembler,
        private readonly FluxoRhApiService $fluxo
    ) {
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function ativos(int $empresaId, int $page, int $perPage, array $filtros = []): LengthAwarePaginator
    {
        $query = $this->baseQuery($empresaId)
            ->where('a.status', Admissao::STATUS_ADMISSAO_ADMITIDO)
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('demissaos as d')
                    ->whereColumn('d.feedback_id', 'fc.id');
            });

        $this->aplicarFiltros($query, $filtros);

        return $this->paginateEnriquecido($empresaId, $query, $page, $perPage, completo: false);
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function emAndamento(int $empresaId, int $page, int $perPage, array $filtros = []): LengthAwarePaginator
    {
        $query = $this->baseQuery($empresaId)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('resultado_integrados as ri')
                    ->whereColumn('ri.feedback_id', 'fc.id');
            })
            ->where(function ($q) {
                $q->whereNull('a.id')
                    ->orWhere('a.status', '!=', Admissao::STATUS_ADMISSAO_ADMITIDO);
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('demissaos as d')
                    ->whereColumn('d.feedback_id', 'fc.id');
            });

        $this->aplicarFiltros($query, $filtros);

        return $this->paginateEnriquecido($empresaId, $query, $page, $perPage, completo: false);
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function demitidos(int $empresaId, int $page, int $perPage, array $filtros = []): LengthAwarePaginator
    {
        $query = $this->baseQuery($empresaId)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('demissaos as d')
                    ->whereColumn('d.feedback_id', 'fc.id');
            });

        $this->aplicarFiltros($query, $filtros, incluirDemissao: true);

        return $this->paginateEnriquecido($empresaId, $query, $page, $perPage, completo: false);
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function buscar(
        int $empresaId,
        string $q,
        int $page,
        int $perPage,
        array $filtros = []
    ): ?LengthAwarePaginator {
        $q = trim($q);
        if (mb_strlen($q) < 2) {
            return null;
        }

        $filtros['q'] = $q;
        $query = $this->baseQuery($empresaId);
        $this->aplicarFiltros($query, $filtros);

        return $this->paginateEnriquecido($empresaId, $query, $page, $perPage, completo: false);
    }

    public function detalhe(int $empresaId, int $feedbackId): ?array
    {
        $existe = $this->baseQuery($empresaId)
            ->where('fc.id', $feedbackId)
            ->exists();

        if (! $existe) {
            return null;
        }

        return $this->assembler->montarUm($empresaId, $feedbackId, completo: true);
    }

    public function etapas(int $empresaId, int $feedbackId): ?array
    {
        return $this->fluxo->apenasEtapas($empresaId, $feedbackId);
    }

    public function exames(int $empresaId, int $feedbackId): ?array
    {
        if (! $this->processoExiste($empresaId, $feedbackId)) {
            return null;
        }

        $pid = ApiExternaId::encode($feedbackId);
        $itens = [];

        if (Schema::hasTable('examesesmts')) {
            $itens = DB::table('examesesmts')
                ->where('feedback_id', $feedbackId)
                ->orderByDesc('id')
                ->select([
                    'id',
                    'exame_realizado',
                    'atual',
                    'resultado',
                    'data_realizacao',
                    'data_vencimento',
                    'created_at',
                    'updated_at',
                ])
                ->get()
                ->map(function ($row) {
                    $data = ApiExternaId::mapRow($row, ['id']);
                    $data['exame_realizado'] = (bool) ($data['exame_realizado'] ?? false);
                    $data['atual'] = (bool) ($data['atual'] ?? false);
                    if (isset($data['resultado']) && is_string($data['resultado'])) {
                        $decoded = json_decode($data['resultado'], true);
                        $data['resultado'] = is_array($decoded) ? $decoded : $data['resultado'];
                    }

                    return $data;
                })
                ->all();
        }

        return [
            'public_id' => $pid,
            'exames' => $itens,
            'links' => [
                'self' => url('/api/v3/processos/' . $pid . '/exames'),
                'processo' => url('/api/v3/processos/' . $pid),
            ],
        ];
    }

    public function documentos(int $empresaId, int $feedbackId): ?array
    {
        if (! $this->processoExiste($empresaId, $feedbackId)) {
            return null;
        }

        $curriculoId = (int) DB::table('feedback_curriculos')
            ->where('empresa_id', $empresaId)
            ->where('id', $feedbackId)
            ->whereNull('deleted_at')
            ->value('curriculo_id');

        $pid = ApiExternaId::encode($feedbackId);
        $porTipo = [];

        if ($curriculoId > 0 && Schema::hasTable('documentos_curriculos') && Schema::hasTable('arquivos')) {
            $rows = DB::table('documentos_curriculos as dc')
                ->join('arquivos as a', 'a.id', '=', 'dc.arquivo_id')
                ->where('dc.curriculo_id', $curriculoId)
                ->select([
                    'dc.tipo',
                    'a.id',
                    'a.nome',
                    'a.extensao',
                    'a.bytes',
                    'a.created_at',
                ])
                ->orderBy('dc.tipo')
                ->orderBy('a.id')
                ->get();

            $porTipo = $rows->groupBy('tipo')->map(function ($group, $tipo) {
                $arquivos = $group->map(fn ($a) => [
                    'public_id' => ApiExternaId::encode((int) $a->id),
                    'nome' => $a->nome,
                    'extensao' => $a->extensao,
                    'bytes' => $a->bytes,
                    'created_at' => $a->created_at,
                ])->values()->all();

                return [
                    'tipo' => (string) $tipo,
                    'arquivos_count' => count($arquivos),
                    'arquivos' => $arquivos,
                ];
            })->values()->all();
        }

        return [
            'public_id' => $pid,
            'curriculo_public_id' => ApiExternaId::encode($curriculoId ?: null),
            'documentos' => $porTipo,
            'links' => [
                'self' => url('/api/v3/processos/' . $pid . '/documentos'),
                'processo' => url('/api/v3/processos/' . $pid),
            ],
        ];
    }

    public function ferias(int $empresaId, int $feedbackId): ?array
    {
        if (! $this->processoExiste($empresaId, $feedbackId)) {
            return null;
        }

        $admissaoId = DB::table('admissoes')
            ->where('feedback_id', $feedbackId)
            ->whereNull('deleted_at')
            ->value('id');

        $pid = ApiExternaId::encode($feedbackId);
        $itens = [];

        if ($admissaoId && Schema::hasTable('ferias_adquiridas')) {
            $itens = DB::table('ferias_adquiridas')
                ->where('admissao_id', $admissaoId)
                ->orderBy('id')
                ->get()
                ->map(fn ($f) => ApiExternaId::mapRow($f, ['id', 'admissao_id']))
                ->all();
        }

        return [
            'public_id' => $pid,
            'admissao_public_id' => $admissaoId ? ApiExternaId::encode((int) $admissaoId) : null,
            'ferias_adquiridas' => $itens,
            'links' => [
                'self' => url('/api/v3/processos/' . $pid . '/ferias'),
                'processo' => url('/api/v3/processos/' . $pid),
            ],
        ];
    }

    public function preAdmissao(int $empresaId, int $feedbackId): ?array
    {
        if (! $this->processoExiste($empresaId, $feedbackId)) {
            return null;
        }

        $pid = ApiExternaId::encode($feedbackId);
        $registro = null;

        if (Schema::hasTable('feedback_preadmissao')) {
            $row = DB::table('feedback_preadmissao')
                ->where('feedback_id', $feedbackId)
                ->select(['id', 'feedback_id', 'user_finalizou_id', 'created_at', 'updated_at'])
                ->first();

            if ($row) {
                $registro = ApiExternaId::mapRow($row, [
                    'id',
                    'feedback_id',
                    'user_finalizou_id' => 'user_finalizou_public_id',
                ]);
            }
        }

        return [
            'public_id' => $pid,
            'pre_admissao' => $registro,
            'links' => [
                'self' => url('/api/v3/processos/' . $pid . '/pre-admissao'),
                'processo' => url('/api/v3/processos/' . $pid),
                'documentos' => url('/api/v3/processos/' . $pid . '/documentos'),
            ],
        ];
    }

    private function processoExiste(int $empresaId, int $feedbackId): bool
    {
        return DB::table('feedback_curriculos')
            ->where('empresa_id', $empresaId)
            ->where('id', $feedbackId)
            ->whereNull('deleted_at')
            ->exists();
    }

    private function paginateEnriquecido(
        int $empresaId,
        Builder $query,
        int $page,
        int $perPage,
        bool $completo
    ): LengthAwarePaginator {
        $paginator = $query
            ->orderByDesc('fc.id')
            ->paginate($perPage, ['fc.id'], 'page', $page);

        $ids = collect($paginator->items())
            ->map(fn ($row) => (int) (is_object($row) ? $row->id : $row))
            ->all();

        $map = $this->assembler->montarVarios($empresaId, $ids, $completo);

        return $paginator->through(function ($row) use ($map) {
            $id = (int) (is_object($row) ? $row->id : $row);

            return $map[$id] ?? null;
        });
    }

    private function baseQuery(int $empresaId): Builder
    {
        return DB::table('feedback_curriculos as fc')
            ->leftJoin('admissoes as a', function ($join) {
                $join->on('a.feedback_id', '=', 'fc.id')
                    ->whereNull('a.deleted_at');
            })
            ->where('fc.empresa_id', $empresaId)
            ->whereNull('fc.deleted_at');
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    private function aplicarFiltros(Builder $query, array $filtros, bool $incluirDemissao = false): void
    {
        $needsCurriculo = ! empty($filtros['q']) || ! empty($filtros['cpf']);
        $needsVagaAberta = ! empty($filtros['uf']) || ! empty($filtros['vaga_aberta_id']);
        $needsMunicipio = ! empty($filtros['uf']);
        $needsParecer = ! empty($filtros['parecer_de']) || ! empty($filtros['parecer_ate']);
        $needsDemissaoDatas = $incluirDemissao && (
            ! empty($filtros['data_desmobilizacao_de']) || ! empty($filtros['data_desmobilizacao_ate'])
        );

        if ($needsCurriculo) {
            $query->leftJoin('curriculos as c', function ($join) {
                $join->on('c.id', '=', 'fc.curriculo_id')->whereNull('c.deleted_at');
            });
        }

        if ($needsVagaAberta) {
            $query->leftJoin('vagas_abertas as va', 'va.id', '=', 'fc.vagas_abertas_id');
        }

        if ($needsMunicipio) {
            $query->leftJoin('municipios as m', 'm.id', '=', 'va.municipio_id');
        }

        if ($needsParecer) {
            $query->leftJoin('parecer_rh as prh', 'prh.feedback_id', '=', 'fc.id');
        }

        if ($needsDemissaoDatas) {
            $query->leftJoin('demissaos as dem', 'dem.feedback_id', '=', 'fc.id');
        }

        if (! empty($filtros['q'])) {
            $q = trim((string) $filtros['q']);
            $digits = preg_replace('/\D+/', '', $q) ?: '';
            $query->where(function ($inner) use ($q, $digits) {
                $inner->where('c.nome', 'like', '%' . $q . '%');
                if ($digits !== '') {
                    $inner->orWhere('c.cpf', 'like', '%' . $digits . '%');
                }
            });
        }

        if (! empty($filtros['cpf'])) {
            $cpf = preg_replace('/\D+/', '', (string) $filtros['cpf']) ?: '';
            if ($cpf !== '') {
                $query->where('c.cpf', $cpf);
            }
        }

        if (! empty($filtros['empresa_filtro_id'])) {
            $empresaFiltroId = (int) $filtros['empresa_filtro_id'];
            $query->where(function ($q) use ($empresaFiltroId) {
                $q->where('fc.empresa_id', $empresaFiltroId)
                    ->orWhere('fc.cliente_id', $empresaFiltroId);
            });
        }

        if (! empty($filtros['vaga_aberta_id'])) {
            $query->where('fc.vagas_abertas_id', (int) $filtros['vaga_aberta_id']);
        }

        if (! empty($filtros['uf'])) {
            $query->where('m.uf', strtoupper((string) $filtros['uf']));
        }

        if (! empty($filtros['tipo_admissao'])) {
            $query->where('a.tipo_admissao', $filtros['tipo_admissao']);
        }

        if (! empty($filtros['status_admissao'])) {
            $query->where('a.status', $filtros['status_admissao']);
        }

        if (! empty($filtros['data_admissao_de'])) {
            $query->whereDate('a.data_admissao', '>=', $filtros['data_admissao_de']);
        }

        if (! empty($filtros['data_admissao_ate'])) {
            $query->whereDate('a.data_admissao', '<=', $filtros['data_admissao_ate']);
        }

        if (! empty($filtros['parecer_de'])) {
            $query->whereDate('prh.created_at', '>=', $filtros['parecer_de']);
        }

        if (! empty($filtros['parecer_ate'])) {
            $query->whereDate('prh.created_at', '<=', $filtros['parecer_ate']);
        }

        if ($incluirDemissao) {
            if (! empty($filtros['data_desmobilizacao_de'])) {
                $query->whereDate('dem.data_desmobilizacao', '>=', $filtros['data_desmobilizacao_de']);
            }
            if (! empty($filtros['data_desmobilizacao_ate'])) {
                $query->whereDate('dem.data_desmobilizacao', '<=', $filtros['data_desmobilizacao_ate']);
            }
        }
    }
}
