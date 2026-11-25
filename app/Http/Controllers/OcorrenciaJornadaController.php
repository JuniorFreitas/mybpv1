<?php

namespace App\Http\Controllers;

use App\Http\Requests\Operacional\StoreOcorrenciaJornadaRequest;
use App\Http\Requests\Operacional\UpdateOcorrenciaJornadaRequest;
use App\Models\OcorrenciaJornada;
use Illuminate\Http\Request;

class OcorrenciaJornadaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.controle-ponto..ocorrencias_jornadas.index');
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
    public function store(StoreOcorrenciaJornadaRequest $request)
    {
        $this->authorize('controle_ponto_ocorrencias_jornadas_insert');
        $dados = $request->validated();

        OcorrenciaJornada::create($dados);

        return response()->json([], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OcorrenciaJornada  $categoria
     * @return \Illuminate\Http\Response
     */
    public function show(OcorrenciaJornada $categoria)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OcorrenciaJornada  $categoriaPlanoConta
     * @return \Illuminate\Http\Response
     */
    public function edit(OcorrenciaJornada $ocorrencia_jornada)
    {
        return $ocorrencia_jornada;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OcorrenciaJornada  $ocorrencia_jornada
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOcorrenciaJornadaRequest $request, OcorrenciaJornada $ocorrencia_jornada)
    {
        $this->authorize('controle_ponto_ocorrencias_jornadas_update');
        $dados = $request->validated();

        $ocorrencia_jornada->update($dados);

        return response()->json([], 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OcorrenciaJornada  $ocorrencia_jornada
     * @return \Illuminate\Http\Response
     */
    public function destroy(OcorrenciaJornada $ocorrencia_jornada)
    {
        $this->authorize('controle_ponto_ocorrencias_jornadas_delete');
        $ocorrencia_jornada->delete();
        return response()->json([], 200);
    }

    //campo de busca
    public function atualizar(Request $request)
    {
        $this->authorize('controle_ponto_ocorrencias_jornadas');
        $porPagina = $request->get('porPagina');
        $busca = false;
        if ($request->has('campoBusca')) {
            $busca = $request->get('campoBusca');
            if (intval($busca) > 0) { // se encontrar um numero
                $resultado = OcorrenciaJornada::where('id', '=', intval($busca))->orderBy('descricao');
            } else {
                $resultado = OcorrenciaJornada::where('descricao', 'like', '%' . $busca . '%')->orderBy('descricao');
            }
        } else {
            $resultado = OcorrenciaJornada::orderBy('descricao'); // senao busca tudo
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
