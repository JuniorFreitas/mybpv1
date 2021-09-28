<?php

namespace App\Http\Controllers;

use App\Events\WeeklyReport\CheckListTarefaEvent;
use App\Models\ChecklistsTarefa;
use App\Models\ChecklistsTarefaItem;
use App\Models\ListaTarefa;
use App\Models\Quadro;
use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class ChecklistsTarefaController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa) {
        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'required|min:1',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao criar a checklist',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            \DB::beginTransaction();
            $dados = $request->input();
            $dados['tarefa_id'] = $tarefa->id;
            $dados['ordem'] = 1;
            $ultimo = $tarefa->Checklists()->orderByDesc('ordem')->first();
            if ($ultimo) {
                $dados['ordem'] = $ultimo->ordem + 1; // ordem do ultimo + 1
            }
            $request->replace($dados);
            $checkList = $tarefa->Checklists()->create($request->input());
            \DB::commit();

            Event::dispatch(new CheckListTarefaEvent($checkList, CheckListTarefaEvent::INSERT));

            return response()->json($checkList, 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\ChecklistsTarefa $checklistsTarefa
     * @return \Illuminate\Http\Response
     */
    public function show(ChecklistsTarefa $checklistsTarefa) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\ChecklistsTarefa $checklistsTarefa
     * @return \Illuminate\Http\Response
     */
    public function edit(ChecklistsTarefa $checklistsTarefa) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ChecklistsTarefa $checklistsTarefa
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, ChecklistsTarefa $checklist) {
        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'min:1',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar o título da checklist',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            \DB::beginTransaction();
            $dados = $request->only('titulo');
            $checklist->update($dados);

            \DB::commit();

            Event::dispatch(new CheckListTarefaEvent($checklist, CheckListTarefaEvent::UPDATE));

            return response()->json([], 200);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\ChecklistsTarefa $checklistsTarefa
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, ChecklistsTarefa $checklist) {

        try {
            \DB::beginTransaction();

            $idDelete = $checklist->id;
            Event::dispatch(new CheckListTarefaEvent($checklist,CheckListTarefaEvent::DELETE,$idDelete));

            $checklist->delete();
            \DB::commit();

            return response()->json([], 200);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'msg' => $e->getMessage(),
            ], 400);
        }
    }

    public function atualizarOrdemItens(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, ChecklistsTarefa $checklist){
        if($request->evento=='moveu'){
            foreach ($request->novaLista as $obj){
                $id = $obj['id'];
                ChecklistsTarefaItem::whereChecklistId($checklist->id)->whereId($id)->update($obj);
            }
            Event::dispatch(new CheckListTarefaEvent($checklist, CheckListTarefaEvent::ORDENAR_ITENS));
        }
        if($request->evento=='adicionar'){
            foreach ($request->novaChecklist as $obj){
                ChecklistsTarefaItem::whereId($obj['id'])->update($obj);
            }
            Event::dispatch(new CheckListTarefaEvent($checklist, CheckListTarefaEvent::ORDENAR_ITENS));
        }
        if($request->evento=='remover'){
            foreach ($request->antigaChecklist as $obj){
                ChecklistsTarefaItem::whereId($obj['id'])->update($obj);
            }
            Event::dispatch(new CheckListTarefaEvent($checklist, CheckListTarefaEvent::ORDENAR_ITENS));

        }
    }
}
