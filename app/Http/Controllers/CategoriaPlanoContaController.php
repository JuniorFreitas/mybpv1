<?php

namespace App\Http\Controllers;

use App\Models\CategoriaPlanoConta;
use Illuminate\Http\Request;
class CategoriaPlanoContaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.financeiro.classificacao-plano-conta.index');
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
    public function store(\App\Http\Requests\Financeiro\StoreCategoriaPlanoContaRequest $request)
    {
        $this->authorize('financeiro_classificacao-plano-conta_insert');
        CategoriaPlanoConta::create($request->validated());
        return response()->json([], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CategoriaPlanoConta  $categoria
     * @return \Illuminate\Http\Response
     */
    public function show(CategoriaPlanoConta $categoria)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CategoriaPlanoConta  $categoriaPlanoConta
     * @return \Illuminate\Http\Response
     */
    public function edit(CategoriaPlanoConta $categoria)
    {
        return $categoria;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CategoriaPlanoConta  $categoria
     * @return \Illuminate\Http\Response
     */
    public function update(\App\Http\Requests\Financeiro\UpdateCategoriaPlanoContaRequest $request, CategoriaPlanoConta $categoria)
    {
        $this->authorize('financeiro_classificacao-plano-conta_update');
        $categoria->update($request->validated());
        return response()->json([], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CategoriaPlanoConta  $categoria
     * @return \Illuminate\Http\Response
     */
    public function destroy(CategoriaPlanoConta $categoria)
    {
        $this->authorize('financeiro_classificacao-plano-conta_delete');
        $categoria->delete();
        return response()->json([], 200);
    }

    //campo de busca
    public function atualizar(Request $request)
    {
        $this->authorize('financeiro_classificacao-plano-conta');
        $porPagina = $request->get('porPagina');
        $busca = false;
        if ($request->has('campoBusca')) {
            $busca = $request->get('campoBusca');
            if (intval($busca) > 0) { // se encontrar um numero
                $resultado = CategoriaPlanoConta::where('id', '=', intval($busca))->orderBy('descricao');
            } else {
                $resultado = CategoriaPlanoConta::where('descricao', 'like', '%' . $busca . '%')->orderBy('descricao');
            }
        } else {
            $resultado = CategoriaPlanoConta::orderBy('descricao'); // senao busca tudo
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
