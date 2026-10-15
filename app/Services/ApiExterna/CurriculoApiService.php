<?php

namespace App\Services\ApiExterna;

use App\Support\ApiExterna\ApiExternaId;
use App\Support\ApiExterna\ApiExternaResponse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * API v3 Currículos (somente leitura): listagem filtrável, busca, detalhe rico
 * e nested (feedbacks, documentos, experiências).
 */
class CurriculoApiService
{
    private const CURRICULO_COLS = [
        'c.id',
        'c.nome',
        'c.estado_civil',
        'c.naturalidade',
        'c.nacionalidade',
        'c.carteira_trabalho',
        'c.cnh',
        'c.cnh_vencimento',
        'c.sexo',
        'c.cpf',
        'c.rg',
        'c.rg_data_emissao',
        'c.orgao_expeditor',
        'c.logradouro',
        'c.end_numero',
        'c.complemento',
        'c.bairro',
        'c.municipio',
        'c.uf',
        'c.cep',
        'c.filiacao_pai',
        'c.filiacao_mae',
        'c.pcd',
        'c.nascimento',
        'c.email',
        'c.disponibilidade_sabado',
        'c.disponibilidade_domingo',
        'c.formacao',
        'c.formacao_curso',
        'c.formacao_status',
        'c.vaga_pretendida',
        'c.created_at',
        'c.updated_at',
    ];

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function listar(
        int $empresaId,
        int $page,
        int $perPage,
        array $filtros = []
    ): LengthAwarePaginator {
        $ultimoFeedback = DB::table('feedback_curriculos as fc2')
            ->selectRaw('MAX(fc2.id)')
            ->whereColumn('fc2.curriculo_id', 'c.id')
            ->where('fc2.empresa_id', $empresaId)
            ->whereNull('fc2.deleted_at');

        $feedbacksCount = DB::table('feedback_curriculos as fc3')
            ->selectRaw('COUNT(*)')
            ->whereColumn('fc3.curriculo_id', 'c.id')
            ->where('fc3.empresa_id', $empresaId)
            ->whereNull('fc3.deleted_at');

        $query = $this->baseQuery($empresaId)
            ->select([
                'c.id',
                'c.nome',
                'c.cpf',
                'c.email',
                'c.municipio',
                'c.uf',
                'c.pcd',
                'c.vaga_pretendida',
                'c.created_at',
                'c.updated_at',
            ])
            ->selectSub($ultimoFeedback, 'ultimo_feedback_id')
            ->selectSub($feedbacksCount, 'feedbacks_count')
            ->orderByDesc('c.id');

        if (Schema::hasColumn('curriculos', 'uf_vaga')) {
            $query->addSelect('c.uf_vaga');
        }
        if (Schema::hasColumn('curriculos', 'lido')) {
            $query->addSelect('c.lido');
        }

        $this->aplicarFiltros($query, $empresaId, $filtros);

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $ultimoIds = collect($paginator->items())
            ->pluck('ultimo_feedback_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
        $ultimos = $this->carregarFeedbacksResumo($empresaId, $ultimoIds);

        return $paginator->through(function ($row) use ($ultimos) {
            $pid = ApiExternaId::encode((int) $row->id);
            $ultimoId = $row->ultimo_feedback_id ? (int) $row->ultimo_feedback_id : null;

            return [
                'public_id' => $pid,
                'nome' => $row->nome,
                'cpf' => ApiExternaResponse::mascararCpf($row->cpf),
                'email' => $row->email,
                'municipio' => $row->municipio,
                'uf' => $row->uf,
                'uf_vaga' => $row->uf_vaga ?? null,
                'pcd' => isset($row->pcd) ? (bool) $row->pcd : null,
                'lido' => isset($row->lido) ? (bool) $row->lido : null,
                'vaga_pretendida_public_id' => $this->encodeVagaPretendida($row->vaga_pretendida),
                'feedbacks_count' => (int) ($row->feedbacks_count ?? 0),
                'ultimo_feedback' => $ultimoId ? ($ultimos[$ultimoId] ?? [
                    'public_id' => ApiExternaId::encode($ultimoId),
                ]) : null,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
                'links' => [
                    'self' => url('/api/v3/curriculos/' . $pid),
                    'feedbacks' => url('/api/v3/curriculos/' . $pid . '/feedbacks'),
                ],
            ];
        });
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

        return $this->listar($empresaId, $page, $perPage, $filtros);
    }

    public function detalhe(int $empresaId, int $curriculoId): ?array
    {
        if (! $this->pertenceEmpresa($empresaId, $curriculoId)) {
            return null;
        }

        $cols = self::CURRICULO_COLS;
        if (Schema::hasColumn('curriculos', 'uf_vaga')) {
            $cols[] = 'c.uf_vaga';
        }
        if (Schema::hasColumn('curriculos', 'lido')) {
            $cols[] = 'c.lido';
        }

        $row = DB::table('curriculos as c')
            ->select($cols)
            ->where('c.id', $curriculoId)
            ->whereNull('c.deleted_at')
            ->first();

        if (! $row) {
            return null;
        }

        $pid = ApiExternaId::encode($curriculoId);
        $escolaridade = $this->carregarEscolaridade($row->formacao);
        $telefones = $this->carregarTelefones([$curriculoId])->get($curriculoId) ?? collect();
        $dependentes = $this->carregarDependentes([$curriculoId])->get($curriculoId) ?? collect();
        $feedbacks = $this->listarFeedbacksCollection($empresaId, $curriculoId);

        return [
            'public_id' => $pid,
            'nome' => $row->nome,
            'estado_civil' => $row->estado_civil,
            'naturalidade' => $row->naturalidade,
            'nacionalidade' => $row->nacionalidade,
            'carteira_trabalho' => $row->carteira_trabalho,
            'cnh' => $row->cnh,
            'cnh_vencimento' => $row->cnh_vencimento,
            'sexo' => $row->sexo,
            'cpf' => ApiExternaResponse::mascararCpf($row->cpf),
            'rg' => $row->rg,
            'rg_data_emissao' => $row->rg_data_emissao,
            'orgao_expeditor' => $row->orgao_expeditor,
            'logradouro' => $row->logradouro,
            'end_numero' => $row->end_numero,
            'complemento' => $row->complemento,
            'bairro' => $row->bairro,
            'municipio' => $row->municipio,
            'uf' => $row->uf,
            'uf_vaga' => $row->uf_vaga ?? null,
            'cep' => $row->cep,
            'filiacao_pai' => $row->filiacao_pai,
            'filiacao_mae' => $row->filiacao_mae,
            'pcd' => isset($row->pcd) ? (bool) $row->pcd : null,
            'lido' => isset($row->lido) ? (bool) $row->lido : null,
            'nascimento' => $row->nascimento,
            'email' => $row->email,
            'disponibilidade_sabado' => isset($row->disponibilidade_sabado) ? (bool) $row->disponibilidade_sabado : null,
            'disponibilidade_domingo' => isset($row->disponibilidade_domingo) ? (bool) $row->disponibilidade_domingo : null,
            'formacao_curso' => $row->formacao_curso,
            'formacao_status' => $row->formacao_status,
            'vaga_pretendida_public_id' => $this->encodeVagaPretendida($row->vaga_pretendida),
            'escolaridade' => $escolaridade,
            'telefones' => $telefones->map(fn ($t) => $this->mapTelefone($t))->values()->all(),
            'dependentes' => $dependentes->map(fn ($d) => [
                'public_id' => ApiExternaId::encode((int) $d->id),
                'nome' => $d->nome,
                'nascimento' => $d->nascimento ?? null,
                'tipo' => $d->tipo ?? null,
                'outro_tipo' => $d->outro_tipo ?? null,
                'cpf' => ApiExternaResponse::mascararCpf($d->cpf ?? null),
            ])->values()->all(),
            'feedbacks' => $feedbacks,
            'feedbacks_count' => count($feedbacks),
            'experiencias_count' => $this->contarExperiencias($curriculoId),
            'documentos_count' => $this->contarDocumentos($curriculoId),
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
            'links' => [
                'self' => url('/api/v3/curriculos/' . $pid),
                'feedbacks' => url('/api/v3/curriculos/' . $pid . '/feedbacks'),
                'documentos' => url('/api/v3/curriculos/' . $pid . '/documentos'),
                'experiencias' => url('/api/v3/curriculos/' . $pid . '/experiencias'),
            ],
        ];
    }

    /**
     * @param  array{status?:string,selecionado?:string}  $filtros
     */
    public function feedbacks(
        int $empresaId,
        int $curriculoId,
        int $page,
        int $perPage,
        array $filtros = []
    ): ?LengthAwarePaginator {
        if (! $this->pertenceEmpresa($empresaId, $curriculoId)) {
            return null;
        }

        $pid = ApiExternaId::encode($curriculoId);

        $query = DB::table('feedback_curriculos')
            ->select([
                'id',
                'status',
                'selecionado',
                'interesse',
                'contato_realizado',
                'vaga_id',
                'vagas_abertas_id',
                'empresa_id',
                'cliente_id',
                'data_entrevista',
                'created_at',
                'updated_at',
            ])
            ->where('empresa_id', $empresaId)
            ->where('curriculo_id', $curriculoId)
            ->whereNull('deleted_at')
            ->when(! empty($filtros['status']), fn ($q) => $q->where('status', $filtros['status']))
            ->when(! empty($filtros['selecionado']), fn ($q) => $q->where('selecionado', $filtros['selecionado']))
            ->orderByDesc('id');

        return $query->paginate($perPage, ['*'], 'page', $page)
            ->through(function ($f) use ($pid) {
                $fp = ApiExternaId::encode((int) $f->id);

                return [
                    'public_id' => $fp,
                    'status' => $f->status,
                    'selecionado' => $f->selecionado,
                    'interesse' => isset($f->interesse) ? (bool) $f->interesse : null,
                    'contato_realizado' => isset($f->contato_realizado) ? (bool) $f->contato_realizado : null,
                    'vaga_public_id' => ApiExternaId::encode($f->vaga_id),
                    'vaga_aberta_public_id' => ApiExternaId::encode($f->vagas_abertas_id),
                    'empresa_public_id' => ApiExternaId::encode($f->cliente_id ?: $f->empresa_id),
                    'data_entrevista' => $f->data_entrevista,
                    'created_at' => $f->created_at,
                    'updated_at' => $f->updated_at,
                    'links' => [
                        'self' => url('/api/v3/processos/' . $fp),
                        'curriculo' => url('/api/v3/curriculos/' . $pid),
                    ],
                ];
            });
    }

    public function documentos(int $empresaId, int $curriculoId): ?array
    {
        if (! $this->pertenceEmpresa($empresaId, $curriculoId)) {
            return null;
        }

        $pid = ApiExternaId::encode($curriculoId);
        $porTipo = [];

        if (Schema::hasTable('documentos_curriculos') && Schema::hasTable('arquivos')) {
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
            'documentos' => $porTipo,
            'links' => [
                'self' => url('/api/v3/curriculos/' . $pid . '/documentos'),
                'curriculo' => url('/api/v3/curriculos/' . $pid),
            ],
        ];
    }

    public function experiencias(int $empresaId, int $curriculoId): ?array
    {
        if (! $this->pertenceEmpresa($empresaId, $curriculoId)) {
            return null;
        }

        $pid = ApiExternaId::encode($curriculoId);
        $itens = [];

        if (Schema::hasTable('curriculo_experiencias')) {
            $itens = DB::table('curriculo_experiencias')
                ->where('curriculo_id', $curriculoId)
                ->select(['id', 'empresa', 'cargo', 'principais_atv', 'data_inicio', 'data_fim'])
                ->orderByDesc('id')
                ->get()
                ->map(fn ($e) => [
                    'public_id' => ApiExternaId::encode((int) $e->id),
                    'empresa' => $e->empresa,
                    'cargo' => $e->cargo,
                    'principais_atv' => $e->principais_atv,
                    'data_inicio' => $e->data_inicio,
                    'data_fim' => $e->data_fim,
                ])
                ->all();
        }

        return [
            'public_id' => $pid,
            'experiencias' => $itens,
            'links' => [
                'self' => url('/api/v3/curriculos/' . $pid . '/experiencias'),
                'curriculo' => url('/api/v3/curriculos/' . $pid),
            ],
        ];
    }

    private function baseQuery(int $empresaId)
    {
        return DB::table('curriculos as c')
            ->whereNull('c.deleted_at')
            ->whereExists(function ($q) use ($empresaId) {
                $q->select(DB::raw(1))
                    ->from('feedback_curriculos as fc')
                    ->whereColumn('fc.curriculo_id', 'c.id')
                    ->where('fc.empresa_id', $empresaId)
                    ->whereNull('fc.deleted_at');
            });
    }

    private function pertenceEmpresa(int $empresaId, int $curriculoId): bool
    {
        return DB::table('feedback_curriculos')
            ->where('empresa_id', $empresaId)
            ->where('curriculo_id', $curriculoId)
            ->whereNull('deleted_at')
            ->exists();
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    private function aplicarFiltros($query, int $empresaId, array $filtros): void
    {
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

        if (! empty($filtros['uf'])) {
            $uf = strtoupper(trim((string) $filtros['uf']));
            $query->where(function ($inner) use ($uf) {
                $inner->where('c.uf', $uf);
                if (Schema::hasColumn('curriculos', 'uf_vaga')) {
                    $inner->orWhere('c.uf_vaga', $uf);
                }
            });
        }

        if (array_key_exists('pcd', $filtros) && $filtros['pcd'] !== null) {
            $query->where('c.pcd', (bool) $filtros['pcd']);
        }

        if (array_key_exists('lido', $filtros) && $filtros['lido'] !== null && Schema::hasColumn('curriculos', 'lido')) {
            $query->where('c.lido', (bool) $filtros['lido']);
        }

        if (! empty($filtros['vaga_id'])) {
            $query->where('c.vaga_pretendida', (int) $filtros['vaga_id']);
        }

        if (! empty($filtros['updated_de'])) {
            $query->whereDate('c.updated_at', '>=', $filtros['updated_de']);
        }

        if (! empty($filtros['updated_ate'])) {
            $query->whereDate('c.updated_at', '<=', $filtros['updated_ate']);
        }

        if (! empty($filtros['status']) || ! empty($filtros['selecionado'])) {
            $status = $filtros['status'] ?? null;
            $selecionado = $filtros['selecionado'] ?? null;
            $query->whereExists(function ($q) use ($empresaId, $status, $selecionado) {
                $q->select(DB::raw(1))
                    ->from('feedback_curriculos as fcf')
                    ->whereColumn('fcf.curriculo_id', 'c.id')
                    ->where('fcf.empresa_id', $empresaId)
                    ->whereNull('fcf.deleted_at')
                    ->when($status, fn ($qq) => $qq->where('fcf.status', $status))
                    ->when($selecionado, fn ($qq) => $qq->where('fcf.selecionado', $selecionado));
            });
        }
    }

    /**
     * @param  list<int>  $ids
     * @return array<int, array<string, mixed>>
     */
    private function carregarFeedbacksResumo(int $empresaId, array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return DB::table('feedback_curriculos')
            ->where('empresa_id', $empresaId)
            ->whereIn('id', $ids)
            ->whereNull('deleted_at')
            ->select(['id', 'status', 'selecionado', 'vagas_abertas_id'])
            ->get()
            ->mapWithKeys(fn ($f) => [
                (int) $f->id => [
                    'public_id' => ApiExternaId::encode((int) $f->id),
                    'status' => $f->status,
                    'selecionado' => $f->selecionado,
                    'vaga_aberta_public_id' => ApiExternaId::encode($f->vagas_abertas_id),
                ],
            ])
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function listarFeedbacksCollection(int $empresaId, int $curriculoId): array
    {
        return DB::table('feedback_curriculos')
            ->select([
                'id',
                'status',
                'selecionado',
                'interesse',
                'vaga_id',
                'vagas_abertas_id',
                'empresa_id',
                'cliente_id',
                'created_at',
            ])
            ->where('empresa_id', $empresaId)
            ->where('curriculo_id', $curriculoId)
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->get()
            ->map(function ($f) {
                $fp = ApiExternaId::encode((int) $f->id);

                return [
                    'public_id' => $fp,
                    'status' => $f->status,
                    'selecionado' => $f->selecionado,
                    'interesse' => isset($f->interesse) ? (bool) $f->interesse : null,
                    'vaga_public_id' => ApiExternaId::encode($f->vaga_id),
                    'vaga_aberta_public_id' => ApiExternaId::encode($f->vagas_abertas_id),
                    'empresa_public_id' => ApiExternaId::encode($f->cliente_id ?: $f->empresa_id),
                    'created_at' => $f->created_at,
                    'links' => [
                        'self' => url('/api/v3/processos/' . $fp),
                    ],
                ];
            })
            ->all();
    }

    private function carregarEscolaridade(mixed $formacaoId): ?array
    {
        if (! $formacaoId || ! Schema::hasTable('escolaridades')) {
            return null;
        }

        $esc = DB::table('escolaridades')
            ->where('id', (int) $formacaoId)
            ->select(['id', 'tipo'])
            ->first();

        if (! $esc) {
            return null;
        }

        return [
            'public_id' => ApiExternaId::encode((int) $esc->id),
            'tipo' => $esc->tipo,
        ];
    }

    /**
     * @param  list<int>  $curriculoIds
     * @return Collection<int, Collection<int, object>>
     */
    private function carregarTelefones(array $curriculoIds): Collection
    {
        if ($curriculoIds === []) {
            return collect();
        }

        return DB::table('curriculo_telefone')
            ->whereIn('curriculo_id', $curriculoIds)
            ->select(['id', 'curriculo_id', 'numero', 'pais', 'tipo', 'principal'])
            ->orderBy('id')
            ->get()
            ->groupBy('curriculo_id');
    }

    /**
     * @param  list<int>  $curriculoIds
     * @return Collection<int, Collection<int, object>>
     */
    private function carregarDependentes(array $curriculoIds): Collection
    {
        if ($curriculoIds === [] || ! Schema::hasTable('usuario_dependentes')) {
            return collect();
        }

        return DB::table('usuario_dependentes')
            ->whereIn('user_id', $curriculoIds)
            ->select(['id', 'user_id', 'nome', 'nascimento', 'tipo', 'outro_tipo', 'cpf'])
            ->orderBy('id')
            ->get()
            ->groupBy('user_id');
    }

    private function mapTelefone(object $tel): array
    {
        return [
            'public_id' => ApiExternaId::encode((int) $tel->id),
            'numero' => $tel->numero,
            'pais' => $tel->pais ?? null,
            'tipo' => $tel->tipo ?? null,
            'principal' => (bool) ($tel->principal ?? false),
        ];
    }

    private function encodeVagaPretendida(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return ApiExternaId::encode((int) $value);
        }

        return null;
    }

    private function contarExperiencias(int $curriculoId): int
    {
        if (! Schema::hasTable('curriculo_experiencias')) {
            return 0;
        }

        return (int) DB::table('curriculo_experiencias')
            ->where('curriculo_id', $curriculoId)
            ->count();
    }

    private function contarDocumentos(int $curriculoId): int
    {
        if (! Schema::hasTable('documentos_curriculos')) {
            return 0;
        }

        return (int) DB::table('documentos_curriculos')
            ->where('curriculo_id', $curriculoId)
            ->count();
    }
}
