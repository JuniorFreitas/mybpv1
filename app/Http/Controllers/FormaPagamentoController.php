<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cadastros\StoreFormaPagamentoRequest;
use App\Http\Requests\Cadastros\UpdateFormaPagamentoRequest;
use App\Models\FormaPagamento;
use Illuminate\Http\Request;

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
    public function store(StoreFormaPagamentoRequest $request) {
        FormaPagamento::create($request->validated());
        return response()->json([], 201);
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
    public function update(UpdateFormaPagamentoRequest $request, FormaPagamento $forma) {
        $forma->update($request->validated());

        return response()->json([], 201);
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
