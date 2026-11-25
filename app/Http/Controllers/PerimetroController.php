<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cadastros\AssociarPerimetroRequest;
use App\Http\Requests\Cadastros\StorePerimetroRequest;
use App\Http\Requests\Cadastros\UpdatePerimetroRequest;
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
    public function store(StorePerimetroRequest $request) {
        $dados = $request->only([
            'descricao','lat','long','perimetro','obrigatorio'
        ]);

        $perimetro = EmpresaPerimetro::create($dados);

        return response()->json($perimetro, 201);
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
    public function update(UpdatePerimetroRequest $request, EmpresaPerimetro $perimetro) {
        $dados = $request->only([
            'descricao','lat','long','perimetro','obrigatorio'
        ]);

        $perimetro->update($dados);

        return response()->json($perimetro, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\EmpresaPerimetro $perimetro
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmpresaPerimetro $perimetro) {
        $this->authorize('controle_ponto_perimetros_delete');
        $perimetro->delete();
        return response()->json([],200);
    }

    public function assosicarPerimetro(AssociarPerimetroRequest $request) {
        foreach ($request->funcionariosSelecionados as $funcnionario_id) {
            $user = User::find($funcnionario_id);
            if (is_null($request->perimetro_id) && count($request->perimetrosSelecionados)>0) {
                $user->PerimetrosFuncionario()->sync($request->perimetrosSelecionados);
            } else {
                $user->PerimetrosFuncionario()->detach();
            }
        }

        return response()->json([], 200);
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
        $perimetrosSelecionados = auth()->user()->PerimetrosEmpresa->map(function ($item){
            return [
                'id' => $item->id,
                'descricao' => $item->descricao,
                'selecionado' => false
            ];
        });

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'todos_perimetros' => $perimetrosSelecionados
            ],
        ]);
    }

}
