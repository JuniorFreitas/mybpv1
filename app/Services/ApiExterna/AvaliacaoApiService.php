<?php

namespace App\Services\ApiExterna;

use App\Support\ApiExterna\ApiExternaId;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * API v3 Avaliações de desempenho (somente leitura): campanhas, feedbacks,
 * respostas, resultado consolidado e catálogos (tipos / tópicos).
 */
class AvaliacaoApiService
{
    private const AVALIACAO_COLS = [
        'a.id',
        'a.titulo',
        'a.ano_avaliacao',
        'a.status',
        'a.ativo',
        'a.auto_avaliacao',
        'a.tipo_pj',
        'a.avaliacao_tipo_id',
        'a.data_inicio_prazo',
        'a.data_fim_prazo',
        'a.fluxo',
        'a.mostrar_notas_avaliador_final',
    ];

    /**
     * @param  array{
     *   q?:string,
     *   ano_avaliacao?:int,
     *   tipo_pj?:?bool,
     *   avaliacao_tipo_id?:int
     * }  $filtros
     */
    public function listar(
        int $empresaId,
        int $page,
        int $perPage,
        ?string $status = null,
        ?bool $ativo = null,
        array $filtros = []
    ): LengthAwarePaginator {
        $query = DB::table('avaliacoes as a')
            ->leftJoin('avaliacoes_tipos as t', 't.id', '=', 'a.avaliacao_tipo_id')
            ->select(array_merge(self::AVALIACAO_COLS, [
                't.nome as tipo_nome',
                't.descricao as tipo_descricao',
                't.ativo as tipo_ativo',
            ]))
            ->where('a.empresa_id', $empresaId)
            ->when($status, fn ($q) => $q->where('a.status', $status))
            ->when($ativo !== null, fn ($q) => $q->where('a.ativo', $ativo))
            ->orderByDesc('a.id');

        $this->aplicarFiltrosCampanha($query, $filtros);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $ids = collect($paginator->items())->pluck('id')->map(fn ($id) => (int) $id)->all();
        $counts = $this->carregarContagensFeedback($empresaId, $ids);

        return $paginator->through(function ($row) use ($counts) {
            return $this->mapCampanha($row, $counts->get((int) $row->id));
        });
    }

    public function detalhe(int $empresaId, int $id): ?array
    {
        $row = DB::table('avaliacoes as a')
            ->leftJoin('avaliacoes_tipos as t', 't.id', '=', 'a.avaliacao_tipo_id')
            ->select(array_merge(self::AVALIACAO_COLS, [
                't.nome as tipo_nome',
                't.descricao as tipo_descricao',
                't.ativo as tipo_ativo',
            ]))
            ->where('a.empresa_id', $empresaId)
            ->where('a.id', $id)
            ->first();

        if (! $row) {
            return null;
        }

        $counts = $this->carregarContagensFeedback($empresaId, [$id]);

        return $this->mapCampanha($row, $counts->get($id), detalhe: true);
    }

    /**
     * @param  array{
     *   status?:string,
     *   origem_feedback?:string,
     *   principal?:?bool,
     *   funcionario_id?:int,
     *   avaliador_id?:int,
     *   tipo_avaliador_id?:int,
     *   q?:string
     * }  $filtros
     */
    public function feedbacks(
        int $empresaId,
        int $avaliacaoId,
        int $page,
        int $perPage,
        array $filtros = []
    ): ?LengthAwarePaginator {
        if (! $this->campanhaExiste($empresaId, $avaliacaoId)) {
            return null;
        }

        $query = $this->baseFeedbackQuery($empresaId, $avaliacaoId);
        $this->aplicarFiltrosFeedback($query, $filtros);

        return $query
            ->orderByDesc('f.id')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(fn ($row) => $this->mapFeedback($row, comComentario: false));
    }

