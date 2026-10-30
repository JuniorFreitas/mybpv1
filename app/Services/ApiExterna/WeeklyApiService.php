<?php

namespace App\Services\ApiExterna;

use App\Support\ApiExterna\ApiExternaId;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WeeklyApiService
{
    /**
     * @param  array<string, mixed>  $filtros
     */
    public function listar(int $empresaId, int $page, int $perPage, array $filtros = []): LengthAwarePaginator
    {
        $query = DB::table('quadros as q')
            ->where('q.empresa_id', $empresaId)
            ->whereNull('q.deleted_at')
            ->select(['q.id', 'q.titulo', 'q.created_at', 'q.updated_at'])
            ->selectSub($this->contagemListas(), 'listas_count')
            ->selectSub($this->contagemTarefas(), 'tarefas_count')
            ->selectSub($this->contagemMembros(), 'membros_count')
            ->orderBy('q.titulo')
            ->orderBy('q.id');

        if (! empty($filtros['q'])) {
            $query->where('q.titulo', 'like', '%' . $filtros['q'] . '%');
        }

        return $query->paginate($perPage, ['*'], 'page', $page)
            ->through(fn ($row) => $this->mapQuadro($row));
    }

    public function detalhe(int $empresaId, int $quadroId): ?array
    {
        $quadro = $this->quadroDaEmpresa($empresaId, $quadroId);
        if (! $quadro) {
            return null;
        }

        $mapped = $this->mapQuadro($quadro);
        $mapped['membros'] = $this->membrosQuadro($empresaId, $quadroId);
        $mapped['listas'] = $this->mapListas($empresaId, $quadroId);
        $mapped['logs'] = $this->logsQuadro($empresaId, $quadroId);

        return $mapped;
    }

    public function listas(int $empresaId, int $quadroId): ?array
    {
        if (! $this->quadroDaEmpresa($empresaId, $quadroId)) {
            return null;
        }

        return [
            'quadro_public_id' => ApiExternaId::encode($quadroId),
            'listas' => $this->mapListas($empresaId, $quadroId),
        ];
    }

    /**
     * @param  array<string, mixed>  $filtros
     */
    public function tarefas(
        int $empresaId,
        int $quadroId,
        int $listaId,
        int $page,
        int $perPage,
        array $filtros = []
    ): ?LengthAwarePaginator {
        if (! $this->listaDoQuadro($empresaId, $quadroId, $listaId)) {
            return null;
        }

        $query = $this->queryTarefas($empresaId)
            ->where('t.lista_id', $listaId)
            ->orderBy('t.ordem')
            ->orderBy('t.id');

        if (! empty($filtros['q'])) {
            $query->where('t.titulo', 'like', '%' . $filtros['q'] . '%');
        }

        if (array_key_exists('concluido', $filtros)) {
            $query->where('t.concluido', $filtros['concluido'] ? 1 : 0);
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        $porId = [];
        foreach ($this->hidratarTarefas($empresaId, $quadroId, $paginator->items()) as $tarefa) {
            $porId[$tarefa['public_id']] = $tarefa;
        }

        return $paginator->through(
            fn ($row) => $porId[ApiExternaId::encode((int) $row->id)]
        );
    }

    public function detalheTarefa(int $empresaId, int $quadroId, int $tarefaId): ?array
    {
        $row = $this->queryTarefas($empresaId)
            ->join('lista_tarefas as l', 'l.id', '=', 't.lista_id')
            ->join('quadros as q', function ($join) use ($empresaId, $quadroId) {
                $join->on('q.id', '=', 'l.quadro_id')
                    ->where('q.id', $quadroId)
                    ->where('q.empresa_id', $empresaId)
                    ->whereNull('q.deleted_at');
            })
            ->where('t.id', $tarefaId)
            ->addSelect(['l.titulo as lista_titulo'])
            ->first();

        if (! $row) {
            return null;
        }

        $mapped = $this->hidratarTarefas($empresaId, $quadroId, [$row])[0];
        $mapped['lista'] = [
            'public_id' => ApiExternaId::encode((int) $row->lista_id),
            'titulo' => $row->lista_titulo,
        ];

        return $mapped;
    }

    private function quadroDaEmpresa(int $empresaId, int $quadroId): ?object
    {
        $row = DB::table('quadros as q')
            ->where('q.id', $quadroId)
            ->where('q.empresa_id', $empresaId)
            ->whereNull('q.deleted_at')
            ->select(['q.id', 'q.titulo', 'q.created_at', 'q.updated_at'])
            ->selectSub($this->contagemListas(), 'listas_count')
            ->selectSub($this->contagemTarefas(), 'tarefas_count')
            ->selectSub($this->contagemMembros(), 'membros_count')
            ->first();

        return $row ?: null;
    }

    private function listaDoQuadro(int $empresaId, int $quadroId, int $listaId): ?object
    {
        if (! $this->quadroDaEmpresa($empresaId, $quadroId)) {
            return null;
        }

        $row = DB::table('lista_tarefas')
            ->where('id', $listaId)
            ->where('quadro_id', $quadroId)
            ->first(['id']);

        return $row ?: null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function mapListas(int $empresaId, int $quadroId): array
    {
        $quadroPid = ApiExternaId::encode($quadroId);
        $listas = DB::table('lista_tarefas as l')
            ->leftJoin('users as autor', function ($join) use ($empresaId) {
                $join->on('autor.id', '=', 'l.user_id')
                    ->where('autor.empresa_id', $empresaId)
                    ->whereNull('autor.deleted_at');
            })
            ->where('l.quadro_id', $quadroId)
            ->orderBy('l.ordem')
            ->orderBy('l.id')
            ->get([
                'l.id',
                'l.titulo',
                'l.ordem',
                'l.created_at',
                'l.updated_at',
                'autor.nome as autor_nome',
            ]);

        $tarefas = $this->queryTarefas($empresaId)
            ->whereIn('t.lista_id', $listas->pluck('id')->all() ?: [0])
            ->orderBy('t.ordem')
            ->orderBy('t.id')
            ->get();
        $porLista = [];
        foreach ($this->hidratarTarefas($empresaId, $quadroId, $tarefas->all()) as $tarefa) {
            $porLista[$tarefa['lista_public_id']][] = $tarefa;
        }

        return $listas->map(function ($row) use ($quadroPid, $porLista) {
            $pid = ApiExternaId::encode((int) $row->id);
            $tarefasLista = $porLista[$pid] ?? [];

            return [
                'public_id' => $pid,
                'titulo' => $row->titulo,
                'ordem' => (int) $row->ordem,
                'autor' => $row->autor_nome,
                'criado_em' => $row->created_at,
                'atualizado_em' => $row->updated_at,
                'tarefas_count' => count($tarefasLista),
                'tarefas' => $tarefasLista,
                'links' => [
                    'tarefas' => url('/api/v3/weekly/' . $quadroPid . '/listas/' . $pid . '/tarefas'),
                    'quadro' => url('/api/v3/weekly/' . $quadroPid),
                ],
            ];
        })->all();
    }

    private function queryTarefas(int $empresaId)
    {
        return DB::table('tarefas as t')
            ->leftJoin('users as autor', function ($join) use ($empresaId) {
                $join->on('autor.id', '=', 't.user_id')
                    ->where('autor.empresa_id', $empresaId)
                    ->whereNull('autor.deleted_at');
            })
            ->whereNull('t.deleted_at')
            ->select([
                't.id',
                't.lista_id',
                't.titulo',
                't.descricao',
                't.ordem',
                't.concluido',
                't.datahora_inicio',
                't.datahora_entrega',
                't.lembrete',
                't.created_at',
                't.updated_at',
                'autor.nome as autor_nome',
            ]);
    }

    /**
     * @param  list<object>  $rows
     * @return list<array<string, mixed>>
     */
    private function hidratarTarefas(int $empresaId, int $quadroId, array $rows): array
    {
        $ids = array_map(fn ($row) => (int) $row->id, $rows);
        $contexto = $this->contextoTarefas($empresaId, $ids);

        return array_map(
            fn ($row) => $this->mapTarefa($row, $quadroId, $contexto),
            $rows
        );
    }

    /**
     * @param  list<int>  $tarefaIds
     * @return array{
     *     membros: array<int, list<array{nome: string}>>,
     *     checklists: array<int, list<array<string, mixed>>>,
     *     comentarios: array<int, list<array<string, mixed>>>,
     *     anexos: array<int, list<array<string, mixed>>>,
     *     logs: array<int, list<array<string, mixed>>>
     * }
     */
    private function contextoTarefas(int $empresaId, array $tarefaIds): array
    {
        if ($tarefaIds === []) {
            return $this->contextoVazio();
        }

        return [
            'membros' => $this->membrosPorTarefa($empresaId, $tarefaIds),
            'checklists' => $this->checklistsPorTarefa($empresaId, $tarefaIds),
            'comentarios' => $this->comentariosPorTarefa($empresaId, $tarefaIds),
            'anexos' => $this->anexosPorTarefa($tarefaIds),
            'logs' => $this->logsPorTarefa($empresaId, $tarefaIds),
        ];
    }

    /**
     * @return array{
     *     membros: array<int, list<array{nome: string}>>,
     *     checklists: array<int, list<array<string, mixed>>>,
     *     comentarios: array<int, list<array<string, mixed>>>,
     *     anexos: array<int, list<array<string, mixed>>>,
     *     logs: array<int, list<array<string, mixed>>>
     * }
     */
    private function contextoVazio(): array
    {
        return [
            'membros' => [],
            'checklists' => [],
            'comentarios' => [],
            'anexos' => [],
            'logs' => [],
        ];
    }

    /**
     * @return list<array{nome: string, papel: string}>
     */
    private function membrosQuadro(int $empresaId, int $quadroId): array
    {
        return DB::table('quadros_membros as qm')
            ->join('users as u', function ($join) use ($empresaId) {
                $join->on('u.id', '=', 'qm.user_id')
                    ->where('u.empresa_id', $empresaId)
                    ->whereNull('u.deleted_at');
            })
            ->where('qm.quadro_id', $quadroId)
            ->orderBy('u.nome')
            ->get(['u.nome', 'qm.papel'])
            ->map(fn ($row) => [
                'nome' => $row->nome,
                'papel' => $row->papel,
            ])
            ->all();
    }

    /**
     * @param  list<int>  $tarefaIds
     * @return array<int, list<array{nome: string}>>
     */
    private function membrosPorTarefa(int $empresaId, array $tarefaIds): array
    {
        if ($tarefaIds === [] || ! Schema::hasTable('membros_tarefa')) {
            return [];
        }

        $grouped = [];
        $rows = DB::table('membros_tarefa as mt')
            ->join('users as u', function ($join) use ($empresaId) {
                $join->on('u.id', '=', 'mt.user_id')
                    ->where('u.empresa_id', $empresaId)
                    ->whereNull('u.deleted_at');
            })
            ->whereIn('mt.tarefa_id', $tarefaIds)
            ->orderBy('u.nome')
            ->get(['mt.tarefa_id', 'u.nome']);

        foreach ($rows as $row) {
            $grouped[(int) $row->tarefa_id][] = ['nome' => $row->nome];
        }

        return $grouped;
    }

    /**
     * @param  list<int>  $tarefaIds
     * @return array<int, list<array<string, mixed>>>
     */
    private function checklistsPorTarefa(int $empresaId, array $tarefaIds): array
    {
        if ($tarefaIds === [] || ! Schema::hasTable('checklists_tarefas')) {
            return [];
        }

        $colunasChecklist = ['id', 'tarefa_id', 'titulo', 'ordem'];
        if (Schema::hasColumn('checklists_tarefas', 'datahora_entrega')) {
            $colunasChecklist[] = 'datahora_entrega';
        }

        $checklists = DB::table('checklists_tarefas')
            ->whereIn('tarefa_id', $tarefaIds)
            ->orderBy('ordem')
            ->orderBy('id')
            ->get($colunasChecklist);

        $checklistIds = $checklists->pluck('id')->map(fn ($id) => (int) $id)->all();
        $itensPorChecklist = [];
        if ($checklistIds !== [] && Schema::hasTable('checklists_tarefa_items')) {
            $colunas = ['id', 'checklist_id', 'titulo', 'concluido', 'ordem'];
            if (Schema::hasColumn('checklists_tarefa_items', 'datahora_entrega')) {
                $colunas[] = 'datahora_entrega';
            }
            $itens = DB::table('checklists_tarefa_items')
                ->whereIn('checklist_id', $checklistIds)
                ->orderBy('ordem')
                ->orderBy('id')
                ->get($colunas);
            $membrosItem = $this->membrosPorItemChecklist(
                $empresaId,
                $itens->pluck('id')->map(fn ($id) => (int) $id)->all()
            );
            foreach ($itens as $item) {
                $itensPorChecklist[(int) $item->checklist_id][] = [
                    'public_id' => ApiExternaId::encode((int) $item->id),
                    'titulo' => $item->titulo,
                    'concluido' => (bool) $item->concluido,
                    'ordem' => (int) $item->ordem,
                    'datahora_entrega' => $item->datahora_entrega ?? null,
                    'membros' => $membrosItem[(int) $item->id] ?? [],
                ];
            }
        }

        $grouped = [];
        foreach ($checklists as $row) {
            $grouped[(int) $row->tarefa_id][] = [
                'public_id' => ApiExternaId::encode((int) $row->id),
                'titulo' => $row->titulo,
                'ordem' => (int) $row->ordem,
                'datahora_entrega' => $row->datahora_entrega ?? null,
                'itens' => $itensPorChecklist[(int) $row->id] ?? [],
            ];
        }

        return $grouped;
    }

    /**
     * @param  list<int>  $itemIds
     * @return array<int, list<array{nome: string}>>
     */
    private function membrosPorItemChecklist(int $empresaId, array $itemIds): array
    {
        if ($itemIds === [] || ! Schema::hasTable('checklists_tarefa_items_membros')) {
            return [];
        }

        $grouped = [];
        $rows = DB::table('checklists_tarefa_items_membros as m')
            ->join('users as u', function ($join) use ($empresaId) {
                $join->on('u.id', '=', 'm.user_id')
                    ->where('u.empresa_id', $empresaId)
                    ->whereNull('u.deleted_at');
            })
            ->whereIn('m.checklists_tarefa_item_id', $itemIds)
            ->orderBy('u.nome')
            ->get(['m.checklists_tarefa_item_id', 'u.nome']);

        foreach ($rows as $row) {
            $grouped[(int) $row->checklists_tarefa_item_id][] = ['nome' => $row->nome];
        }

        return $grouped;
    }

    /**
     * @param  list<int>  $tarefaIds
     * @return array<int, list<array<string, mixed>>>
     */
    private function comentariosPorTarefa(int $empresaId, array $tarefaIds): array
    {
        if ($tarefaIds === [] || ! Schema::hasTable('tarefas_comentarios')) {
            return [];
        }

        $grouped = [];
        $rows = DB::table('tarefas_comentarios as c')
            ->leftJoin('users as u', function ($join) use ($empresaId) {
                $join->on('u.id', '=', 'c.user_id')
                    ->where('u.empresa_id', $empresaId)
                    ->whereNull('u.deleted_at');
            })
            ->whereIn('c.tarefa_id', $tarefaIds)
            ->whereNull('c.deleted_at')
            ->orderByDesc('c.created_at')
            ->orderByDesc('c.id')
            ->get(['c.id', 'c.tarefa_id', 'c.comentario', 'c.tipo', 'c.created_at', 'c.updated_at', 'u.nome']);

        foreach ($rows as $row) {
            $grouped[(int) $row->tarefa_id][] = [
                'public_id' => ApiExternaId::encode((int) $row->id),
                'tipo' => $row->tipo,
                'tipo_label' => $this->tipoComentario($row->tipo),
                'texto' => $this->textoPlano($row->comentario),
                'autor' => $row->nome,
                'criado_em' => $row->created_at,
                'atualizado_em' => $row->updated_at,
            ];
        }

        return $grouped;
    }

    /**
     * @param  list<int>  $tarefaIds
     * @return array<int, list<array<string, mixed>>>
     */
    private function anexosPorTarefa(array $tarefaIds): array
    {
        if ($tarefaIds === [] || ! Schema::hasTable('tarefa_anexos') || ! Schema::hasTable('arquivos')) {
            return [];
        }

        $grouped = [];
        $rows = DB::table('tarefa_anexos as ta')
            ->join('arquivos as a', 'a.id', '=', 'ta.arquivo_id')
            ->whereIn('ta.tarefa_id', $tarefaIds)
            ->orderBy('a.nome')
            ->get(['ta.tarefa_id', 'a.id', 'a.nome', 'a.extensao', 'a.bytes']);

        foreach ($rows as $row) {
            $grouped[(int) $row->tarefa_id][] = [
                'public_id' => ApiExternaId::encode((int) $row->id),
                'nome' => $row->nome,
                'extensao' => $row->extensao,
                'bytes' => $row->bytes !== null ? (int) $row->bytes : null,
            ];
        }

        return $grouped;
    }

    /**
     * @param  list<int>  $tarefaIds
     * @return array<int, list<array<string, mixed>>>
     */
    private function logsPorTarefa(int $empresaId, array $tarefaIds): array
    {
        if ($tarefaIds === [] || ! Schema::hasTable('log_weekly')) {
            return [];
        }

        $grouped = [];
        foreach ($this->queryLogs($empresaId)->whereIn('l.tarefa_id', $tarefaIds)->get() as $row) {
            $grouped[(int) $row->tarefa_id][] = $this->mapLog($row);
        }

        return $grouped;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function logsQuadro(int $empresaId, int $quadroId): array
    {
        if (! Schema::hasTable('log_weekly')) {
            return [];
        }

        return $this->queryLogs($empresaId)
            ->where('l.quadro_id', $quadroId)
            ->whereNull('l.tarefa_id')
            ->get()
            ->map(fn ($row) => $this->mapLog($row))
            ->all();
    }

    private function queryLogs(int $empresaId)
    {
        return DB::table('log_weekly as l')
            ->leftJoin('users as u', function ($join) use ($empresaId) {
                $join->on('u.id', '=', 'l.user_id')
                    ->where('u.empresa_id', $empresaId)
                    ->whereNull('u.deleted_at');
            })
            ->orderByDesc('l.created_at')
            ->orderByDesc('l.id')
            ->select(['l.id', 'l.tarefa_id', 'l.descricao', 'l.created_at', 'u.nome']);
    }

    private function mapLog(object $row): array
    {
        return [
            'public_id' => ApiExternaId::encode((int) $row->id),
            'descricao' => $this->textoPlano($row->descricao),
            'autor' => $row->nome,
            'tarefa_public_id' => $row->tarefa_id ? ApiExternaId::encode((int) $row->tarefa_id) : null,
            'criado_em' => $row->created_at,
        ];
    }

    private function mapQuadro(object $row): array
    {
        $pid = ApiExternaId::encode((int) $row->id);

        return [
            'public_id' => $pid,
            'titulo' => $row->titulo,
            'criado_em' => $row->created_at,
            'atualizado_em' => $row->updated_at,
            'listas_count' => (int) ($row->listas_count ?? 0),
            'tarefas_count' => (int) ($row->tarefas_count ?? 0),
            'membros_count' => (int) ($row->membros_count ?? 0),
            'links' => [
                'self' => url('/api/v3/weekly/' . $pid),
                'listas' => url('/api/v3/weekly/' . $pid . '/listas'),
            ],
        ];
    }

    /**
     * @param  array{
     *     membros: array<int, list<array{nome: string}>>,
     *     checklists: array<int, list<array<string, mixed>>>,
     *     comentarios: array<int, list<array<string, mixed>>>,
     *     anexos: array<int, list<array<string, mixed>>>,
     *     logs: array<int, list<array<string, mixed>>>
     * }  $contexto
     */
    private function mapTarefa(object $row, int $quadroId, array $contexto): array
    {
        $id = (int) $row->id;
        $pid = ApiExternaId::encode($id);
        $quadroPid = ApiExternaId::encode($quadroId);
        $entrega = $row->datahora_entrega;
        $comentarios = $contexto['comentarios'][$id] ?? [];
        $checklists = $contexto['checklists'][$id] ?? [];
        $anexos = $contexto['anexos'][$id] ?? [];
        $bloqueios = 0;
        foreach ($comentarios as $comentario) {
            if (($comentario['tipo'] ?? null) === 'bloqueio') {
                $bloqueios++;
            }
        }

        return [
            'public_id' => $pid,
            'lista_public_id' => ApiExternaId::encode((int) $row->lista_id),
            'titulo' => $row->titulo,
            'descricao' => $this->textoPlano($row->descricao ?? null),
            'ordem' => (int) $row->ordem,
            'concluido' => (bool) $row->concluido,
            'em_atraso' => $this->emAtraso((bool) $row->concluido, $entrega),
            'datahora_inicio' => $row->datahora_inicio,
            'datahora_entrega' => $entrega,
            'lembrete' => $row->lembrete ?? null,
            'lembrete_texto' => $this->lembreteTexto($row->lembrete ?? null, $entrega),
            'autor' => $row->autor_nome ?? null,
            'criado_em' => $row->created_at ?? null,
            'atualizado_em' => $row->updated_at ?? null,
            'membros' => $contexto['membros'][$id] ?? [],
            'checklists_count' => count($checklists),
            'checklists' => $checklists,
            'comentarios_count' => count($comentarios),
            'bloqueios_count' => $bloqueios,
            'comentarios' => $comentarios,
            'anexos_count' => count($anexos),
            'anexos' => $anexos,
            'logs' => $contexto['logs'][$id] ?? [],
            'links' => [
                'self' => url('/api/v3/weekly/' . $quadroPid . '/tarefas/' . $pid),
            ],
        ];
    }

    private function tipoComentario(mixed $tipo): string
    {
        return match ($tipo) {
            'bloqueio' => 'Bloqueio',
            'dependencia' => 'Dependência externa',
            default => 'Comentário',
        };
    }

    private function lembreteTexto(mixed $lembrete, mixed $entrega): ?string
    {
        if ($lembrete === null || $lembrete === '' || $entrega === null || $entrega === '') {
            return null;
        }

        try {
            $segundos = Carbon::parse((string) $entrega)->getTimestamp() - Carbon::parse((string) $lembrete)->getTimestamp();
        } catch (\Throwable) {
            return null;
        }

        return match ($segundos) {
            5 * 60 => '5m',
            10 * 60 => '10m',
            15 * 60 => '15m',
            60 * 60 => '1H',
            2 * 60 * 60 => '2H',
            24 * 60 * 60 => '1d',
            2 * 24 * 60 * 60 => '2d',
            default => null,
        };
    }

    private function emAtraso(bool $concluido, mixed $entrega): bool
    {
        if ($concluido || $entrega === null || $entrega === '') {
            return false;
        }

        try {
            return Carbon::parse((string) $entrega)->isPast();
        } catch (\Throwable) {
            return false;
        }
    }

    private function textoPlano(mixed $html): ?string
    {
        if (! is_string($html)) {
            return null;
        }

        $html = preg_replace_callback(
            '/<span\b[^>]*\bwr-mention\b[^>]*>.*?<\/span>/is',
            static function (array $matches): string {
                $raw = $matches[0];
                if (! preg_match('/data-nome\s*=\s*("|\')([^"\']*)\1/i', $raw, $nomeMatch)) {
                    return '';
                }
                $nome = trim(html_entity_decode($nomeMatch[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));

                return $nome !== '' ? '@' . $nome : '';
            },
            $html
        ) ?? $html;

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);

        return $text === '' ? null : $text;
    }

    private function contagemListas()
    {
        return DB::table('lista_tarefas as l')
            ->selectRaw('count(*)')
            ->whereColumn('l.quadro_id', 'q.id');
    }

    private function contagemTarefas()
    {
        return DB::table('tarefas as t')
            ->join('lista_tarefas as l', 'l.id', '=', 't.lista_id')
            ->selectRaw('count(*)')
            ->whereColumn('l.quadro_id', 'q.id')
            ->whereNull('t.deleted_at');
    }

    private function contagemMembros()
    {
        return DB::table('quadros_membros as qm')
            ->selectRaw('count(*)')
            ->whereColumn('qm.quadro_id', 'q.id');
    }
}
