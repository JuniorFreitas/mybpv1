<?php

namespace App\Http\Controllers;

use App\Models\Habilidade;
use App\Models\Papel;
use Illuminate\Http\Request;

class PapeisController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('g.configuracoes.papeis.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
//        $listaDeHabilidades = Habilidade::orderBy('nome', 'asc')->get()->map(function ($habilidade) {
//            $habilidade->acesso = true;
//            return $habilidade;
//        });

        $listaDeHabilidades = Papel::whereEmpresaId(auth()->user()->empresa_id)->where('master', true)->first()->habilidades->map(function ($habilidade) {
            $habilidade->acesso = true;
            return $habilidade;
        });;

        return response()->json($listaDeHabilidades, 201);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('configuracao_papel_insert');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;
        $dados['master'] = false;

        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required|min:3',
            'descricao' => 'required|min:3',
            'ativo' => 'required|boolean',
            'empresa_id' => 'required',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar papel',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            $papel = Papel::create($dados);
            $habilidades = collect($request->listaDeHabilidades)->filter(function ($habilidade) {
                if ($habilidade['acesso'] == 'true') {
                    return $habilidade;
                }
            })->pluck('id');
            $papel->habilidades()->attach($habilidades);

            return response()->json([], 201);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Papel $papel
     * @return \Illuminate\Http\Response
     */
    public function show(Papel $papel)
    {
        return response()->json($papel, 201);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Papel $papel
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function edit(Papel $papel)
    {
        $this->authorize('configuracao_papel_update');
        $papel->load('habilidades');

        $listaDeHabilidades = Papel::whereEmpresaId($papel->empresa_id)->where('master', true)->with('habilidades', function ($q) {
            $q->orderBy('nome');
        })->first();

        $listaDeHabilidades->habilidades->map(function ($habilidade) {
            $habilidade->acesso = false;
            return $habilidade;
        });

        return response()->json(['listaDeHabilidade' => $listaDeHabilidades->habilidades, 'papel' => $papel], 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Papel $papel
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, Papel $papel)
    {

        $this->authorize('configuracao_papel_update');

        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;
        $dados['master'] = false;

        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required|min:3',
            'descricao' => 'required|min:3',
            'ativo' => 'required|boolean',
            'empresa_id' => 'required',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao alterar papel',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            $papel->update($dados);
            $habilidades = collect($request->listaDeHabilidades)->filter(function ($habilidade) {
                if ($habilidade['acesso'] == 'true') {
                    return $habilidade;
                }
            })->pluck('id');
            $papel->habilidades()->sync($habilidades);
            return response()->json([], 201);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Papel $papel
     * @return \Illuminate\Http\Response
     */
    public function destroy(Papel $papel)
    {
        $this->authorize('configuracao_papel_delete');
        $papel->habilidades()->detach();
        $papel->delete();
    }

    public function atualizar(Request $request)
    {
        //$this->authorize('papel');
        $porPagina = $request->get('porPagina');
        $busca = false;

        $resultado = Papel::whereEmpresaId(auth()->user()->empresa_id)->where('master', false)->orderBy('nome');

        if ($request->has('campoBusca')) {
            $busca = $request->get('campoBusca');
            if (intval($busca) > 0) { // se encontrar um numero
                $resultado = $resultado->where('id', '=', intval($busca));
            } else {
                $resultado = $resultado->where('nome', 'like', '%' . $busca . '%');
            }
        }

        $resultado = $resultado->paginate($porPagina);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['itens' => $resultado->items(), 'empresa_id' => auth()->user()->empresa_id]
        ]);
    }

    public function ativaDesativa(Papel $papel)
    {
        $this->authorize('configuracao_papel_update');
        $papel->ativo = !$papel->ativo;
        $papel->save();
        $papel->refresh();
        return response()->json(['ativo' => $papel->ativo], 201);
    }
}