    public function feedbackDetalhe(int $empresaId, int $avaliacaoId, int $feedbackId): ?array
    {
        if (! $this->campanhaExiste($empresaId, $avaliacaoId)) {
            return null;
        }

        $row = $this->baseFeedbackQuery($empresaId, $avaliacaoId)
            ->where('f.id', $feedbackId)
            ->first();

        if (! $row) {
            return null;
        }

        $mapped = $this->mapFeedback($row, comComentario: true);
        $mapped['respostas'] = $this->carregarRespostas($empresaId, [$feedbackId])->get($feedbackId) ?? [];
        $mapped['pdi_cadastrado'] = $this->pdiCadastrado((int) $row->id, (int) ($row->avaliador_id ?? 0));

        return $mapped;
    }

    public function feedbackResultado(int $empresaId, int $avaliacaoId, int $feedbackId): ?array
    {
        $feedback = $this->feedbackDetalhe($empresaId, $avaliacaoId, $feedbackId);
        if (! $feedback) {
            return null;
        }

        $campanha = DB::table('avaliacoes')
            ->select(['id', 'titulo', 'auto_avaliacao', 'fluxo', 'avaliacao_tipo_id', 'mostrar_notas_avaliador_final'])
            ->where('empresa_id', $empresaId)
            ->where('id', $avaliacaoId)
            ->first();

        if (! $campanha) {
            return null;
        }

        $funcionarioId = ApiExternaId::decode((string) ($feedback['funcionario']['public_id'] ?? ''));
        if ($funcionarioId === null) {
            return null;
        }

        $fluxoEtapas = $this->fluxoEtapas($campanha->fluxo, (bool) $campanha->auto_avaliacao);

        $etapas = $this->baseFeedbackQuery($empresaId, $avaliacaoId)
            ->where('f.funcionario_id', $funcionarioId)
            ->orderBy('f.id')
            ->get()
            ->map(fn ($row) => $this->mapFeedback($row, comComentario: true))
            ->values()
            ->all();

        $feedbackIds = collect($etapas)->pluck('public_id')
            ->map(fn ($pid) => ApiExternaId::decode((string) $pid))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $respostasPorFeedback = $this->carregarRespostas($empresaId, $feedbackIds);
        $medias = $this->calcularMediasPorTopico($respostasPorFeedback, $etapas);

        $tipoNome = DB::table('avaliacoes_tipos')->where('id', $campanha->avaliacao_tipo_id)->value('nome');

        $principalId = collect($etapas)
            ->first(fn ($e) => ! empty($e['principal']))['public_id'] ?? $feedback['public_id'];
        $principalNumeric = ApiExternaId::decode((string) $principalId);
        $planos = $principalNumeric
            ? $this->carregarPlanosAcao($empresaId, $principalNumeric)
            : [];

        $total = count($etapas);
        $concluidas = collect($etapas)->filter(
            fn ($e) => in_array($e['status'], ['Avaliada', 'Finalizada'], true)
        )->count();

        $notaFinal = $feedback['nota_final_total']
            ?? collect($etapas)->first(fn ($e) => ! empty($e['principal']))['nota_final_total']
            ?? null;

        return [
            'avaliacao' => [
                'public_id' => ApiExternaId::encode($avaliacaoId),
                'titulo' => $campanha->titulo,
                'tipo' => $tipoNome,
                'fluxo_etapas' => $fluxoEtapas,
            ],
            'feedback' => [
                'public_id' => $feedback['public_id'],
                'status' => $feedback['status'],
                'principal' => $feedback['principal'],
            ],
            'funcionario' => $feedback['funcionario'],
            'status_avaliacao' => $feedback['status'],
            'total_etapas' => $total,
            'total_etapas_concluidas' => $concluidas,
            'nota_final' => $notaFinal,
            'etapas' => $etapas,
            'medias_por_topico' => $medias,
            'planos_acao' => $planos,
            'links' => [
                'self' => url('/api/v3/avaliacoes/' . ApiExternaId::encode($avaliacaoId) . '/feedbacks/' . $feedback['public_id'] . '/resultado'),
                'feedback' => url('/api/v3/avaliacoes/' . ApiExternaId::encode($avaliacaoId) . '/feedbacks/' . $feedback['public_id']),
                'avaliacao' => url('/api/v3/avaliacoes/' . ApiExternaId::encode($avaliacaoId)),
                'colaborador_fluxo' => url(
                    '/api/v3/avaliacoes/' . ApiExternaId::encode($avaliacaoId)
                    . '/colaboradores/' . ($feedback['funcionario']['public_id'] ?? '')
                    . '/feedbacks'
                ),
            ],
        ];
    }

