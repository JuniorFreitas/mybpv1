<?php

namespace App\Http\Controllers;

use App\Models\FormaPagamento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FormaPagamentoController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('g.financeiro.formas-pagamento.index');
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
        $this->authorize('financeiro_formas-pagamento_insert');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            //'descricao' => 'required|min:3|unique:formas_pagamento,descricao',
            'descricao' => ['required','min:3',
                Rule::unique('formas_pagamento')->where(function ($query) use ($dados) {
                    return $query->whereDescricao($dados['descricao'])->whereEmpresaId(auth()->user()->empresa_id);
                })],
            'ativo' => 'required|boolean',

        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar uma nova classificação',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            FormaPagamento::create($dados);
            return response()->json([], 201);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\FormaPagamento $formaPagamento
     * @return \Illuminate\Http\Response
     */
    public function show(FormaPagamento $formaPagamento) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\FormaPagamento $formaPagamento
     * @return \Illuminate\Http\Response
     */
    public function edit(FormaPagamento $forma) {
        return $forma;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\FormaPagamento $formaPagamento
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FormaPagamento $forma) {
        $this->authorize('financeiro_formas-pagamento_update');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            //'descricao' => 'required|min:3|unique:formas_pagamento,descricao,' . $forma->id,
            'descricao' => ['required','min:3',
                Rule::unique('formas_pagamento')->where(function ($query) use ($dados,$forma) {
                    return $query->whereDescricao($dados['descricao'])->whereEmpresaId(auth()->user()->empresa_id)->whereNotIn('id',[$forma->id]);
                })],
            'ativo' => 'required|boolean'
        ]);

        /*$dadosValidados = \Validator::make($dados, [
            'tema' => ['required',
                Rule::unique('aulas')->where(function ($query) use ($dados) {
                    return $query->whereTema($dados['tema'])->whereTipodecursoId($dados['tipodecurso_id'])
                        ->whereCategoriaId($dados['categoria_id']);
                })],
            'ativo' => 'required',
        ]);*/
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar a classificação de plano de conta',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            $forma->update($dados);

            return response()->json([], 201);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\FormaPagamento $formaPagamento
     * @return \Illuminate\Http\Response
     */
    public function destroy(FormaPagamento $forma) {
        $this->authorize('financeiro_formas-pagamento_delete');
        $forma->delete();
        return response()->json([], 200);
    }

    //campo de busca
    public function atualizar(Request $request) {
        $this->authorize('financeiro_formas-pagamento');
        $porPagina = $request->get('porPagina');
        $busca = false;
        if ($request->has('campoBusca')) {
            $busca = $request->get('campoBusca');
            if (intval($busca) > 0) { // se encontrar um numero
                $resultado = FormaPagamento::where('id', '=', intval($busca))->orderBy('descricao');
            } else {
                $resultado = FormaPagamento::where('descricao', 'like', '%' . $busca . '%')->orderBy('descricao');
            }
        } else {
            $resultado = FormaPagamento::orderBy('descricao'); // senao busca tudo
        }
        $resultado = $resultado->paginate($porPagina);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => $resultado->items()
        ]);
    }
}
