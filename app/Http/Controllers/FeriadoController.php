<?php

namespace App\Http\Controllers;

use App\Models\Feriado;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class FeriadoController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {

        return view('g.controle-ponto.feriados.index');
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
    public function store(Request $request) {

        $this->authorize('controle_ponto_feriados_insert');
        $dados = $request->input();
        $data = new DataHora($request->data);

        $dadosValidados = \Validator::make($dados, [
            'descricao' => 'required|min:3',
            'data' => 'required',
            'ativo' => 'required',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar feriado',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            $dados['data'] = $data->dataInsert();
            Feriado::create($dados);
            return response()->json([], 201);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Feriado $feriado
     * @return \Illuminate\Http\Response
     */
    public function show(Feriado $feriado) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Feriado $feriado
     * @return \Illuminate\Http\Response
     */
    public function edit(Feriado $feriado) {
        $this->authorize('controle_ponto_feriados_update');
        return $feriado;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Feriado $feriado
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Feriado $feriado) {
        $this->authorize('controle_ponto_feriados_update');
        $dados = $request->input();
        $data = new DataHora($dados['data']);
        $dados['data'] = $data->dataInsert();

        $dadosValidados = \Validator::make($dados, [
            'descricao' => 'required|min:3',
            'data' => 'required',
            'ativo' => 'required',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar a feriado',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            $feriado->update($dados);
            return response()->json([], 201);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Feriado $feriado
     * @return \Illuminate\Http\Response
     */
    public function destroy(Feriado $feriado) {
        $this->authorize('controle_ponto_feriados_delete');
        $feriado->delete();
    }

    public function atualizar(Request $request) {
        $this->authorize('controle_ponto_feriados');
        $porPagina = $request->get('porPagina');
        $busca = false;
        if ($request->has('campoBusca')) {
            $busca = $request->get('campoBusca');
            if (intval($busca) > 0) { // se encontrar um numero
                $resultado = Feriado::where('id', '=', intval($busca))->orderBy('descricao');
            } else {
                $resultado = Feriado::where('descricao', 'like', '%' . $busca . '%')->orderBy('descricao');
            }
        } else {
            $resultado = Feriado::orderBy('descricao'); // senao busca tudo
        }
        $resultado = $resultado->paginate($porPagina);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => $resultado->items()
        ]);
    }

    public function searchFeriado() {
        return Feriado::all()->map(function ($f) {
            return $f = $f->descricao;
        });
    }

    public function ativaDesativa(Feriado $feriado) {
        $feriado->ativo = !$feriado->ativo;
        $feriado->save();
        $feriado->refresh();
        return response()->json(['ativo' => $feriado->ativo], 201);
    }
}