    public function colaboradorFeedbacks(int $empresaId, int $avaliacaoId, int $funcionarioId): ?array
    {
        if (! $this->campanhaExiste($empresaId, $avaliacaoId)) {
            return null;
        }

        $existe = DB::table('avaliacoes_feedbacks')
            ->where('empresa_id', $empresaId)
            ->where('avaliacao_id', $avaliacaoId)
            ->where('funcionario_id', $funcionarioId)
            ->exists();

        if (! $existe) {
            return null;
        }

        $itens = $this->baseFeedbackQuery($empresaId, $avaliacaoId)
            ->where('f.funcionario_id', $funcionarioId)
            ->orderBy('f.id')
            ->get()
            ->map(fn ($row) => $this->mapFeedback($row, comComentario: false))
            ->values()
            ->all();

        $avaliacaoPid = ApiExternaId::encode($avaliacaoId);
        $funcPid = ApiExternaId::encode($funcionarioId);

        return [
            'avaliacao_public_id' => $avaliacaoPid,
            'funcionario_public_id' => $funcPid,
            'feedbacks' => $itens,
            'links' => [
                'self' => url('/api/v3/avaliacoes/' . $avaliacaoPid . '/colaboradores/' . $funcPid . '/feedbacks'),
                'avaliacao' => url('/api/v3/avaliacoes/' . $avaliacaoPid),
            ],
        ];
    }

    public function tipos(int $empresaId, ?bool $ativo = true, ?bool $tipoPj = null): array
    {
        return DB::table('avaliacoes_tipos')
            ->select(['id', 'nome', 'descricao', 'ativo', 'tipo_pj'])
            ->where('empresa_id', $empresaId)
            ->when($ativo !== null, fn ($q) => $q->where('ativo', $ativo))
            ->when($tipoPj !== null, fn ($q) => $q->where('tipo_pj', $tipoPj))
            ->orderBy('nome')
            ->get()
            ->map(fn ($row) => ApiExternaId::mapRow($row, ['id']))
            ->all();
    }

    public function tiposAvaliador(int $empresaId, ?bool $ativo = true, ?bool $tipoPj = null): array
    {
        return DB::table('avaliacao_avaliadores_tipos')
            ->select(['id', 'label', 'descricao', 'ativo', 'tipo_pj'])
            ->where('empresa_id', $empresaId)
            ->when($ativo !== null, fn ($q) => $q->where('ativo', $ativo))
            ->when($tipoPj !== null, fn ($q) => $q->where('tipo_pj', $tipoPj))
            ->orderBy('label')
            ->get()
            ->map(fn ($row) => ApiExternaId::mapRow($row, ['id']))
            ->all();
    }

    public function topicosDoTipo(int $empresaId, int $tipoId, ?bool $ativo = true): ?array
    {
        $tipo = DB::table('avaliacoes_tipos')
            ->select(['id', 'nome'])
            ->where('empresa_id', $empresaId)
            ->where('id', $tipoId)
            ->first();

        if (! $tipo) {
            return null;
        }

        $rows = DB::table('avaliacoes_topicos')
            ->select(['id', 'topico_pai_id', 'topico', 'topico_explicacao', 'ativo'])
            ->where('empresa_id', $empresaId)
            ->where('avaliacao_tipo_id', $tipoId)
            ->when($ativo !== null, fn ($q) => $q->where('ativo', $ativo))
            ->orderBy('id')
            ->get();

        $mapped = $rows->map(function ($row) {
            return [
                'public_id' => ApiExternaId::encode((int) $row->id),
                'topico_pai_public_id' => $row->topico_pai_id
                    ? ApiExternaId::encode((int) $row->topico_pai_id)
                    : null,
                'topico' => $row->topico,
                'topico_explicacao' => $row->topico_explicacao,
                'ativo' => (bool) $row->ativo,
            ];
        });

        $byPai = $mapped->groupBy(fn ($t) => $t['topico_pai_public_id'] ?? '');

        $arvore = ($byPai->get('') ?? collect())->map(function ($pai) use ($byPai) {
            $pai['subtopicos'] = ($byPai->get($pai['public_id']) ?? collect())->values()->all();

            return $pai;
        })->values()->all();

        return [
            'avaliacao_tipo' => [
                'public_id' => ApiExternaId::encode((int) $tipo->id),
                'nome' => $tipo->nome,
            ],
            'topicos' => $arvore,
        ];
    }

