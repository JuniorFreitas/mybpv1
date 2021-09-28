<?php

namespace App\Http\Controllers;

use App\Events\WeeklyReport\ListaEvent;
use App\Models\ListaTarefa;
use App\Models\LogWeekly;
use App\Models\Quadro;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class ListaTarefaController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, User $empresa, Quadro $quadro) {
        return response()->json([
            'lista' => $quadro->Listas()->orderBy('ordem')->get(),
            'atividades' => $quadro->Logs()->take(5)->get(),

        ], 200);
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
    public function store(Request $request, User $empresa, Quadro $quadro) {
        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'required|min:1',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao criar a lista',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            \DB::beginTransaction();
            $dados = $request->input();
            $dados['quadro_id'] = $quadro->id;
            $dados['ordem'] = 1;
            $ultimo = $quadro->Listas()->orderByDesc('ordem')->first();
            if ($ultimo) {
                $dados['ordem'] = $ultimo->ordem + 1; // ordem do ultimo + 1
            }
            $request->replace($dados);
            $lista = ListaTarefa::create($request->input());

            LogWeekly::create(['quadro_id'=>$quadro->id,'descricao'=>"Criou a lista {$request->titulo} a este quadro"]);

            \DB::commit();

            Event::dispatch(new ListaEvent($lista, ListaEvent::INSERT));


            return response()->json([], 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Models\ListaTarefa $listaTarefa
     * @return \Illuminate\Http\Response
     */
    public function show(ListaTarefa $lista) {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Models\ListaTarefa $listaTarefa
     * @return \Illuminate\Http\Response
     */
    public function edit(ListaTarefa $listaTarefa) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Models\ListaTarefa $listaTarefa
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista) {
        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'required|min:1',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar a lista',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            \DB::beginTransaction();
            $lista->update($request->input());

            \DB::commit();

            Event::dispatch(new ListaEvent($lista, ListaEvent::UPDATE));

            return response()->json([], 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Models\ListaTarefa $listaTarefa
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, User $empresa, Quadro $quadro, ListaTarefa $lista) {
        try {
            \DB::beginTransaction();
            //todo apagar todos os anexos das tarefas

            $idDelete = $lista->id;
            $lista->delete();

            LogWeekly::create(['quadro_id'=>$quadro->id,'descricao'=>"Apagou a lista {$request->titulo}"]);

            \DB::commit();

            Event::dispatch(new ListaEvent($quadro,ListaEvent::DELETE,$idDelete));



            return response()->json([], 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'msg' => $e->getMessage(),
                'lista' => $quadro->Listas()->orderBy('ordem')->with([
                    'Tarefas.Membros',
                    'Tarefas.Checklists.Itens',
                ])->get()
            ], 400);
        }
    }

    public function atualizarOrdem(Request $request,User $empresa, Quadro $quadro){
        foreach ($request->novaLista as $obj){
            $id = $obj['id'];
            ListaTarefa::whereQuadroId($quadro->id)->whereId($id)->update($obj);
        }
        Event::dispatch(new ListaEvent($quadro, ListaEvent::ORDENAR));
    }
}
