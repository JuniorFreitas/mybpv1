<?php

namespace App\Http\Controllers;

use App\Models\EmpresaPerimetro;
use App\Models\User;
use Illuminate\Http\Request;

class PerimetroController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //return auth()->user()->PerimetrosEmpresa;
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
        $this->authorize('perimetros_insert');
        $dados = $request->only([
            'descricao','lat','long','perimetro','obrigatorio'
        ]);

        $dadosValidados = \Validator::make($dados, [
            'descricao' => 'required|min:3',
            'lat' => 'numeric',
            'long' => 'numeric',
            'perimetro' => 'numeric',
            'obrigatorio' => 'required|boolean',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao salvar o perímetro',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            $perimetro = EmpresaPerimetro::create($dados);

            return response()->json($perimetro, 201);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\EmpresaPerimetro $perimetro
     * @return \Illuminate\Http\Response
     */
    public function show(EmpresaPerimetro $perimetro) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\EmpresaPerimetro $perimetro
     * @return \Illuminate\Http\Response
     */
    public function edit(EmpresaPerimetro $perimetro) {
        return $perimetro;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\EmpresaPerimetro $perimetro
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmpresaPerimetro $perimetro) {

        $this->authorize('perimetros_update');
        $dados = $request->only([
            'descricao','lat','long','perimetro','obrigatorio'
        ]);

        $dadosValidados = \Validator::make($dados, [
            'descricao' => 'required|min:3|unique:empresa_perimetros,descricao,'.$perimetro->id,
            'lat' => 'numeric',
            'long' => 'numeric',
            'obrigatorio' => 'required|boolean',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao salvar o perímetro',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            $perimetro->update($dados);


            return response()->json($perimetro, 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\EmpresaPerimetro $perimetro
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmpresaPerimetro $perimetro) {
        $this->authorize('perimetros_delete');
        $perimetro->delete();
        return response()->json([],200);
    }

    public function assosicarPerimetro(Request $request) {
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados, [
            'perimetro_id' => 'required|min:1',
            'funcionariosSelecionados' => 'required|array|min:1',
        ]);


        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao associar perímetros',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            foreach ($request->funcionariosSelecionados as $funcnionario_id) {
                $user = User::find($funcnionario_id);
                if ($request->perimetro_id > 0) {
                    $user->PerimetrosFuncionario()->sync($request->perimetro_id);
                } else {
                    $user->PerimetrosFuncionario()->detach();
                }
            }


            return response()->json([], 200);
        }
    }

    public function atualizarPerimetros(Request $request) {

        $resultado = auth()->user()->PerimetrosEmpresa();

        $porPagina = $request->get('porPagina');
        $busca = false;
        if ($request->filled('campoBusca')) {
            $busca = $request->get('campoBusca');
            $resultado = $resultado->where('descricao', 'like', '%' . $busca . '%');
        } else {
            $resultado = $resultado->orderBy('descricao'); // senao busca tudo
        }
        /*$resultado->with([
            'Empresa:id,nome',
            'PerimetrosFuncionario:id,descricao'
        ]);*/

        $resultado = $resultado->paginate($porPagina);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => $resultado->items(),
        ]);
    }

}
