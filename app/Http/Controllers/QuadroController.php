<?php

namespace App\Http\Controllers;

use App\Events\WeeklyReport\QuadroEvent;
use App\Models\Arquivo;
use App\Models\LogWeekly;
use App\Models\Quadro;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class QuadroController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('g.weekly-report.index');
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
    public function store(Request $request,User $empresa) {
        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'required|min:1',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao criar o quadro',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            \DB::beginTransaction();

            $quadro = Quadro::create($request->input());
            \DB::commit();

            Event::dispatch(new QuadroEvent($quadro,QuadroEvent::INSERT));

            return response()->json([], 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Quadro $quadro
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,  User $empresa) {
        return response()->json([
            'lista' => Quadro::all(),
            'quadro_insert' => auth()->user()->can('quadro_insert'),
            'quadro_update' => auth()->user()->can('quadro_update'),
            'quadro_delete' => auth()->user()->can('quadro_delete'),
            // 'lista_insert' => auth()->user()->can('lista_quadro_insert'),
            // 'lista_update' => auth()->user()->can('lista_quadro_update'),
            // 'lista_delete' => auth()->user()->can('lista_quadro_delete'),
            'weekly-report_quadro_lista_insert' => auth()->user()->can('lista_quadro_insert'),
            'weekly-report_quadro_lista_update' => auth()->user()->can('lista_quadro_update'),
            'weekly-report_quadro_lista_delete' => auth()->user()->can('lista_quadro_delete'),
            'tarefa_insert' => auth()->user()->can('tarefa_insert'),
            'tarefa_update' => auth()->user()->can('tarefa_update'),
            'tarefa_delete' => auth()->user()->can('tarefa_delete'),
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Quadro $quadro
     * @return \Illuminate\Http\Response
     */
    public function edit(Quadro $quadro) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Quadro $quadro
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $empresa, Quadro $quadro) {
        $dadosValidados = \Validator::make($request->all(), [
            'titulo' => 'required|min:1',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar o quadro',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            \DB::beginTransaction();
            $quadro->update($request->input());

            \DB::commit();

            Event::dispatch(new QuadroEvent($quadro,QuadroEvent::UPDATE));

            return response()->json([], 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['msg' => $e->getMessage()], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Quadro $quadro
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, User $empresa, Quadro $quadro) {
        try {
            \DB::beginTransaction();
            $idDelete = $quadro->id;
            $quadro->delete();

            \DB::commit();

            Event::dispatch(new QuadroEvent($idDelete,QuadroEvent::DELETE));

            return response()->json([], 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'msg' => $e->getMessage(),
                'lista' => Quadro::all()
            ], 400);
        }


    }
}