    private function campanhaExiste(int $empresaId, int $avaliacaoId): bool
    {
        return DB::table('avaliacoes')
            ->where('empresa_id', $empresaId)
            ->where('id', $avaliacaoId)
            ->exists();
    }

    private function baseFeedbackQuery(int $empresaId, int $avaliacaoId)
    {
        return DB::table('avaliacoes_feedbacks as f')
            ->leftJoin('users as func', function ($join) {
                $join->on('func.id', '=', 'f.funcionario_id')
                    ->whereNull('func.deleted_at');
            })
            ->leftJoin('users as aval', function ($join) {
                $join->on('aval.id', '=', 'f.avaliador_id')
                    ->whereNull('aval.deleted_at');
            })
            ->leftJoin('avaliacao_avaliadores_tipos as tat', 'tat.id', '=', 'f.avaliacao_tipo_id')
            ->select([
                'f.id',
                'f.avaliacao_id',
                'f.status',
                'f.origem_feedback',
                'f.principal',
                'f.tipo_pj',
                'f.funcionario_id',
                'f.avaliador_id',
                'f.avaliacao_tipo_id',
                'f.nota_final_total',
                'f.inicio_feedback',
                'f.fim_feedback',
                'f.comentario',
                'f.estado_atual',
                'f.estado_desejado',
                'func.nome as funcionario_nome',
                'aval.nome as avaliador_nome',
                'tat.label as tipo_avaliador_label',
            ])
            ->where('f.empresa_id', $empresaId)
            ->where('f.avaliacao_id', $avaliacaoId);
    }

    /**
     * @param  array{q?:string,ano_avaliacao?:int,tipo_pj?:?bool,avaliacao_tipo_id?:int}  $filtros
     */
    private function aplicarFiltrosCampanha($query, array $filtros): void
    {
        if (! empty($filtros['q'])) {
            $q = trim((string) $filtros['q']);
            $query->where('a.titulo', 'like', '%' . $q . '%');
        }

        if (! empty($filtros['ano_avaliacao'])) {
            $query->where('a.ano_avaliacao', (int) $filtros['ano_avaliacao']);
        }

        if (array_key_exists('tipo_pj', $filtros) && $filtros['tipo_pj'] !== null) {
            $query->where('a.tipo_pj', (bool) $filtros['tipo_pj']);
        }

        if (! empty($filtros['avaliacao_tipo_id'])) {
            $query->where('a.avaliacao_tipo_id', (int) $filtros['avaliacao_tipo_id']);
        }
    }

    /**
     * @param  array{
     *   status?:string,
     *   origem_feedback?:string,
     *   principal?:?bool,
     *   funcionario_id?:int,
     *   avaliador_id?:int,
     *   tipo_avaliador_id?:int,
     *   q?:string
     * }  $filtros
     */
    private function aplicarFiltrosFeedback($query, array $filtros): void
    {
        if (! empty($filtros['status'])) {
            $query->where('f.status', $filtros['status']);
        }

        if (! empty($filtros['origem_feedback'])) {
            $query->where('f.origem_feedback', $filtros['origem_feedback']);
        }

        if (array_key_exists('principal', $filtros) && $filtros['principal'] !== null) {
            $query->where('f.principal', (bool) $filtros['principal']);
        }

        if (! empty($filtros['funcionario_id'])) {
            $query->where('f.funcionario_id', (int) $filtros['funcionario_id']);
        }

        if (! empty($filtros['avaliador_id'])) {
            $query->where('f.avaliador_id', (int) $filtros['avaliador_id']);
        }

        if (! empty($filtros['tipo_avaliador_id'])) {
            $query->where('f.avaliacao_tipo_id', (int) $filtros['tipo_avaliador_id']);
        }

        if (! empty($filtros['q'])) {
            $q = trim((string) $filtros['q']);
            $query->where(function ($inner) use ($q) {
                $inner->where('func.nome', 'like', '%' . $q . '%')
                    ->orWhere('aval.nome', 'like', '%' . $q . '%');
            });
        }
    }

