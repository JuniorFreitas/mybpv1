<?php

namespace App\Http\Controllers;

use App\Events\WeeklyReport\ItemChecklistEvent;
use App\Models\ChecklistsTarefa;
use App\Models\ChecklistsTarefaItem;
use App\Models\ListaTarefa;
use App\Models\LogWeekly;
use App\Models\Quadro;
use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class ChecklistsTarefaItemController extends Controller
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, ChecklistsTarefa $checklist)
    {
        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'required|min:1',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao criar a item',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            \DB::beginTransaction();
            $dados = $request->input();
            $dados['checklist_id'] = $tarefa->id;
            $dados['ordem'] = 1;
            $ultimo = $checklist->Itens()->orderByDesc('ordem')->first();
            if ($ultimo) {
                $dados['ordem'] = $ultimo->ordem + 1; // ordem do ultimo + 1
            }
            $request->replace($dados);
            $item = $checklist->Itens()->create($request->input());
            \DB::commit();

            Event::dispatch(new ItemChecklistEvent($item, ItemChecklistEvent::INSERT));

            return response()->json([], 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ChecklistsTarefaItem  $checklistsTarefaItem
     * @return \Illuminate\Http\Response
     */
    public function show(ChecklistsTarefaItem $checklistsTarefaItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ChecklistsTarefaItem  $checklistsTarefaItem
     * @return \Illuminate\Http\Response
     */
    public function edit(ChecklistsTarefaItem $checklistsTarefaItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ChecklistsTarefaItem  $checklistsTarefaItem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, ChecklistsTarefa $checklist, ChecklistsTarefaItem $item)
    {
        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'min:1',
            'concluido'=>'boolean'
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar  descrição do item',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            \DB::beginTransaction();
            $dados = $request->only(['titulo','concluido']);
            $item->update($dados);

            \DB::commit();

            if($request->has('concluido')){
                if($dados['concluido']){
                    LogWeekly::create(['quadro_id'=>$quadro->id,'tarefa_id'=>$tarefa->id,'descricao'=>"concluiu {$item->titulo} nesta tarefa"]);
                }else{
                    LogWeekly::create(['quadro_id'=>$quadro->id,'tarefa_id'=>$tarefa->id,'descricao'=>" não concluiu {$item->titulo} nesta tarefa"]);
                }
            }

            Event::dispatch(new ItemChecklistEvent($item, ItemChecklistEvent::UPDATE));

            return response()->json([], 200);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ChecklistsTarefaItem  $checklistsTarefaItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista, Tarefa $tarefa, ChecklistsTarefa $checklist, ChecklistsTarefaItem $item)
    {

        try {
            \DB::beginTransaction();

            $idDelete = $item->id;
            Event::dispatch(new ItemChecklistEvent($item,ItemChecklistEvent::DELETE,$idDelete));

            $item->delete();
            \DB::commit();

            return response()->json([], 200);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'msg' => $e->getMessage(),
            ], 400);
        }
    }
}
