<?php

namespace App\Http\Controllers;

use App\Models\CategoriaPlanoConta;
use App\Models\PlanoConta;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlanoContaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categorias = CategoriaPlanoConta::whereAtivo(true)->orderBy('descricao')->get(['id', 'descricao']);
        return view('g.financeiro.planos-conta.index', compact('categorias'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('plano-conta_insert');
        $dadosValidados = \Validator::make($request->input(), [
            //'descricao' => 'required|min:3|unique:plano_contas,descricao',
            'descricao' => ['required','min:3',
                Rule::unique('plano_contas')->where(function ($query) use ($request) {
                    return $query->whereDescricao($request->descricao)->whereEmpresaId(auth()->user()->empresa_id);
                })],
            'operacao' => 'required|string|min:1',
            'categoria_plano_id' => 'required|numeric|min:1',
            'ativo' => 'required|boolean',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar plano de conta',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {

            PlanoConta::create($request->input());
            return response()->json([], 201);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PlanoConta  $planoConta
     * @return \Illuminate\Http\Response
     */
    public function show(PlanoConta $planoConta)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PlanoConta  $planoConta
     * @return \Illuminate\Http\Response
     */
    public function edit(PlanoConta $plano)
    {
        return $plano;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PlanoConta  $planoConta
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PlanoConta $plano)
    {
        $this->authorize('plano-conta_update');

        $dadosValidados = \Validator::make($request->input(), [
            //'descricao' => 'required|min:3|unique:plano_contas,descricao,' . $plano->id,
            'descricao' => ['required','min:3',
                Rule::unique('plano_contas')->where(function ($query) use ($request,$plano) {
                    return $query->whereDescricao($request->descricao)->whereEmpresaId(auth()->user()->empresa_id)->whereNotIn('id',[$plano->id]);
                })],
            'operacao' => 'required|string|min:1',
            'categoria_plano_id' => 'required|numeric|min:1',
            'ativo' => 'required|boolean',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar a plano de conta',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            $plano->update($request->input());
            return response()->json([], 201);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PlanoConta  $planoConta
     * @return \Illuminate\Http\Response
     */
    public function destroy(PlanoConta $plano)
    {

        $this->authorize('plano-conta_delete');
        $plano->delete();
        return response()->json([], 200);
    }

    public function atualizar(Request $request)
    {
        $this->authorize('plano-conta');
        $categorias = CategoriaPlanoConta::orderBy('descricao')->get(['id', 'descricao']);
        $porPagina = $request->get('porPagina');
        $busca = false;
        if ($request->has('campoBusca')) {
            $busca = $request->get('campoBusca');
            if (intval($busca) > 0) { // se encontrar um numero
                $resultado = PlanoConta::where('id', '=', intval($busca))->orderBy('descricao');
            } else {
                $resultado = PlanoConta::where('descricao', 'like', '%' . $busca . '%')->orderBy('descricao');
            }
        } else {
            $resultado = PlanoConta::orderBy('descricao'); // senao busca tudo
        }
        $resultado->with('Categoria');

        $resultado = $resultado->paginate($porPagina);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => $resultado->items(),
            'categorias' => $categorias
        ]);
    }
}
