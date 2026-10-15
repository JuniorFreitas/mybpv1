<?php

namespace App\Services\ApiExterna;

use App\Models\Admissao;
use App\Models\Ferias;
use App\Services\AvaliacaoNoventaService;
use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaResponse;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use MasterTag\DataHora;

/**
 * Relatórios do menu RH (somente leitura) para a API Externa v3.
 * Isolamento por empresa_id; IDs públicos; CPF mascarado.
 */
class RelatorioApiService
{
    public const SLUGS = [
        'efetivo',
        'centro-custo',
        'vencimento-asos',
        'vencimento-treinamentos',
        'vencimento-ferias',
        'ferias',
        'medidas-administrativas',
        'avaliacao-experiencia',
        'aniversariantes',
        'nps',
    ];

    public function __construct(
        private readonly AvaliacaoNoventaService $avaliacaoNoventaService
    ) {
    }

    /**
     * @return list<array{slug: string, nome: string, descricao: string, filtros: list<string>}>
     */
    public function catalogo(): array
    {
        return [
            [
                'slug' => 'efetivo',
                'nome' => 'Efetivo',
                'descricao' => 'Colaboradores admitidos (sem demissão)',
                'filtros' => ['q', 'cargo', 'tipo_admissao', 'centro_custo_public_id', 'data_admissao_de', 'data_admissao_ate'],
            ],
            [
                'slug' => 'centro-custo',
                'nome' => 'Centro de custo',
                'descricao' => 'Efetivo agrupado por centro de custo',
                'filtros' => ['q', 'centro_custo_public_id'],
            ],
            [
                'slug' => 'vencimento-asos',
                'nome' => 'Vencimento de ASO',
                'descricao' => 'Último ASO atual/aprovado e dias para vencer',
                'filtros' => ['q', 'centro_custo_public_id', 'somente_alerta'],
            ],
            [
                'slug' => 'vencimento-treinamentos',
                'nome' => 'Vencimento de treinamentos',
                'descricao' => 'Treinamentos com vencimento no período',
                'filtros' => ['q', 'centro_custo_public_id', 'data_vencimento_de', 'data_vencimento_ate'],
            ],
            [
                'slug' => 'vencimento-ferias',
                'nome' => 'Vencimento de férias',
                'descricao' => 'Cálculo de avos / períodos aquisitivos (admitidos)',
                'filtros' => ['q', 'centro_custo_public_id', 'cargo'],
            ],
            [
                'slug' => 'ferias',
                'nome' => 'Férias',
                'descricao' => 'Solicitações de férias (aguardando/gozando)',
                'filtros' => ['q', 'status_ferias', 'centro_custo_public_id', 'data_saida_de', 'data_saida_ate'],
            ],
            [
                'slug' => 'medidas-administrativas',
                'nome' => 'Medidas administrativas',
                'descricao' => 'Medidas do efetivo com totais por tipo',
                'filtros' => ['q', 'tipo', 'causa', 'data_solicitacao_de', 'data_solicitacao_ate', 'centro_custo_public_id'],
            ],
            [
                'slug' => 'avaliacao-experiencia',
                'nome' => 'Avaliação de experiência (90 dias)',
                'descricao' => 'Vencimentos de avaliação de experiência',
                'filtros' => ['q', 'status', 'cargo', 'funcao'],
            ],
            [
                'slug' => 'aniversariantes',
                'nome' => 'Aniversariantes',
                'descricao' => 'Admitidos aniversariantes do mês',
                'filtros' => ['mes'],
            ],
            [
                'slug' => 'nps',
                'nome' => 'NPS',
                'descricao' => 'Resumo NPS da empresa autenticada',
                'filtros' => ['data_de', 'data_ate', 'ciclo_public_id'],
            ],
        ];
    }

