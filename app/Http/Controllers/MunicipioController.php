<?php

namespace App\Http\Controllers;

use App\Models\Municipio;
use Illuminate\Http\Request;

class MunicipioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.configuracoes.municipios.index');
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
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('municipios_insert');
        $dados = $request->input();
        $dados['capital'] = $dados['capital'] =='true' ? true:false;

        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required|min:3',
            'uf' => 'required|min:2',
            'capital' => 'required|boolean',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar municipio',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            Municipio::create($dados);
            return response()->json([], 201);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Municipio $municipio
     * @return \Illuminate\Http\Response
     */
    public function show(Municipio $municipio)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Municipio $municipio
     * @return \Illuminate\Http\Response
     */
    public function edit(Municipio $municipio)
    {
        $this->authorize('municipios_update');
        return $municipio;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Models\Municipio $municipio
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Municipio $municipio)
    {
        $this->authorize('municipios_update');

        $dados = $request->input();
        $dados['capital'] = $dados['capital'] =='true' ? true:false;

        $dadosValidados = \Validator::make($dados, [
            'nome' => 'required|min:3',
            'uf' => 'required|min:2',
            'capital' => 'required|boolean',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar a municipio',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            $municipio->update($dados);
            return response()->json([], 201);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Municipio $municipio
     * @return \Illuminate\Http\Response
     */
    public function destroy(Municipio $municipio)
    {
        //
        $this->authorize('municipios_delete');
        $municipio->delete();
    }

    public function atualizar(Request $request)
    {
        $this->authorize('municipios');
        $porPagina = $request->get('porPagina');
        $busca = false;
        if ($request->has('campoBusca')) {
            $busca = $request->get('campoBusca');
            if (intval($busca) > 0) { // se encontrar um numero
                $resultado = Municipio::where('id', '=', intval($busca))->orderBy('uf');
            } else {
                $resultado = Municipio::where('nome', 'like', '%' . $busca . '%')->orderBy('uf');
            }
        } else {
            $resultado = Municipio::orderBy('uf'); // senao busca tudo
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
