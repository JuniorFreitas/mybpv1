<?php

namespace App\Http\Controllers;

use App\Events\WeeklyReport\ComentarioTarefaEvent;
use App\Http\Controllers\Concerns\GuardsWeeklyReportTenant;
use App\Models\ListaTarefa;
use App\Models\LogWeekly;
use App\Models\Quadro;
use App\Models\Tarefa;
use App\Models\TarefaComentario;
use App\Services\WeeklyReport\AttachMentionedMembers;
use App\Support\WeeklyReport\WeeklyReportEagerLoads;
use App\Support\WeeklyReportHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class TarefasComentarioController extends Controller
{
    use GuardsWeeklyReportTenant;

    public function index(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);

        $page = max(1, (int) $request->input('page', 1));

        return $tarefa->Comentarios()
            ->with('Usuario:id,nome')
            ->paginate(WeeklyReportEagerLoads::PAGE_SIZE, ['*'], 'page', $page);
    }

    public function store(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);

        $comentarioHtml = WeeklyReportHtml::sanitize($request->input('comentario'));
        if (WeeklyReportHtml::isEmpty($comentarioHtml)) {
            return response()->json([
                'msg' => 'Informe o texto do comentário',
                'erros' => ['comentario' => ['O comentário é obrigatório.']],
            ], 400);
        }

        $dadosValidados = \Validator::make([
            'comentario' => $comentarioHtml,
            'tipo' => $request->input('tipo'),
        ], [
            'comentario' => 'required|string|min:1|max:20000',
            'tipo' => 'nullable|in:' . implode(',', TarefaComentario::TIPOS),
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao criar o comentário',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        try {
            \DB::beginTransaction();
            $comentario = $tarefa->Comentarios()->create([
                'comentario' => $comentarioHtml,
                'tipo' => $request->input('tipo', TarefaComentario::TIPO_COMENTARIO),
            ]);
            \DB::commit();

            $comentario->load('Usuario:id,nome');

            $this->logComentario($quadro, $tarefa, $comentario, 'add');

            // Broadcast + menções depois da resposta HTTP (não travar o Publicar)
            $comentarioId = $comentario->id;
            $htmlMencoes = $comentario->comentario;
            $tarefaId = $tarefa->id;
            $quadroId = $quadro->id;
            $listaId = $lista->id;
            $empresaId = $empresa;

            dispatch(function () use ($comentarioId, $htmlMencoes, $tarefaId, $quadroId, $listaId, $empresaId) {
                try {
                    $comentarioModel = TarefaComentario::query()->with('Usuario:id,nome')->find($comentarioId);
                    if ($comentarioModel) {
                        Event::dispatch(new ComentarioTarefaEvent(
                            $comentarioModel,
                            ComentarioTarefaEvent::INSERT,
                            null,
                            $tarefaId,
                            $listaId
                        ));
                    }
                } catch (\Throwable $e) {
                    \Log::warning('WeeklyReport: falha ao broadcast do comentário', [
                        'comentario_id' => $comentarioId,
                        'erro' => $e->getMessage(),
                    ]);
                }

                try {
                    $tarefaModel = Tarefa::query()->find($tarefaId);
                    $quadroModel = Quadro::query()->find($quadroId);
                    if ($tarefaModel && $quadroModel) {
                        app(AttachMentionedMembers::class)->attachFromHtml(
                            $tarefaModel,
                            $quadroModel,
                            $empresaId,
                            $htmlMencoes
                        );
                    }
                } catch (\Throwable $e) {
                    \Log::warning('WeeklyReport: falha ao vincular mencionados no comentário', [
                        'tarefa_id' => $tarefaId,
                        'erro' => $e->getMessage(),
                    ]);
                }
            })->afterResponse();

            return response()->json([
                'comentario' => $comentario,
            ], 201);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function update(
        Request $request,
        int $empresa,
        Quadro $quadro,
        ListaTarefa $lista,
        Tarefa $tarefa,
        TarefaComentario $comentario
    ) {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);
        $this->assertComentarioDaTarefa($tarefa, $comentario);

        if ((int) $comentario->user_id !== (int) auth()->id()) {
            return response()->json(['msg' => 'Só o autor pode editar este comentário.'], 403);
        }

        $comentarioHtml = WeeklyReportHtml::sanitize($request->input('comentario'));
        if (WeeklyReportHtml::isEmpty($comentarioHtml)) {
            return response()->json([
                'msg' => 'Informe o texto do comentário',
                'erros' => ['comentario' => ['O comentário é obrigatório.']],
            ], 400);
        }

        $dadosValidados = \Validator::make([
            'comentario' => $comentarioHtml,
            'tipo' => $request->input('tipo'),
        ], [
            'comentario' => 'required|string|min:1|max:20000',
            'tipo' => 'nullable|in:' . implode(',', TarefaComentario::TIPOS),
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao atualizar o comentário',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        try {
            \DB::beginTransaction();
            $dados = [
                'comentario' => $comentarioHtml,
            ];
            if ($request->filled('tipo')) {
                $dados['tipo'] = $request->input('tipo');
            }
            $comentario->update($dados);
            \DB::commit();

            $comentario->load('Usuario:id,nome');

            try {
                app(AttachMentionedMembers::class)->attachFromHtml(
                    $tarefa,
                    $quadro,
                    $empresa,
                    $comentario->comentario
                );
            } catch (\Throwable $e) {
                \Log::warning('WeeklyReport: falha ao vincular mencionados no comentário', [
                    'tarefa_id' => $tarefa->id,
                    'erro' => $e->getMessage(),
                ]);
            }

            Event::dispatch(new ComentarioTarefaEvent(
                $comentario,
                ComentarioTarefaEvent::UPDATE,
                null,
                $tarefa->id,
                $lista->id
            ));

            return response()->json([
                'comentario' => $comentario,
                'tarefa' => WeeklyReportEagerLoads::patchPayload(
                    $tarefa->fresh()->load([
                        'Membros' => function ($q) {
                            $q->select(['users.id', 'users.nome']);
                        },
                    ])
                ),
            ], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function destroy(
        Request $request,
        int $empresa,
        Quadro $quadro,
        ListaTarefa $lista,
        Tarefa $tarefa,
        TarefaComentario $comentario
    ) {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);
        $this->assertComentarioDaTarefa($tarefa, $comentario);

        $isAuthor = (int) $comentario->user_id === (int) auth()->id();
        $canUpdate = auth()->user()->can('weekly_report_quadro_tarefa_update');
        if (!$isAuthor && !$canUpdate) {
            return response()->json(['msg' => 'Sem permissão para excluir este comentário.'], 403);
        }

        try {
            \DB::beginTransaction();
            $idDelete = $comentario->id;
            $tipo = $comentario->tipo;
            $comentario->delete();
            \DB::commit();

            if (in_array($tipo, [TarefaComentario::TIPO_BLOQUEIO, TarefaComentario::TIPO_DEPENDENCIA], true)) {
                LogWeekly::create([
                    'quadro_id' => $quadro->id,
                    'tarefa_id' => $tarefa->id,
                    'descricao' => $tipo === TarefaComentario::TIPO_BLOQUEIO
                        ? 'removeu um bloqueio desta tarefa'
                        : 'removeu uma dependência externa desta tarefa',
                ]);
            }

            Event::dispatch(new ComentarioTarefaEvent(
                (object) [
                    'id' => $idDelete,
                    'tarefa_id' => $tarefa->id,
                ],
                ComentarioTarefaEvent::DELETE,
                $idDelete,
                $tarefa->id,
                $lista->id
            ));

            return response()->json(['comentario_id' => $idDelete], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    private function assertComentarioDaTarefa(Tarefa $tarefa, TarefaComentario $comentario): void
    {
        if ((int) $comentario->tarefa_id !== (int) $tarefa->id) {
            abort(404, 'Comentário não encontrado nesta tarefa.');
        }
    }

    private function logComentario(Quadro $quadro, Tarefa $tarefa, TarefaComentario $comentario, string $acao): void
    {
        if ($acao !== 'add') {
            return;
        }

        $descricao = match ($comentario->tipo) {
            TarefaComentario::TIPO_BLOQUEIO => 'registrou um bloqueio nesta tarefa',
            TarefaComentario::TIPO_DEPENDENCIA => 'registrou uma dependência externa nesta tarefa',
            default => 'comentou nesta tarefa',
        };

        LogWeekly::create([
            'quadro_id' => $quadro->id,
            'tarefa_id' => $tarefa->id,
            'descricao' => $descricao,
        ]);
    }
}