    /**
     * @param  list<int>  $avaliacaoIds
     * @return Collection<int, object>
     */
    private function carregarContagensFeedback(int $empresaId, array $avaliacaoIds): Collection
    {
        if ($avaliacaoIds === []) {
            return collect();
        }

        $rows = DB::table('avaliacoes_feedbacks')
            ->selectRaw('avaliacao_id')
            ->selectRaw('COUNT(*) as feedbacks_count')
            ->selectRaw("SUM(CASE WHEN status = 'Pendente' THEN 1 ELSE 0 END) as feedbacks_pendentes_count")
            ->selectRaw("SUM(CASE WHEN status = 'Finalizada' THEN 1 ELSE 0 END) as feedbacks_finalizados_count")
            ->selectRaw('COUNT(DISTINCT funcionario_id) as colaboradores_count')
            ->where('empresa_id', $empresaId)
            ->whereIn('avaliacao_id', $avaliacaoIds)
            ->groupBy('avaliacao_id')
            ->get()
            ->keyBy(fn ($r) => (int) $r->avaliacao_id);

        return $rows;
    }

    private function mapCampanha(object $row, ?object $counts, bool $detalhe = false): array
    {
        $pid = ApiExternaId::encode((int) $row->id);
        $fluxoRaw = $this->decodeFluxo($row->fluxo ?? null);
        $fluxoEtapas = $this->fluxoEtapas($row->fluxo ?? null, (bool) $row->auto_avaliacao);

        $data = [
            'public_id' => $pid,
            'titulo' => $row->titulo,
            'ano_avaliacao' => (int) $row->ano_avaliacao,
            'status' => $row->status,
            'ativo' => (bool) $row->ativo,
            'auto_avaliacao' => (bool) $row->auto_avaliacao,
            'tipo_pj' => (bool) $row->tipo_pj,
            'mostrar_notas_avaliador_final' => (bool) ($row->mostrar_notas_avaliador_final ?? false),
            'data_inicio_prazo' => $row->data_inicio_prazo,
            'data_fim_prazo' => $row->data_fim_prazo,
            'avaliacao_tipo' => $row->avaliacao_tipo_id ? [
                'public_id' => ApiExternaId::encode((int) $row->avaliacao_tipo_id),
                'nome' => $row->tipo_nome,
                'descricao' => $detalhe ? ($row->tipo_descricao ?? null) : null,
                'ativo' => isset($row->tipo_ativo) ? (bool) $row->tipo_ativo : null,
            ] : null,
            'fluxo_etapas' => $fluxoEtapas,
            'feedbacks_count' => (int) ($counts->feedbacks_count ?? 0),
            'feedbacks_pendentes_count' => (int) ($counts->feedbacks_pendentes_count ?? 0),
            'feedbacks_finalizados_count' => (int) ($counts->feedbacks_finalizados_count ?? 0),
            'colaboradores_count' => (int) ($counts->colaboradores_count ?? 0),
            'links' => [
                'self' => url('/api/v3/avaliacoes/' . $pid),
                'feedbacks' => url('/api/v3/avaliacoes/' . $pid . '/feedbacks'),
            ],
        ];

        if ($detalhe) {
            $data['fluxo'] = $fluxoRaw;
            if (is_array($data['avaliacao_tipo'])) {
                // manter descricao
            }
        } else {
            if (is_array($data['avaliacao_tipo'])) {
                unset($data['avaliacao_tipo']['descricao']);
            }
        }

        return $data;
    }

