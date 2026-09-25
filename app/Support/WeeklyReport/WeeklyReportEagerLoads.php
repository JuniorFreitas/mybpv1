<?php

namespace App\Support\WeeklyReport;

use App\Models\Tarefa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

/**
 * Eager loads explícitos por contexto — evita $with global e over-fetch.
 */
final class WeeklyReportEagerLoads
{
    public const PAGE_SIZE = 20;

    /** Colunas do card no kanban (sem descrição/lembrete/anexos). */
    public const TAREFA_BOARD_COLUMNS = [
        'id',
        'lista_id',
        'user_id',
        'titulo',
        'ordem',
        'datahora_entrega',
        'concluido',
        'created_at',
        'updated_at',
    ];

    public static function applyBoardTarefas(Builder|Relation $query): Builder|Relation
    {
        return $query
            ->select(self::TAREFA_BOARD_COLUMNS)
            ->orderBy('ordem')
            ->withCount([
                'Anexos as anexos_count',
                'Comentarios as comentarios_count',
                'ComentariosBloqueio as bloqueios_count',
            ])
            ->with([
                'Membros' => function ($q) {
                    $q->select(['users.id', 'users.nome']);
                },
                'Checklists' => function ($q) {
                    $q->select(['id', 'tarefa_id', 'ordem'])->orderBy('ordem');
                },
                'Checklists.Itens' => function ($q) {
                    $q->select(['id', 'checklist_id', 'concluido', 'ordem'])->orderBy('ordem');
                },
            ]);
    }

    /** Remove appends/atributos que o board não usa. */
    public static function finalizeBoardListas(Collection|EloquentCollection $listas): Collection|EloquentCollection
    {
        foreach ($listas as $lista) {
            foreach ($lista->Tarefas ?? [] as $tarefa) {
                $tarefa->setAppends(['emAtraso']);
                $tarefa->makeHidden(['lembrete', 'descricao', 'lembreteText']);
            }
        }

        return $listas;
    }

    public static function loadShow(Tarefa $tarefa): Tarefa
    {
        $tarefa->load([
            'Membros' => function ($q) {
                $q->select(['users.id', 'users.nome']);
            },
            'Anexos',
            'Checklists' => function ($q) {
                $q->orderBy('ordem')->with([
                    'Itens' => function ($iq) {
                        $iq->orderBy('ordem')->with([
                            'Membros' => function ($mq) {
                                $mq->select(['users.id', 'users.nome']);
                            },
                        ]);
                    },
                ]);
            },
        ]);

        return $tarefa;
    }

    public static function applyLembrete(Builder $query): Builder
    {
        return $query
            ->select([
                'id',
                'lista_id',
                'titulo',
                'datahora_entrega',
                'lembrete',
                'concluido',
            ])
            ->with([
                'Membros' => function ($q) {
                    $q->select(['users.id', 'users.nome', 'users.login']);
                },
                'Lista' => function ($q) {
                    $q->select(['id', 'titulo']);
                },
            ]);
    }

    /** Payload mínimo após PATCH (não reidrata anexos/checklist). */
    public static function patchPayload(Tarefa $tarefa, array $extra = []): array
    {
        $base = [
            'id' => $tarefa->id,
            'lista_id' => $tarefa->lista_id,
            'titulo' => $tarefa->titulo,
            'descricao' => $tarefa->descricao,
            'concluido' => (bool) $tarefa->concluido,
            'datahora_inicio' => $tarefa->datahora_inicio,
            'datahora_entrega' => $tarefa->datahora_entrega,
            'emAtraso' => (bool) $tarefa->emAtraso,
            'lembreteText' => $tarefa->lembreteText,
        ];

        if ($tarefa->relationLoaded('Membros')) {
            $base['membros'] = $tarefa->Membros->map(fn ($u) => [
                'id' => $u->id,
                'nome' => $u->nome,
            ])->values()->all();
        }

        return array_merge($base, $extra);
    }

    public static function pageMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