    public function existe(string $slug): bool
    {
        return in_array($slug, self::SLUGS, true);
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array{paginator: LengthAwarePaginator, context: array<string, mixed>}
     */
    public function executar(string $slug, int $empresaId, int $page, int $perPage, array $filtros = []): array
    {
        return match ($slug) {
            'efetivo' => $this->efetivo($empresaId, $page, $perPage, $filtros),
            'centro-custo' => $this->centroCusto($empresaId, $page, $perPage, $filtros),
            'vencimento-asos' => $this->vencimentoAsos($empresaId, $page, $perPage, $filtros),
            'vencimento-treinamentos' => $this->vencimentoTreinamentos($empresaId, $page, $perPage, $filtros),
            'vencimento-ferias' => $this->vencimentoFerias($empresaId, $page, $perPage, $filtros),
            'ferias' => $this->ferias($empresaId, $page, $perPage, $filtros),
            'medidas-administrativas' => $this->medidas($empresaId, $page, $perPage, $filtros),
            'avaliacao-experiencia' => $this->avaliacaoExperiencia($empresaId, $page, $perPage, $filtros),
            'aniversariantes' => $this->aniversariantes($empresaId, $page, $perPage, $filtros),
            'nps' => $this->nps($empresaId, $page, $perPage, $filtros),
            default => throw new \InvalidArgumentException('Relatório inválido'),
        };
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array{paginator: LengthAwarePaginator, context: array<string, mixed>}
     */
    private function efetivo(int $empresaId, int $page, int $perPage, array $filtros): array
    {
        $query = $this->baseAdmitidos($empresaId)
            ->leftJoin('centro_custos as cc', 'cc.id', '=', 'a.centro_custo_id')
            ->select([
                'fc.id as feedback_id',
                'c.nome',
                'c.cpf',
                'a.id as admissao_id',
                'a.cargo',
                'a.funcao',
                'a.tipo_admissao',
                'a.data_admissao',
                'a.centro_custo_id',
                'cc.label as centro_custo_label',
            ])
            ->orderBy('c.nome');

        if (! empty($filtros['q'])) {
            $q = $filtros['q'];
            $query->where(function ($w) use ($q) {
                $w->where('c.nome', 'like', '%' . $q . '%')
                    ->orWhere('c.cpf', 'like', '%' . preg_replace('/\D+/', '', $q) . '%');
            });
        }
        if (! empty($filtros['cargo'])) {
            $query->where('a.cargo', 'like', '%' . $filtros['cargo'] . '%');
        }
        if (! empty($filtros['tipo_admissao'])) {
            $query->where('a.tipo_admissao', $filtros['tipo_admissao']);
        }
        if (! empty($filtros['centro_custo_id'])) {
            $query->where('a.centro_custo_id', (int) $filtros['centro_custo_id']);
        }
        if (! empty($filtros['data_admissao_de'])) {
            $query->whereDate('a.data_admissao', '>=', $filtros['data_admissao_de']);
        }
        if (! empty($filtros['data_admissao_ate'])) {
            $query->whereDate('a.data_admissao', '<=', $filtros['data_admissao_ate']);
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page)
            ->through(function ($row) {
                $pid = ApiExternaId::encode((int) $row->feedback_id);

                return [
                    'processo_public_id' => $pid,
                    'nome' => $row->nome,
                    'cpf' => ApiExternaResponse::mascararCpf($row->cpf),
                    'cargo' => $row->cargo ?: 'Não informado',
                    'funcao' => $row->funcao,
                    'tipo_admissao' => $row->tipo_admissao ?: 'Não informado',
                    'data_admissao' => $row->data_admissao,
                    'centro_custo_public_id' => ApiExternaId::encode($row->centro_custo_id),
                    'centro_custo' => $row->centro_custo_label ?: 'Não informado',
                    'links' => [
                        'processo' => url('/api/v3/processos/' . $pid),
                        'historico' => url('/api/v3/historico/' . $pid),
                    ],
                ];
            });

        return ['paginator' => $paginator, 'context' => []];
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array{paginator: LengthAwarePaginator, context: array<string, mixed>}
     */
    private function centroCusto(int $empresaId, int $page, int $perPage, array $filtros): array
    {
        $query = $this->baseAdmitidos($empresaId)
            ->leftJoin('centro_custos as cc', 'cc.id', '=', 'a.centro_custo_id')
            ->select([
                'a.centro_custo_id',
                DB::raw("COALESCE(NULLIF(TRIM(cc.label), ''), 'SEM CENTRO DE CUSTO') as label"),
                DB::raw('COUNT(a.id) as total'),
            ])
            ->groupBy('a.centro_custo_id', 'cc.label')
            ->orderByDesc('total');

        if (! empty($filtros['centro_custo_id'])) {
            $query->where('a.centro_custo_id', (int) $filtros['centro_custo_id']);
        }
        if (! empty($filtros['q'])) {
            $query->where('cc.label', 'like', '%' . $filtros['q'] . '%');
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page)
            ->through(fn ($row) => [
                'centro_custo_public_id' => ApiExternaId::encode($row->centro_custo_id),
                'label' => $row->label,
                'total' => (int) $row->total,
                'links' => [
                    'efetivo' => url('/api/v3/relatorios/efetivo?centro_custo_public_id=' . (ApiExternaId::encode($row->centro_custo_id) ?? '')),
                ],
            ]);

        return ['paginator' => $paginator, 'context' => []];
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array{paginator: LengthAwarePaginator, context: array<string, mixed>}
     */
    private function vencimentoAsos(int $empresaId, int $page, int $perPage, array $filtros): array
    {
        if (! Schema::hasTable('examesesmts')) {
            return ['paginator' => $this->paginarVazio($page, $perPage), 'context' => ['config_ok' => false]];
        }

        $diasAlerta = 90;
        $hoje = date('Y-m-d');

        $query = $this->baseAdmitidos($empresaId)
            ->join('examesesmts as e', function ($join) {
                $join->on('e.feedback_id', '=', 'fc.id')
                    ->where('e.atual', true)
                    ->where('e.exame_realizado', true)
                    ->whereNull('e.deleted_at');
            })
            ->leftJoin('centro_custos as cc', 'cc.id', '=', 'a.centro_custo_id')
            ->leftJoin('exame_funcionarios as ef', 'ef.id', '=', 'e.exame_funcionario_id')
            ->leftJoin('exame_tipos as et', 'et.id', '=', 'ef.exame_tipo_id')
            ->whereNotNull('e.data_vencimento')
            ->select([
                'fc.id as feedback_id',
                'c.nome',
                'c.cpf',
                'a.cargo',
                'a.data_admissao',
                'a.centro_custo_id',
                'cc.label as centro_custo_label',
                'e.data_realizacao',
                'e.data_vencimento',
                'et.label as exame_tipo',
            ])
            ->orderBy('e.data_vencimento');

        if (! empty($filtros['q'])) {
            $query->where('c.nome', 'like', '%' . $filtros['q'] . '%');
        }
        if (! empty($filtros['centro_custo_id'])) {
            $query->where('a.centro_custo_id', (int) $filtros['centro_custo_id']);
        }

        $rows = $query->get()->map(function ($row) use ($hoje, $diasAlerta) {
            $venc = $this->normalizarData($row->data_vencimento);
            $dias = $this->diasEntre($hoje, $venc);
            $pid = ApiExternaId::encode((int) $row->feedback_id);

            return [
                'processo_public_id' => $pid,
                'nome' => $row->nome,
                'cpf' => ApiExternaResponse::mascararCpf($row->cpf),
                'cargo' => $row->cargo ?: 'Não informado',
                'data_admissao' => $row->data_admissao,
                'centro_custo_public_id' => ApiExternaId::encode($row->centro_custo_id),
                'centro_custo' => $row->centro_custo_label,
                'exame_tipo' => $row->exame_tipo ?: '—',
                'data_aso' => $row->data_realizacao,
                'data_vencimento' => $row->data_vencimento,
                'dias_vencer' => $dias,
                'status' => $dias < 0 ? 'VENCIDO' : 'A VENCER',
                'alerta' => $dias <= $diasAlerta,
                'links' => ['processo' => url('/api/v3/processos/' . $pid)],
            ];
        });

        if (! empty($filtros['somente_alerta'])) {
            $rows = $rows->filter(fn ($r) => $r['alerta'])->values();
        }

        return [
            'paginator' => $this->paginar($rows, $page, $perPage),
            'context' => ['dias_alerta' => $diasAlerta],
        ];
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array{paginator: LengthAwarePaginator, context: array<string, mixed>}
     */
    private function vencimentoTreinamentos(int $empresaId, int $page, int $perPage, array $filtros): array
    {
        if (! Schema::hasTable('treinamento_vencimento') && ! Schema::hasTable('treinamento_vencimentos')) {
            return ['paginator' => $this->paginarVazio($page, $perPage), 'context' => []];
        }

        $tvTable = Schema::hasTable('treinamento_vencimento') ? 'treinamento_vencimento' : 'treinamento_vencimentos';
        $de = $filtros['data_vencimento_de'] ?? date('Y-m-d');
        $ate = $filtros['data_vencimento_ate'] ?? date('Y-m-d', strtotime('+30 days'));

        $query = $this->baseAdmitidos($empresaId)
            ->join('treinamentos as t', function ($join) {
                $join->on('t.feedback_id', '=', 'fc.id')->whereNull('t.deleted_at');
            })
            ->join($tvTable . ' as tv', 'tv.treinamento_id', '=', 't.id')
            ->leftJoin('centro_custos as cc', 'cc.id', '=', 'a.centro_custo_id')
            ->whereBetween('tv.data_vencimento', [$de, $ate])
            ->select([
                'fc.id as feedback_id',
                'c.nome',
                'c.cpf',
                'a.cargo',
                'a.centro_custo_id',
                'cc.label as centro_custo_label',
                't.id as treinamento_id',
                'tv.data_vencimento',
                'tv.id as vencimento_id',
            ])
            ->orderBy('tv.data_vencimento');

        if (Schema::hasColumn($tvTable, 'deleted_at')) {
            $query->whereNull('tv.deleted_at');
        }
        if (! empty($filtros['q'])) {
            $query->where('c.nome', 'like', '%' . $filtros['q'] . '%');
        }
        if (! empty($filtros['centro_custo_id'])) {
            $query->where('a.centro_custo_id', (int) $filtros['centro_custo_id']);
        }

        $hoje = date('Y-m-d');
        $paginator = $query->paginate($perPage, ['*'], 'page', $page)
            ->through(function ($row) use ($hoje) {
                $venc = $this->normalizarData($row->data_vencimento);
                $dias = $this->diasEntre($hoje, $venc);
                $pid = ApiExternaId::encode((int) $row->feedback_id);

                return [
                    'processo_public_id' => $pid,
                    'treinamento_public_id' => ApiExternaId::encode($row->treinamento_id),
                    'vencimento_public_id' => ApiExternaId::encode($row->vencimento_id),
                    'nome' => $row->nome,
                    'cpf' => ApiExternaResponse::mascararCpf($row->cpf),
                    'cargo' => $row->cargo,
                    'centro_custo_public_id' => ApiExternaId::encode($row->centro_custo_id),
                    'centro_custo' => $row->centro_custo_label,
                    'data_vencimento' => $row->data_vencimento,
                    'dias_vencer' => $dias,
                    'status' => $dias < 0 ? 'VENCIDO' : ($dias <= 30 ? 'PROXIMO' : 'REGULAR'),
                    'links' => ['processo' => url('/api/v3/processos/' . $pid)],
                ];
            });

        return [
            'paginator' => $paginator,
            'context' => ['periodo' => ['de' => $de, 'ate' => $ate]],
        ];
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array{paginator: LengthAwarePaginator, context: array<string, mixed>}
     */
    private function vencimentoFerias(int $empresaId, int $page, int $perPage, array $filtros): array
    {
        if (! Schema::hasTable('ferias_calculo_avos')) {
            return ['paginator' => $this->paginarVazio($page, $perPage), 'context' => []];
        }

        $query = DB::table('ferias_calculo_avos as fca')
            ->join('admissoes as a', function ($join) {
                $join->on('a.id', '=', 'fca.admissao_id')->whereNull('a.deleted_at');
            })
            ->join('feedback_curriculos as fc', function ($join) use ($empresaId) {
                $join->on('fc.id', '=', 'a.feedback_id')
                    ->where('fc.empresa_id', $empresaId)
                    ->whereNull('fc.deleted_at');
            })
            ->leftJoin('demissaos as d', 'd.feedback_id', '=', 'fc.id')
            ->leftJoin('curriculos as c', function ($join) {
                $join->on('c.id', '=', 'fc.curriculo_id')->whereNull('c.deleted_at');
            })
            ->leftJoin('centro_custos as cc', 'cc.id', '=', 'a.centro_custo_id')
            ->leftJoin('periodos_aquisitivos as pa', 'pa.id', '=', 'fca.periodo_aquisitivo_id')
            ->whereNull('d.id')
            ->where('a.status', Admissao::STATUS_ADMISSAO_ADMITIDO)
            ->where('fca.empresa_id', $empresaId)
            ->select([
                'fca.id',
                'fc.id as feedback_id',
                'c.nome',
                'c.cpf',
                'a.cargo',
                'a.centro_custo_id',
                'cc.label as centro_custo_label',
                'fca.total_avos',
                'fca.ultima_atualizacao',
                'pa.ano_inicial',
                'pa.ano_final',
            ])
            ->orderBy('c.nome');

        if (Schema::hasColumn('ferias_calculo_avos', 'deleted_at')) {
            $query->whereNull('fca.deleted_at');
        }
        if (! empty($filtros['q'])) {
            $query->where('c.nome', 'like', '%' . $filtros['q'] . '%');
        }
        if (! empty($filtros['cargo'])) {
            $query->where('a.cargo', 'like', '%' . $filtros['cargo'] . '%');
        }
        if (! empty($filtros['centro_custo_id'])) {
            $query->where('a.centro_custo_id', (int) $filtros['centro_custo_id']);
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page)
            ->through(function ($row) {
                $pid = ApiExternaId::encode((int) $row->feedback_id);

                return [
                    'public_id' => ApiExternaId::encode((int) $row->id),
                    'processo_public_id' => $pid,
                    'nome' => $row->nome,
                    'cpf' => ApiExternaResponse::mascararCpf($row->cpf),
                    'cargo' => $row->cargo,
                    'centro_custo_public_id' => ApiExternaId::encode($row->centro_custo_id),
                    'centro_custo' => $row->centro_custo_label,
                    'total_avos' => $row->total_avos,
                    'periodo_aquisitivo' => trim(($row->ano_inicial ?? '') . '/' . ($row->ano_final ?? ''), '/'),
                    'ultima_atualizacao' => $row->ultima_atualizacao,
                    'links' => ['processo' => url('/api/v3/processos/' . $pid)],
                ];
            });

        return ['paginator' => $paginator, 'context' => []];
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array{paginator: LengthAwarePaginator, context: array<string, mixed>}
     */
    private function ferias(int $empresaId, int $page, int $perPage, array $filtros): array
    {
        if (! Schema::hasTable('ferias')) {
            return ['paginator' => $this->paginarVazio($page, $perPage), 'context' => []];
        }

        $statusPadrao = Ferias::LISTA_RELATORIO_VENCIMENTO_FERIAS;

        $query = DB::table('ferias as f')
            ->join('admissoes as a', function ($join) {
                $join->on('a.id', '=', 'f.admissao_id')->whereNull('a.deleted_at');
            })
            ->join('feedback_curriculos as fc', function ($join) use ($empresaId) {
                $join->on('fc.id', '=', 'a.feedback_id')
                    ->where('fc.empresa_id', $empresaId)
                    ->whereNull('fc.deleted_at');
            })
            ->leftJoin('demissaos as d', 'd.feedback_id', '=', 'fc.id')
            ->leftJoin('curriculos as c', function ($join) {
                $join->on('c.id', '=', 'fc.curriculo_id')->whereNull('c.deleted_at');
            })
            ->leftJoin('centro_custos as cc', 'cc.id', '=', 'a.centro_custo_id')
            ->whereNull('d.id')
            ->where('a.status', Admissao::STATUS_ADMISSAO_ADMITIDO)
            ->select([
                'f.id',
                'fc.id as feedback_id',
                'c.nome',
                'c.cpf',
                'a.cargo',
                'a.centro_custo_id',
                'cc.label as centro_custo_label',
                'f.status_ferias',
                'f.data_saida',
                'f.data_retorno',
            ])
            ->orderBy('f.data_saida');

        if (Schema::hasColumn('ferias', 'deleted_at')) {
            $query->whereNull('f.deleted_at');
        }
        if (! empty($filtros['status_ferias'])) {
            $query->where('f.status_ferias', $filtros['status_ferias']);
        } else {
            $query->whereIn('f.status_ferias', $statusPadrao);
        }
        if (! empty($filtros['q'])) {
            $query->where('c.nome', 'like', '%' . $filtros['q'] . '%');
        }
        if (! empty($filtros['centro_custo_id'])) {
            $query->where('a.centro_custo_id', (int) $filtros['centro_custo_id']);
        }
        if (! empty($filtros['data_saida_de'])) {
            $query->whereDate('f.data_saida', '>=', $filtros['data_saida_de']);
        }
        if (! empty($filtros['data_saida_ate'])) {
            $query->whereDate('f.data_saida', '<=', $filtros['data_saida_ate']);
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page)
            ->through(function ($row) {
                $pid = ApiExternaId::encode((int) $row->feedback_id);

                return [
                    'public_id' => ApiExternaId::encode((int) $row->id),
                    'processo_public_id' => $pid,
                    'nome' => $row->nome,
                    'cpf' => ApiExternaResponse::mascararCpf($row->cpf),
                    'cargo' => $row->cargo,
                    'centro_custo_public_id' => ApiExternaId::encode($row->centro_custo_id),
                    'centro_custo' => $row->centro_custo_label,
                    'status_ferias' => $row->status_ferias,
                    'data_saida' => $row->data_saida,
                    'data_retorno' => $row->data_retorno,
                    'links' => [
                        'processo' => url('/api/v3/processos/' . $pid),
                        'ferias_processo' => url('/api/v3/processos/' . $pid . '/ferias'),
                    ],
                ];
            });

        return ['paginator' => $paginator, 'context' => []];
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array{paginator: LengthAwarePaginator, context: array<string, mixed>}
     */
    private function medidas(int $empresaId, int $page, int $perPage, array $filtros): array
    {
        if (! Schema::hasTable('medida_administrativas')) {
            return ['paginator' => $this->paginarVazio($page, $perPage), 'context' => []];
        }

        $query = DB::table('medida_administrativas as m')
            ->join('feedback_curriculos as fc', function ($join) use ($empresaId) {
                $join->on('fc.id', '=', 'm.feedback_id')
                    ->where('fc.empresa_id', $empresaId)
                    ->whereNull('fc.deleted_at');
            })
            ->leftJoin('curriculos as c', function ($join) {
                $join->on('c.id', '=', 'fc.curriculo_id')->whereNull('c.deleted_at');
            })
            ->leftJoin('admissoes as a', function ($join) {
                $join->on('a.feedback_id', '=', 'fc.id')->whereNull('a.deleted_at');
            })
            ->leftJoin('centro_custos as cc', 'cc.id', '=', 'a.centro_custo_id')
            ->whereNull('m.deleted_at')
            ->select([
                'm.id',
                'fc.id as feedback_id',
                'c.nome',
                'c.cpf',
                'a.cargo',
                'a.centro_custo_id',
                'cc.label as centro_custo_label',
                'm.tipo',
                'm.causa',
                'm.data_solicitacao',
                'm.data_retorno',
                'm.solicitante',
            ])
            ->orderByDesc('m.data_solicitacao');

        if (! empty($filtros['q'])) {
            $query->where('c.nome', 'like', '%' . $filtros['q'] . '%');
        }
        if (! empty($filtros['tipo'])) {
            $query->where('m.tipo', $filtros['tipo']);
        }
        if (! empty($filtros['causa'])) {
            $query->where('m.causa', $filtros['causa']);
        }
        if (! empty($filtros['centro_custo_id'])) {
            $query->where('a.centro_custo_id', (int) $filtros['centro_custo_id']);
        }
        if (! empty($filtros['data_solicitacao_de'])) {
            $query->whereDate('m.data_solicitacao', '>=', $filtros['data_solicitacao_de']);
        }
        if (! empty($filtros['data_solicitacao_ate'])) {
            $query->whereDate('m.data_solicitacao', '<=', $filtros['data_solicitacao_ate']);
        }

        $graficosQuery = DB::table('medida_administrativas as m')
            ->join('feedback_curriculos as fc', function ($join) use ($empresaId) {
                $join->on('fc.id', '=', 'm.feedback_id')
                    ->where('fc.empresa_id', $empresaId)
                    ->whereNull('fc.deleted_at');
            })
            ->whereNull('m.deleted_at')
            ->select('m.tipo', DB::raw('COUNT(*) as total'))
            ->groupBy('m.tipo');
        $graficos = $graficosQuery->get()
            ->map(fn ($r) => ['label' => $r->tipo ?: 'Não informado', 'total' => (int) $r->total])
            ->values()
            ->all();

        $paginator = $query->paginate($perPage, ['*'], 'page', $page)
            ->through(function ($row) {
                $pid = ApiExternaId::encode((int) $row->feedback_id);
                $mid = ApiExternaId::encode((int) $row->id);

                return [
                    'public_id' => $mid,
                    'processo_public_id' => $pid,
                    'nome' => $row->nome,
                    'cpf' => ApiExternaResponse::mascararCpf($row->cpf),
                    'cargo' => $row->cargo,
                    'centro_custo_public_id' => ApiExternaId::encode($row->centro_custo_id),
                    'centro_custo' => $row->centro_custo_label,
                    'tipo' => $row->tipo,
                    'causa' => $row->causa,
                    'data_solicitacao' => $row->data_solicitacao,
                    'data_retorno' => $row->data_retorno,
                    'solicitante' => $row->solicitante,
                    'links' => [
                        'medida' => url('/api/v3/medidas-administrativas/' . $mid),
                        'processo' => url('/api/v3/processos/' . $pid),
                    ],
                ];
            });

        return ['paginator' => $paginator, 'context' => ['graficos' => ['tipos' => $graficos]]];
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array{paginator: LengthAwarePaginator, context: array<string, mixed>}
     */
    private function avaliacaoExperiencia(int $empresaId, int $page, int $perPage, array $filtros): array
    {
        if (! Schema::hasTable('avaliacao_noventa_vencimentos')) {
            return ['paginator' => $this->paginarVazio($page, $perPage), 'context' => ['resumo' => []]];
        }

        $avaliacoes = $this->avaliacaoNoventaService->buscarAvaliacoesVencendoOuVencidas(
            $empresaId,
            AvaliacaoNoventaService::DIAS_ANTECEDENCIA,
            true
        );
        $dataAtual = (new DataHora())->dataCompleta();
        $vencimentos = $this->avaliacaoNoventaService->montarVencimentos($avaliacoes, $dataAtual, false);

        if (! empty($filtros['q'])) {
            $q = mb_strtolower($filtros['q']);
            $vencimentos = $vencimentos->filter(fn ($v) => str_contains(mb_strtolower((string) ($v['colaborador'] ?? '')), $q))->values();
        }
        if (! empty($filtros['status'])) {
            $status = strtoupper($filtros['status']);
            $vencimentos = $vencimentos->filter(fn ($v) => ($v['status'] ?? '') === $status)->values();
        }
        if (! empty($filtros['cargo'])) {
            $cargo = $filtros['cargo'];
            $vencimentos = $vencimentos->filter(fn ($v) => ($v['cargo'] ?? '') === $cargo)->values();
        }
        if (! empty($filtros['funcao'])) {
            $funcao = $filtros['funcao'];
            $vencimentos = $vencimentos->filter(fn ($v) => ($v['funcao'] ?? '') === $funcao)->values();
        }

        $resumo = [
            'total' => $vencimentos->count(),
            'vencidos' => $vencimentos->where('status', 'VENCIDO')->count(),
            'vence_hoje' => $vencimentos->where('status', 'VENCE HOJE')->count(),
            'a_vencer' => $vencimentos->where('status', 'A VENCER')->count(),
            'completas' => $vencimentos->filter(fn ($v) => ($v['qnt_avaliacoes'] ?? 0) >= 2)->count(),
        ];

        $itens = $vencimentos->map(function ($v) {
            $feedbackId = (int) ($v['feedback_id'] ?? 0);
            $pid = $feedbackId ? ApiExternaId::encode($feedbackId) : null;

            return [
                'processo_public_id' => $pid,
                'nome' => $v['colaborador'] ?? null,
                'cargo' => $v['cargo'] ?? null,
                'funcao' => $v['funcao'] ?? null,
                'centro_custo' => $v['centro_custo'] ?? null,
                'gestor' => $v['gestor_nome'] ?? null,
                'status' => $v['status'] ?? null,
                'qnt_avaliacoes' => (int) ($v['qnt_avaliacoes'] ?? 0),
                'prazo_inicial' => $v['prazo_dia_inicial'] ?? null,
                'prazo_final' => $v['prazo_dia_final'] ?? null,
                'dias_para_vencer' => $v['dias_para_vencer'] ?? null,
                'dias_atraso' => $v['dias_atraso'] ?? null,
                'data_admissao' => $v['data_admissao'] ?? null,
                'links' => $pid ? ['processo' => url('/api/v3/processos/' . $pid)] : [],
            ];
        });

        return [
            'paginator' => $this->paginar($itens, $page, $perPage),
            'context' => ['resumo' => $resumo],
        ];
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array{paginator: LengthAwarePaginator, context: array<string, mixed>}
     */
    private function aniversariantes(int $empresaId, int $page, int $perPage, array $filtros): array
    {
        $mes = (int) ($filtros['mes'] ?? (int) date('n'));
        if ($mes < 1 || $mes > 12) {
            $mes = (int) date('n');
        }

        $query = $this->baseAdmitidos($empresaId)
            ->whereNotNull('c.nascimento')
            ->whereMonth('c.nascimento', $mes)
            ->select([
                'fc.id as feedback_id',
                'c.id as curriculo_id',
                'c.nome',
                'c.email',
                'c.nascimento',
            ]);

        if (DB::connection()->getDriverName() === 'sqlite') {
            $query->orderBy('c.nascimento');
        } else {
            $query->orderByRaw('DAY(c.nascimento)');
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page)
            ->through(function ($row) {
                $pid = ApiExternaId::encode((int) $row->feedback_id);
                $nasc = $this->normalizarData($row->nascimento);
                $diaMes = $nasc ? Carbon::parse($nasc)->format('d/m') : null;

                return [
                    'processo_public_id' => $pid,
                    'curriculo_public_id' => ApiExternaId::encode($row->curriculo_id),
                    'nome' => $row->nome,
                    'email' => $row->email,
                    'aniversario' => $diaMes,
                    'hoje' => $nasc ? Carbon::parse($nasc)->isBirthday() : false,
                    'links' => [
                        'processo' => url('/api/v3/processos/' . $pid),
                        'curriculo' => url('/api/v3/curriculos/' . ApiExternaId::encode($row->curriculo_id)),
                    ],
                ];
            });

        return [
            'paginator' => $paginator,
            'context' => ['mes' => $mes],
        ];
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array{paginator: LengthAwarePaginator, context: array<string, mixed>}
     */
    private function nps(int $empresaId, int $page, int $perPage, array $filtros): array
    {
        if (! Schema::hasTable('nps_respostas')) {
            return ['paginator' => $this->paginarVazio($page, $perPage), 'context' => ['resumo' => ['total_respostas' => 0]]];
        }

        $query = DB::table('nps_respostas as r')
            ->where('r.empresa_id', $empresaId)
            ->select(['r.id', 'r.user_id', 'r.nps_ciclo_id', 'r.created_at'])
            ->orderByDesc('r.created_at');

        if (Schema::hasColumn('nps_respostas', 'deleted_at')) {
            $query->whereNull('r.deleted_at');
        }
        if (! empty($filtros['data_de'])) {
            $query->whereDate('r.created_at', '>=', $filtros['data_de']);
        }
        if (! empty($filtros['data_ate'])) {
            $query->whereDate('r.created_at', '<=', $filtros['data_ate']);
        }
        if (! empty($filtros['ciclo_id'])) {
            $query->where('r.nps_ciclo_id', (int) $filtros['ciclo_id']);
        }

        $total = (clone $query)->count();

        $paginator = $query->paginate($perPage, ['*'], 'page', $page)
            ->through(fn ($row) => [
                'public_id' => ApiExternaId::encode((int) $row->id),
                'ciclo_public_id' => ApiExternaId::encode($row->nps_ciclo_id),
                'created_at' => $row->created_at,
            ]);

        $media = null;
        if (Schema::hasTable('nps_resposta_itens') && $total > 0) {
            $ids = DB::table('nps_respostas')->where('empresa_id', $empresaId)->pluck('id');
            $media = round((float) DB::table('nps_resposta_itens')->whereIn('nps_resposta_id', $ids)->avg('nota'), 2);
        }

        return [
            'paginator' => $paginator,
            'context' => [
                'resumo' => [
                    'total_respostas' => $total,
                    'media_geral' => $media,
                ],
            ],
        ];
    }

    private function baseAdmitidos(int $empresaId)
    {
        return DB::table('feedback_curriculos as fc')
            ->join('admissoes as a', function ($join) {
                $join->on('a.feedback_id', '=', 'fc.id')->whereNull('a.deleted_at');
            })
            ->leftJoin('demissaos as d', 'd.feedback_id', '=', 'fc.id')
            ->leftJoin('curriculos as c', function ($join) {
                $join->on('c.id', '=', 'fc.curriculo_id')->whereNull('c.deleted_at');
            })
            ->where('fc.empresa_id', $empresaId)
            ->whereNull('fc.deleted_at')
            ->whereNull('d.id')
            ->where('a.status', Admissao::STATUS_ADMISSAO_ADMITIDO);
    }

    /**
     * @param  Collection<int, mixed>|iterable<int, mixed>  $items
     */
    private function paginar(iterable $items, int $page, int $perPage): LengthAwarePaginator
    {
        $collection = $items instanceof Collection ? $items : collect($items);
        $total = $collection->count();
        $page = max(1, $page);
        $slice = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        return new Paginator($slice, $total, $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    private function paginarVazio(int $page, int $perPage): LengthAwarePaginator
    {
        return $this->paginar(collect(), $page, $perPage);
    }

    private function normalizarData(mixed $valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }
        $valor = trim((string) $valor);
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $valor)) {
            return substr($valor, 0, 10);
        }
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $valor, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }

        try {
            return Carbon::parse($valor)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /** Dias de $de até $ate (negativo = já passou). */
    private function diasEntre(string $de, ?string $ate): int
    {
        if (! $ate) {
            return 0;
        }
        $ini = strtotime($de . ' 00:00:00');
        $fim = strtotime($ate . ' 00:00:00');
        if ($ini === false || $fim === false) {
            return 0;
        }

        return (int) round(($fim - $ini) / 86400);
    }
}
