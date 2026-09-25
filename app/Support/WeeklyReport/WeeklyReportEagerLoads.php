<?php

namespace App\Support\WeeklyReport;

use App\Models\Tarefa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Eager loads explícitos por contexto — evita $with global e over-fetch.
 */
final class WeeklyReportEagerLoads
{
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
                // Progresso do card: só concluído dos itens (sem título/membros)
                'Checklists' => function ($q) {
                    $q->select(['id', 'tarefa_id', 'ordem'])->orderBy('ordem');
                },
                'Checklists.Itens' => function ($q) {
                    $q->select(['id', 'checklist_id', 'concluido', 'ordem'])->orderBy('ordem');
                },
            ]);
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
}