    private function mapFeedback(object $row, bool $comComentario): array
    {
        $avaliacaoPid = ApiExternaId::encode((int) $row->avaliacao_id);
        $feedbackPid = ApiExternaId::encode((int) $row->id);
        $funcPid = ApiExternaId::encode($row->funcionario_id);
        $avalPid = ApiExternaId::encode($row->avaliador_id);

        $data = [
            'public_id' => $feedbackPid,
            'avaliacao_public_id' => $avaliacaoPid,
            'status' => $row->status,
            'origem_feedback' => $row->origem_feedback,
            'principal' => (bool) $row->principal,
            'tipo_pj' => (bool) $row->tipo_pj,
            'nota_final_total' => $row->nota_final_total !== null ? (int) $row->nota_final_total : null,
            'inicio_feedback' => $row->inicio_feedback,
            'fim_feedback' => $row->fim_feedback,
            'funcionario' => $funcPid ? [
                'public_id' => $funcPid,
                'nome' => $row->funcionario_nome,
            ] : null,
            'avaliador' => $avalPid ? [
                'public_id' => $avalPid,
                'nome' => $row->avaliador_nome,
            ] : null,
            'tipo_avaliador' => $row->avaliacao_tipo_id ? [
                'public_id' => ApiExternaId::encode((int) $row->avaliacao_tipo_id),
                'label' => $row->tipo_avaliador_label,
            ] : null,
            'links' => [
                'self' => url('/api/v3/avaliacoes/' . $avaliacaoPid . '/feedbacks/' . $feedbackPid),
                'avaliacao' => url('/api/v3/avaliacoes/' . $avaliacaoPid),
                'resultado' => url('/api/v3/avaliacoes/' . $avaliacaoPid . '/feedbacks/' . $feedbackPid . '/resultado'),
                'colaborador_fluxo' => $funcPid
                    ? url('/api/v3/avaliacoes/' . $avaliacaoPid . '/colaboradores/' . $funcPid . '/feedbacks')
                    : null,
            ],
        ];

        if ($comComentario) {
            $data['comentario'] = $row->comentario;
            $data['estado_atual'] = $row->estado_atual;
            $data['estado_desejado'] = $row->estado_desejado;
        }

        return $data;
    }

    /**
     * @param  list<int>  $feedbackIds
     * @return Collection<int, list<array<string, mixed>>>
     */
    private function carregarRespostas(int $empresaId, array $feedbackIds): Collection
    {
        if ($feedbackIds === [] || ! Schema::hasTable('avaliacoes_respostas')) {
            return collect();
        }

        $rows = DB::table('avaliacoes_respostas as r')
            ->leftJoin('avaliacoes_topicos as t', 't.id', '=', 'r.topico_id')
            ->where('r.empresa_id', $empresaId)
            ->whereIn('r.avaliacao_feedback_id', $feedbackIds)
            ->select([
                'r.avaliacao_feedback_id',
                'r.topico_id',
                'r.nota',
                't.topico',
                't.topico_pai_id',
            ])
            ->orderBy('r.topico_id')
            ->get();

        return $rows->groupBy('avaliacao_feedback_id')->map(function (Collection $group) {
            return $group->map(fn ($r) => [
                'topico_public_id' => ApiExternaId::encode((int) $r->topico_id),
                'topico_pai_public_id' => $r->topico_pai_id
                    ? ApiExternaId::encode((int) $r->topico_pai_id)
                    : null,
                'topico' => $r->topico,
                'nota' => (int) $r->nota,
            ])->values()->all();
        });
    }

