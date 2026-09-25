<?php

namespace App\Http\Controllers;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Events\WeeklyReport\AnexoEvent;
use App\Events\WeeklyReport\TarefaEvent;
use App\Http\Controllers\Concerns\GuardsWeeklyReportTenant;
use App\Jobs\Weekly_report\UpdateMembrosJob;
use App\Models\Arquivo;
use App\Models\ChecklistsTarefa;
use App\Models\ListaTarefa;
use App\Models\LogWeekly;
use App\Models\Quadro;
use App\Models\Sistema;
use App\Models\Tarefa;
use App\Models\User;
use App\Services\WeeklyReport\AttachMentionedMembers;
use App\Support\WeeklyReport\WeeklyReportEagerLoads;
use App\Support\WeeklyReportHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use MasterTag\DataHora;

class TarefasController extends Controller
{
    use GuardsWeeklyReportTenant;

    public function store(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista);

        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'required|min:1',
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao criar a tarefa',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        try {
            \DB::beginTransaction();
            $dados = [
                'titulo' => $request->input('titulo'),
                'lista_id' => $lista->id,
                'ordem' => 1,
                'user_id' => auth()->id(),
            ];
            $ultimo = $lista->Tarefas()->orderByDesc('ordem')->first();
            if ($ultimo) {
                $dados['ordem'] = $ultimo->ordem + 1;
            }
            $tarefa = Tarefa::create($dados);
            \DB::commit();

            Event::dispatch(new TarefaEvent($tarefa, TarefaEvent::INSERT));
            LogWeekly::create([
                'quadro_id' => $quadro->id,
                'tarefa_id' => $tarefa->id,
                'descricao' => "adicionou {$tarefa->titulo} a lista {$lista->titulo}",
            ]);

            return response()->json(['tarefa' => $tarefa], 201);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function show(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);

        WeeklyReportEagerLoads::loadShow($tarefa);
        $logsPage = $tarefa->Logs()->paginate(20);
        $tarefa->setRelation('Logs', collect($logsPage->items()));
        $tarefa->setAttribute('logs_meta', [
            'current_page' => $logsPage->currentPage(),
            'last_page' => $logsPage->lastPage(),
            'per_page' => $logsPage->perPage(),
            'total' => $logsPage->total(),
        ]);
        $tarefa->setRelation(
            'Comentarios',
            $tarefa->Comentarios()->with('Usuario:id,nome')->limit(100)->get()
        );
        $tarefa->loadCount([
            'ComentariosBloqueio as bloqueios_count',
            'Anexos as anexos_count',
        ]);

