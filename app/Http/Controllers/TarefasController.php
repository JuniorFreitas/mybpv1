<?php

namespace App\Http\Controllers;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Events\WeeklyReport\AnexoEvent;
use App\Events\WeeklyReport\TarefaEvent;
use App\Jobs\Weekly_report\UpdateMembrosJob;
use App\Models\Arquivo;
use App\Models\ChecklistsTarefa;
use App\Models\ListaTarefa;
use App\Models\LogWeekly;
use App\Models\Notificacao;
use App\Models\Quadro;
use App\Models\Sistema;
use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Mail;
use MasterTag\DataHora;

class TarefasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista)
    {
        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'required|min:1',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao criar a tarefa',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            \DB::beginTransaction();
            $dados = $request->input();
            $dados['quadro_id'] = $quadro->id;
            $dados['ordem'] = 1;
            $ultimo = $lista->Tarefas()->orderByDesc('ordem')->first();
            if ($ultimo) {
                $dados['ordem'] = $ultimo->ordem + 1; // ordem do ultimo + 1
            }
            $request->replace($dados);
            $tarefa = Tarefa::create($request->input());
            \DB::commit();

            Event::dispatch(new TarefaEvent($tarefa, TarefaEvent::INSERT));
            LogWeekly::create(['quadro_id' => $quadro->id, 'descricao' => "adicionou {$tarefa->titulo} a lista {$lista->titulo}"]);


            return response()->json([], 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Tarefa $tarefa
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {
        return $tarefa;
        $tarefa->load('Logs');
        return $tarefa;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Tarefa $tarefa
     * @return \Illuminate\Http\Response
     */
    public function edit(Tarefa $tarefa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Tarefa $tarefa
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {
        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'min:1',
            //'descricao' => 'min:1',
            'concluido' => 'boolean'
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar a tarefa',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            \DB::beginTransaction();
            $dados = $request->only(['titulo', 'descricao', 'concluido', 'lembrete']);
            if ($request->filled('descricao')) {
                $dados['descricao'] = strip_tags($dados['descricao'], "<br>");
            }
            if ($request->has('lembrete')) {
                $tarefa->lembrete = $dados['lembrete'];//mutator
            }
            $tarefa->update($dados);

            \DB::commit();
            Event::dispatch(new TarefaEvent($tarefa, TarefaEvent::UPDATE));

            return response()->json([], 200);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Tarefa $tarefa
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {
        try {
            \DB::beginTransaction();
            //todo apagar todos os anexos das tarefas

            $idDelete = $tarefa->id;
            $tarefa->delete();

            \DB::commit();

            Event::dispatch(new TarefaEvent($lista, TarefaEvent::DELETE, $idDelete));
            LogWeekly::create(['quadro_id' => $quadro->id, 'descricao' => "apagou a tarefa {$tarefa->titulo} da lista {$lista->titulo}"]);

            return response()->json([], 200);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'msg' => $e->getMessage(),
                'lista' => $lista->Tarefas()->orderBy('ordem')->get()
            ], 400);
        }
    }

    public function atualizarOrdem(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista)
    {
        if ($request->evento == 'moveu') {
            foreach ($request->novaLista as $obj) {
                $id = $obj['id'];
                Tarefa::whereListaId($lista->id)->whereId($id)->update($obj);
            }
            Event::dispatch(new TarefaEvent($lista, TarefaEvent::ORDENAR));
        }
        if ($request->evento == 'adicionar') {
            $tarefa = Tarefa::find($request->tarefa_id);
            foreach ($request->novaLista as $obj) {
                Tarefa::whereId($obj['id'])->update($obj);
            }
            Event::dispatch(new TarefaEvent($lista, TarefaEvent::ORDENAR));
            if ($tarefa) {
                LogWeekly::create(['quadro_id' => $quadro->id, 'descricao' => "moveu {$tarefa->titulo} para a lista {$lista->titulo}"]);
            }

        }
        if ($request->evento == 'remover') {
            foreach ($request->novaLista as $obj) {
                Tarefa::whereId($obj['id'])->update($obj);
            }
            Event::dispatch(new TarefaEvent($lista, TarefaEvent::ORDENAR));
        }

    }

    public function updateMembro(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {

        if ($request->acao == 'add') {
            $tarefa->Membros()->attach($request->user_id);
            $evento = new TarefaEvent($tarefa, TarefaEvent::UPDATE_MEMBROS);
            $evento->acao = TarefaEvent::ACAO_ADD;
            Event::dispatch($evento);
            Event::dispatch(new NotificacaoEvent([
                'tarefa' => $tarefa,
                'user_id' => $request->user_id
            ], NotificacaoEvent::MEMBRO_TAREFA_ADD, NotificacaoEvent::TIPO_PADRAO));
            //Notificar por e-mail
            if (Sistema::validaEmail(auth()->user()->login) && Sistema::validaEmail(User::find($request->user_id)->login)) {
                UpdateMembrosJob::dispatch([
                    'de' => auth()->user(),
                    'para' => User::find($request->user_id),
                    'acao' => TarefaEvent::ACAO_ADD,
                    'modelTarefa' => $tarefa,
                    'empresa_id' => $empresa->empresa_id,
                ]);
            }

        }
        if ($request->acao == 'remove') {
            $evento = new TarefaEvent($tarefa, TarefaEvent::UPDATE_MEMBROS);
            $evento->acao = TarefaEvent::ACAO_DELETE;
            $tarefa->Membros()->detach($request->user_id);
            Event::dispatch($evento);
            Event::dispatch(new NotificacaoEvent([
                'tarefa' => $tarefa,
                'user_id' => $request->user_id
            ], NotificacaoEvent::MEMBRO_TAREFA_REMOVE, NotificacaoEvent::TIPO_PADRAO));
            //Notificar por e-mail
            if (Sistema::validaEmail(auth()->user()->login) && Sistema::validaEmail(User::find($request->user_id)->login)) {
                UpdateMembrosJob::dispatch([
                    'de' => auth()->user(),
                    'para' => User::find($request->user_id),
                    'acao' => TarefaEvent::ACAO_DELETE,
                    'modelTarefa' => $tarefa,
                    'empresa_id' => $empresa->empresa_id,
                ]);
            }
        }

    }

    public function updateDataHoraInicio(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {

        if ($request->acao == 'add') {

            $dataHora = new DataHora(DataHora::converterDatePicker($request->datahora));
            $tarefa->datahora_inicio = $dataHora->dataHoraInsert();
            $tarefa->save();

            $evento = new TarefaEvent($tarefa, TarefaEvent::UPDATE_DATAHORA_INICIO);
            $evento->acao = TarefaEvent::ACAO_ADD;
            Event::dispatch($evento);
        }
        if ($request->acao == 'remove') {
            $tarefa->datahora_inicio = null;
            $tarefa->save();

            $evento = new TarefaEvent($tarefa, TarefaEvent::UPDATE_DATAHORA_INICIO);
            $evento->acao = TarefaEvent::ACAO_DELETE;
            Event::dispatch($evento);
        }

    }

    public function updateDataHoraEntrega(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {

        if ($request->acao == 'add') {

            $dataHora = new DataHora(DataHora::converterDatePicker($request->datahora));
            $tarefa->datahora_entrega = $dataHora->dataHoraInsert();

            if ($request->has('lembrete')) {
                $tarefa->lembrete = $request->lembrete;
            }
            $tarefa->save();

            $evento = new TarefaEvent($tarefa, TarefaEvent::UPDATE_DATAHORA_ENTREGA);
            $evento->acao = TarefaEvent::ACAO_ADD;
            Event::dispatch($evento);
        }
        if ($request->acao == 'remove') {
            $tarefa->datahora_entrega = null;
            $tarefa->concluido = false;
            $tarefa->lembrete = null;
            $tarefa->save();

            $evento = new TarefaEvent($tarefa, TarefaEvent::UPDATE_DATAHORA_ENTREGA);
            $evento->acao = TarefaEvent::ACAO_DELETE;
            Event::dispatch($evento);
        }

    }

    public function atualizarOrdemCheckList(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {
        foreach ($request->novaLista as $obj) {
            $id = $obj['id'];
            ChecklistsTarefa::whereTarefaId($tarefa->id)->whereId($id)->update($obj);
        }
        Event::dispatch(new TarefaEvent($tarefa, TarefaEvent::ORDENAR_CHECKLIST));

    }

    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa)
    {
        if ($request->file('arquivo')->isValid()) {
            $mimeType = $request->file('arquivo')->getMimeType();

            if (in_array($mimeType, Arquivo::MIMEAPENASIMAGENSPDF)) {
                $arquivo = Arquivo::gravaArquivo($request, 'arquivo', Arquivo::DISCO_WEEKLY_REPORT);
                $arquivo->temporario = false;
                $arquivo->chave = null;
                $arquivo->save();
                $tarefa->Anexos()->attach($arquivo);

                Event::dispatch(new AnexoEvent($tarefa, AnexoEvent::INSERT));

                LogWeekly::create(['quadro_id' => $quadro->id, 'tarefa_id' => $tarefa->id, 'descricao' => "anexo {$arquivo->nome}{$arquivo->extensao} a esta tarefa"]);

                return response()->json($arquivo, 201);
            } else {
                return response()->json([
                    'msg' => "O upload do arquivo \"{$request->file('arquivo')->getClientOriginalName()}\" falhou. Tipo de arquivo não permitido",
                    'erros' => []
                ], 400);
            }
        } else {
            return response()->json([
                'msg' => "O upload do anexo falhou",
                'erros' => []
            ], 400);
        }
    }

    public function anexoShow(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, $arquivo)
    {
        if (Storage::disk(Arquivo::DISCO_WEEKLY_REPORT)->exists($arquivo)) {
            return \Storage::disk(Arquivo::DISCO_WEEKLY_REPORT)->response($arquivo);
        }
    }

    public function anexoUpdate(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, Arquivo $arquivo)
    {
        $dadosValidados = \Validator::make($request->all(), [
            'nome' => 'min:1',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar o anexo',
                'erros' => $dadosValidados->errors()
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

    public function anexoDelete(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, $arquivo)
    {
        $model = Arquivo::whereFile($arquivo)
            ->whereDisco(Arquivo::DISCO_WEEKLY_REPORT)
            ->orWhere('thumb', $arquivo)
            ->first(['id', 'nome', 'file', 'imagem', 'thumb', 'extensao', 'temporario']);

        if ($model) {
            $discoStorage = Storage::disk(Arquivo::DISCO_WEEKLY_REPORT);
            if ($discoStorage->exists($arquivo)) {
                if ($model->imagem) {
                    $discoStorage->delete($model->thumb);
                }
                $discoStorage->delete($model->file);
                $model->delete();
                Event::dispatch(new AnexoEvent($tarefa, AnexoEvent::DELETE));
                LogWeekly::create(['quadro_id' => $quadro->id, 'tarefa_id' => $tarefa->id, 'descricao' => "excluiu o anexo {$model->nome}{$model->extensao} desta tarefa"]);
                return response()->json([], 200);
            }
            return response("Não foi possível apagar o anexo", 400);
        }
    }

    public function download(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, $arquivo)
    {
        if (Storage::disk(Arquivo::DISCO_WEEKLY_REPORT)->exists($arquivo)) {
            $item = Arquivo::whereFile($arquivo)->whereDisco(Arquivo::DISCO_WEEKLY_REPORT)->orWhere('thumb', $arquivo)->first(['nome', 'extensao']);
            return Storage::disk(Arquivo::DISCO_WEEKLY_REPORT)->download($arquivo, $item->nome . $item->extensao);
        }
        abort(404);
    }

}