    /**
     * @param  Collection<int, list<array<string, mixed>>>  $respostasPorFeedback
     * @param  list<array<string, mixed>>  $etapas
     * @return list<array<string, mixed>>
     */
    private function calcularMediasPorTopico(Collection $respostasPorFeedback, array $etapas): array
    {
        $porTopico = [];

        foreach ($etapas as $etapa) {
            $fid = ApiExternaId::decode((string) $etapa['public_id']);
            if ($fid === null) {
                continue;
            }

            foreach ($respostasPorFeedback->get($fid) ?? [] as $resp) {
                $key = $resp['topico_public_id'];
                if (! isset($porTopico[$key])) {
                    $porTopico[$key] = [
                        'topico_public_id' => $key,
                        'topico_pai_public_id' => $resp['topico_pai_public_id'],
                        'topico' => $resp['topico'],
                        'notas' => [],
                        'avaliadores' => [],
                    ];
                }
                $porTopico[$key]['notas'][] = $resp['nota'];
                $porTopico[$key]['avaliadores'][] = [
                    'avaliador' => $etapa['avaliador'],
                    'tipo_avaliador' => $etapa['tipo_avaliador'],
                    'origem_feedback' => $etapa['origem_feedback'],
                    'principal' => $etapa['principal'],
                    'nota' => $resp['nota'],
                    'comentario' => $etapa['comentario'] ?? null,
                ];
            }
        }

        return collect($porTopico)->map(function ($item) {
            $notas = $item['notas'];
            $media = count($notas) > 0 ? round(array_sum($notas) / count($notas), 2) : null;

            return [
                'topico_public_id' => $item['topico_public_id'],
                'topico_pai_public_id' => $item['topico_pai_public_id'],
                'topico' => $item['topico'],
                'media' => $media,
                'avaliadores' => $item['avaliadores'],
            ];
        })->values()->all();
    }

    private function carregarPlanosAcao(int $empresaId, int $feedbackId): array
    {
        if (! Schema::hasTable('avaliacoes_resultados')) {
            return [];
        }

        return DB::table('avaliacoes_resultados as r')
            ->leftJoin('avaliacoes_topicos as t', 't.id', '=', 'r.topico_id')
            ->leftJoin('users as g', function ($join) {
                $join->on('g.id', '=', 'r.gestor_id')->whereNull('g.deleted_at');
            })
            ->where('r.empresa_id', $empresaId)
            ->where('r.avaliacao_feedback_id', $feedbackId)
            ->select([
                'r.id',
                'r.topico_id',
                'r.plano_de_acao',
                'r.responsavel',
                'r.inicio',
                'r.termino',
                'r.status',
                'r.gestor_id',
                't.topico',
                'g.nome as gestor_nome',
            ])
            ->orderBy('r.id')
            ->get()
            ->map(fn ($r) => [
                'public_id' => ApiExternaId::encode((int) $r->id),
                'topico_public_id' => $r->topico_id ? ApiExternaId::encode((int) $r->topico_id) : null,
                'topico' => $r->topico,
                'plano_de_acao' => $r->plano_de_acao,
                'responsavel' => $r->responsavel,
                'inicio' => $r->inicio,
                'termino' => $r->termino,
                'status' => $r->status,
                'gestor' => $r->gestor_id ? [
                    'public_id' => ApiExternaId::encode((int) $r->gestor_id),
                    'nome' => $r->gestor_nome,
                ] : null,
            ])
            ->all();
    }

    private function pdiCadastrado(int $feedbackId, int $gestorId): bool
    {
        if (! Schema::hasTable('avaliacoes_resultados') || $gestorId <= 0) {
            return false;
        }

        return DB::table('avaliacoes_resultados')
            ->where('avaliacao_feedback_id', $feedbackId)
            ->where('gestor_id', $gestorId)
            ->exists();
    }

    private function decodeFluxo(mixed $fluxo): array
    {
        if (is_array($fluxo)) {
            return $fluxo;
        }

        if (is_string($fluxo) && $fluxo !== '') {
            $decoded = json_decode($fluxo, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }

    /**
     * @return list<array{public_id:?string,label:string,principal:bool}>
     */
    private function fluxoEtapas(mixed $fluxo, bool $autoAvaliacao): array
    {
        $etapas = collect($this->decodeFluxo($fluxo))->map(function ($item) {
            $id = isset($item['id']) ? (int) $item['id'] : null;
            $principal = (bool) ($item['principal'] ?? false);
            $label = (string) ($item['label'] ?? '');
            if ($principal) {
                $label .= ' (Avaliador Final)';
            }

            return [
                'public_id' => $id && $id > 0 ? ApiExternaId::encode($id) : null,
                'label' => $label,
                'principal' => $principal,
            ];
        });

        if ($autoAvaliacao) {
            $etapas = $etapas->prepend([
                'public_id' => null,
                'label' => 'Auto Avaliação',
                'principal' => false,
            ]);
        }

        return $etapas->values()->all();
    }
}