        return $tarefa;
    }

    public function logs(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);

        $page = max(1, (int) $request->input('page', 1));

        return $tarefa->Logs()->paginate(20, ['*'], 'page', $page);
    }

    public function update(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);

        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'min:1',
            'concluido' => 'boolean',
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao atualizar a tarefa',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        try {
            \DB::beginTransaction();
            $antes = [
                'titulo' => $tarefa->titulo,
                'concluido' => (bool) $tarefa->concluido,
            ];
            $dados = $request->only(['titulo', 'descricao', 'concluido', 'lembrete']);
            if ($request->filled('descricao')) {
                $dados['descricao'] = WeeklyReportHtml::sanitize($dados['descricao']);
            }
            if ($request->exists('lembrete')) {
                $tarefa->lembrete = $dados['lembrete'];
                unset($dados['lembrete']);
            }
            $tarefa->update($dados);
            \DB::commit();

            $tarefa = $tarefa->fresh();
            if ($request->has('concluido') && $antes['concluido'] !== (bool) $tarefa->concluido) {
                LogWeekly::create([
                    'quadro_id' => $quadro->id,
                    'tarefa_id' => $tarefa->id,
                    'descricao' => $tarefa->concluido
                        ? "marcou {$tarefa->titulo} como concluída"
                        : "desmarcou {$tarefa->titulo} como concluída",
                ]);
            } elseif ($request->filled('titulo') && $antes['titulo'] !== $tarefa->titulo) {
                LogWeekly::create([
                    'quadro_id' => $quadro->id,
                    'tarefa_id' => $tarefa->id,
                    'descricao' => "alterou o título para {$tarefa->titulo}",
                ]);
            } elseif ($request->has('descricao')) {
                LogWeekly::create([
                    'quadro_id' => $quadro->id,
                    'tarefa_id' => $tarefa->id,
                    'descricao' => 'atualizou a descrição desta tarefa',
                ]);
                $htmlMencoes = $tarefa->descricao;
                $tarefaId = $tarefa->id;
                $quadroId = $quadro->id;
                $empresaId = $empresa;
                dispatch(function () use ($tarefaId, $quadroId, $empresaId, $htmlMencoes) {
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
                        \Log::warning('WeeklyReport: falha ao vincular mencionados na descrição', [
                            'tarefa_id' => $tarefaId,
                            'erro' => $e->getMessage(),
                        ]);
                    }
                })->afterResponse();
            }

            Event::dispatch(new TarefaEvent($tarefa, TarefaEvent::UPDATE));

            return response()->json(['tarefa' => $tarefa->fresh(['Membros'])], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function destroy(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);

        try {
            \DB::beginTransaction();

            $titulo = $tarefa->titulo;
            $idDelete = $tarefa->id;
            $tarefa->delete();

            \DB::commit();

            Event::dispatch(new TarefaEvent($lista, TarefaEvent::DELETE, $idDelete));
            LogWeekly::create([
                'quadro_id' => $quadro->id,
                'tarefa_id' => $idDelete,
                'descricao' => "apagou a tarefa {$titulo} da lista {$lista->titulo}",
            ]);

            return response()->json([], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json([
                'msg' => $e->getMessage(),
                'lista' => $lista->Tarefas()->orderBy('ordem')->get(),
            ], 400);
        }
    }

    public function atualizarOrdem(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista);

        if ($request->evento == 'moveu') {
            foreach ($request->novaLista ?? [] as $obj) {
                $id = $obj['id'] ?? null;
                if (!$id) {
                    continue;
                }
                $payload = array_intersect_key($obj, array_flip(['ordem', 'lista_id', 'titulo']));
                Tarefa::whereListaId($lista->id)->whereId($id)->update($payload);
            }
            Event::dispatch(new TarefaEvent($lista, TarefaEvent::ORDENAR));
        }

        if ($request->evento == 'adicionar') {
            $tarefa = Tarefa::without(['Membros', 'Anexos', 'Checklists'])->find($request->tarefa_id);
            foreach ($request->novaLista ?? [] as $obj) {
                $id = $obj['id'] ?? null;
                if (!$id) {
                    continue;
                }
                $payload = array_intersect_key($obj, array_flip(['ordem', 'lista_id', 'titulo']));
                if (isset($payload['lista_id']) && (int) $payload['lista_id'] !== (int) $lista->id) {
                    $payload['lista_id'] = $lista->id;
                }
                if (!isset($payload['lista_id'])) {
                    $payload['lista_id'] = $lista->id;
                }
                Tarefa::whereId($id)->update($payload);
            }
            Event::dispatch(new TarefaEvent($lista, TarefaEvent::ORDENAR));
            if ($tarefa) {
                LogWeekly::create([
                    'quadro_id' => $quadro->id,
                    'tarefa_id' => $tarefa->id,
                    'descricao' => "moveu {$tarefa->titulo} para a lista {$lista->titulo}",
                ]);
            }
        }

        if ($request->evento == 'remover') {
            foreach ($request->novaLista ?? [] as $obj) {
                $id = $obj['id'] ?? null;
                if (!$id) {
                    continue;
                }
                $payload = array_intersect_key($obj, array_flip(['ordem', 'lista_id', 'titulo']));
                Tarefa::whereId($id)->update($payload);
            }
            Event::dispatch(new TarefaEvent($lista, TarefaEvent::ORDENAR));
        }

        return response()->json([], 200);
    }

    public function updateMembro(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);

        $membro = User::query()
            ->whereId($request->user_id)
            ->whereEmpresaId($empresa)
            ->whereAtivo(true)
            ->first();

        if (!$membro) {
            return response()->json(['msg' => 'Membro inválido para este tenant.'], 400);
        }

        if ($request->acao == 'add') {
            $tarefa->Membros()->syncWithoutDetaching([$membro->id]);
            $evento = new TarefaEvent($tarefa, TarefaEvent::UPDATE_MEMBROS);
            $evento->acao = TarefaEvent::ACAO_ADD;
            Event::dispatch($evento);
            Event::dispatch(new NotificacaoEvent([
                'tarefa' => $tarefa,
                'user_id' => $membro->id,
            ], NotificacaoEvent::MEMBRO_TAREFA_ADD, NotificacaoEvent::TIPO_PADRAO));

            LogWeekly::create([
                'quadro_id' => $quadro->id,
                'tarefa_id' => $tarefa->id,
                'descricao' => "adicionou {$membro->nome} a esta tarefa",
            ]);

            if (Sistema::validaEmail(auth()->user()->login) && Sistema::validaEmail($membro->login)) {
                UpdateMembrosJob::dispatch([
                    'de' => auth()->user(),
                    'para' => $membro,
                    'acao' => TarefaEvent::ACAO_ADD,
                    'modelTarefa' => $tarefa,
                    'empresa_id' => $empresa,
                ]);
            }
        }

        if ($request->acao == 'remove') {
            $evento = new TarefaEvent($tarefa, TarefaEvent::UPDATE_MEMBROS);
            $evento->acao = TarefaEvent::ACAO_DELETE;
            $tarefa->Membros()->detach($membro->id);
            Event::dispatch($evento);
            Event::dispatch(new NotificacaoEvent([
                'tarefa' => $tarefa,
                'user_id' => $membro->id,
            ], NotificacaoEvent::MEMBRO_TAREFA_REMOVE, NotificacaoEvent::TIPO_PADRAO));

            LogWeekly::create([
                'quadro_id' => $quadro->id,
                'tarefa_id' => $tarefa->id,
                'descricao' => "removeu {$membro->nome} desta tarefa",
            ]);

            if (Sistema::validaEmail(auth()->user()->login) && Sistema::validaEmail($membro->login)) {
                UpdateMembrosJob::dispatch([
                    'de' => auth()->user(),
                    'para' => $membro,
                    'acao' => TarefaEvent::ACAO_DELETE,
                    'modelTarefa' => $tarefa,
                    'empresa_id' => $empresa,
                ]);
            }
        }

        return response()->json(['tarefa' => $tarefa->fresh(['Membros'])], 200);
    }

    public function updateDataHoraInicio(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);

        try {
            if ($request->acao == 'add') {
                $convertida = DataHora::converterDatePicker($request->datahora);
                if (!$convertida) {
                    return response()->json(['msg' => 'Data/hora de início inválida'], 422);
                }
                $dataHora = new DataHora($convertida);
                $tarefa->datahora_inicio = $dataHora->dataHoraInsert();
                $tarefa->save();

                $evento = new TarefaEvent($tarefa, TarefaEvent::UPDATE_DATAHORA_INICIO);
                $evento->acao = TarefaEvent::ACAO_ADD;
                Event::dispatch($evento);

                LogWeekly::create([
                    'quadro_id' => $quadro->id,
                    'tarefa_id' => $tarefa->id,
                    'descricao' => 'definiu a data de início desta tarefa',
                ]);
            }

            if ($request->acao == 'remove') {
                $tarefa->datahora_inicio = null;
                $tarefa->save();

                $evento = new TarefaEvent($tarefa, TarefaEvent::UPDATE_DATAHORA_INICIO);
                $evento->acao = TarefaEvent::ACAO_DELETE;
                Event::dispatch($evento);

                LogWeekly::create([
                    'quadro_id' => $quadro->id,
                    'tarefa_id' => $tarefa->id,
                    'descricao' => 'removeu a data de início desta tarefa',
                ]);
            }

            return response()->json(['tarefa' => $tarefa->fresh()], 200);
        } catch (\Throwable $e) {
            return response()->json(['msg' => 'Erro ao atualizar data de início'], 400);
        }
    }

    public function updateDataHoraEntrega(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);

        try {
            if ($request->acao == 'add') {
                $convertida = DataHora::converterDatePicker($request->datahora);
                if (!$convertida) {
                    return response()->json(['msg' => 'Data/hora de entrega inválida'], 422);
                }
                $codigoLembrete = $request->exists('lembrete')
                    ? $request->input('lembrete')
                    : $tarefa->lembrete_text;

                $dataHora = new DataHora($convertida);
                $tarefa->datahora_entrega = $dataHora->dataHoraInsert();
                $tarefa->lembrete = $codigoLembrete;
                $tarefa->save();

                $evento = new TarefaEvent($tarefa, TarefaEvent::UPDATE_DATAHORA_ENTREGA);
                $evento->acao = TarefaEvent::ACAO_ADD;
                Event::dispatch($evento);

                LogWeekly::create([
                    'quadro_id' => $quadro->id,
                    'tarefa_id' => $tarefa->id,
                    'descricao' => 'definiu a data de entrega desta tarefa',
                ]);
            }

            if ($request->acao == 'remove') {
                $tarefa->datahora_entrega = null;
                $tarefa->concluido = false;
                $tarefa->lembrete = null;
                $tarefa->save();

                $evento = new TarefaEvent($tarefa, TarefaEvent::UPDATE_DATAHORA_ENTREGA);
                $evento->acao = TarefaEvent::ACAO_DELETE;
                Event::dispatch($evento);

                LogWeekly::create([
                    'quadro_id' => $quadro->id,
                    'tarefa_id' => $tarefa->id,
                    'descricao' => 'removeu a data de entrega desta tarefa',
                ]);
            }

            return response()->json(['tarefa' => $tarefa->fresh()], 200);
        } catch (\Throwable $e) {
            return response()->json(['msg' => 'Erro ao atualizar data de entrega'], 400);
        }
    }

    public function atualizarOrdemCheckList(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);

        foreach ($request->novaLista ?? [] as $obj) {
            $id = $obj['id'] ?? null;
            if (!$id) {
                continue;
            }
            $payload = array_intersect_key($obj, array_flip(['ordem', 'titulo']));
            ChecklistsTarefa::whereTarefaId($tarefa->id)->whereId($id)->update($payload);
        }
        Event::dispatch(new TarefaEvent($tarefa, TarefaEvent::ORDENAR_CHECKLIST));

        return response()->json([], 200);
    }

    public function uploadAnexos(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);

        if ($request->file('arquivo') && $request->file('arquivo')->isValid()) {
            $mimeType = $request->file('arquivo')->getMimeType();

            if (in_array($mimeType, Arquivo::MIMEAPENASIMAGENSPDF)) {
                $arquivo = Arquivo::gravaArquivo($request, 'arquivo', Arquivo::DISCO_WEEKLY_REPORT);
                $arquivo->temporario = false;
                $arquivo->chave = null;
                $arquivo->save();
                $tarefa->Anexos()->attach($arquivo);

                Event::dispatch(new AnexoEvent($tarefa, AnexoEvent::INSERT));

                LogWeekly::create([
                    'quadro_id' => $quadro->id,
                    'tarefa_id' => $tarefa->id,
                    'descricao' => "anexo {$arquivo->nome}{$arquivo->extensao} a esta tarefa",
                ]);

                return response()->json($arquivo, 201);
            }

            return response()->json([
                'msg' => "O upload do arquivo \"{$request->file('arquivo')->getClientOriginalName()}\" falhou. Tipo de arquivo não permitido",
                'erros' => [],
            ], 400);
        }

        return response()->json([
            'msg' => 'O upload do anexo falhou',
            'erros' => [],
        ], 400);
    }

    public function uploadEditorImage(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);

        // TinyMCE envia "file"; o Upload do projeto usa "arquivo"
        if ($request->hasFile('file') && !$request->hasFile('arquivo')) {
            $request->files->set('arquivo', $request->file('file'));
        }

        if (!$request->hasFile('arquivo') || !$request->file('arquivo')->isValid()) {
            return response()->json(['msg' => 'Nenhuma imagem enviada'], 400);
        }

        $mimeType = $request->file('arquivo')->getMimeType();
        if (!in_array($mimeType, Arquivo::MIMEAPENASIMAGENS, true)) {
            return response()->json([
                'msg' => 'Apenas imagens (JPG, PNG ou GIF) são permitidas no editor',
                'erros' => [],
            ], 400);
        }

        try {
            $arquivo = Arquivo::gravaArquivo($request, 'arquivo', Arquivo::DISCO_WEEKLY_REPORT);
            $arquivo->temporario = false;
            $arquivo->chave = null;
            $arquivo->save();
            $tarefa->Anexos()->attach($arquivo);

            Event::dispatch(new AnexoEvent($tarefa, AnexoEvent::INSERT));

            $location = route('g.weekly-report.anexo-tarefa.anexo-show', [
                'empresa' => $empresa,
                'quadro' => $quadro->id,
                'lista' => $lista->id,
                'tarefa' => $tarefa->id,
                'arquivo' => $arquivo->file,
            ], true);

            return response()->json([
                'location' => $location,
                'arquivo' => $arquivo->fresh(),
            ], 201);
        } catch (\Throwable $e) {
            \Log::error('weekly-report uploadEditorImage: ' . $e->getMessage(), [
                'tarefa_id' => $tarefa->id,
                'exception' => $e,
            ]);

            return response()->json([
                'msg' => 'Erro ao enviar a imagem',
                'detail' => config('app.debug') ? $e->getMessage() : null,
            ], 400);
        }
    }

    public function anexoShow(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, $arquivo)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);

        if (Storage::disk(Arquivo::DISCO_WEEKLY_REPORT)->exists($arquivo)) {
            return Storage::disk(Arquivo::DISCO_WEEKLY_REPORT)->response($arquivo);
        }

        abort(404);
    }

    public function anexoUpdate(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, Arquivo $arquivo)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);

        $dadosValidados = \Validator::make($request->all(), [
            'nome' => 'min:1',
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao atualizar o anexo',
                'erros' => $dadosValidados->errors(),
            ], 400);
        }

        try {
            \DB::beginTransaction();
            $arquivo->update($request->only(['nome']));
            \DB::commit();
            Event::dispatch(new AnexoEvent($tarefa, AnexoEvent::UPDATE));

            return response()->json([]);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    public function anexoDelete(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, $arquivo)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);

        $model = Arquivo::whereDisco(Arquivo::DISCO_WEEKLY_REPORT)
            ->where(function ($q) use ($arquivo) {
                $q->where('file', $arquivo)->orWhere('thumb', $arquivo);
            })
            ->first(['id', 'nome', 'file', 'imagem', 'thumb', 'extensao', 'temporario']);

        if ($model) {
            $discoStorage = Storage::disk(Arquivo::DISCO_WEEKLY_REPORT);
            if ($discoStorage->exists($arquivo)) {
                if ($model->imagem) {
                    $discoStorage->delete($model->thumb);
                }
                $discoStorage->delete($model->file);
                $tarefa->Anexos()->detach($model->id);
                $model->delete();
                Event::dispatch(new AnexoEvent($tarefa, AnexoEvent::DELETE));
                LogWeekly::create([
                    'quadro_id' => $quadro->id,
                    'tarefa_id' => $tarefa->id,
                    'descricao' => "excluiu o anexo {$model->nome}{$model->extensao} desta tarefa",
                ]);

                return response()->json([], 200);
            }

            return response('Não foi possível apagar o anexo', 400);
        }

        abort(404);
    }

    public function download(Request $request, int $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, $arquivo)
    {
        $this->assertWeeklyHierarchy($empresa, $quadro, $lista, $tarefa);

        if (Storage::disk(Arquivo::DISCO_WEEKLY_REPORT)->exists($arquivo)) {
            $item = Arquivo::whereDisco(Arquivo::DISCO_WEEKLY_REPORT)
                ->where(function ($q) use ($arquivo) {
                    $q->where('file', $arquivo)->orWhere('thumb', $arquivo);
                })
                ->first(['nome', 'extensao']);

            if (!$item) {
                abort(404);
            }

            return Storage::disk(Arquivo::DISCO_WEEKLY_REPORT)->download($arquivo, $item->nome . $item->extensao);
        }

        abort(404);
    }
}
