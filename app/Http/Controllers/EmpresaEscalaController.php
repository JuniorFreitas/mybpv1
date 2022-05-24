<?php

namespace App\Http\Controllers;

use App\Models\EmpresaEscala;
use App\Models\OcorrenciaJornada;
use App\Models\Sistema;
use App\Models\User;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class EmpresaEscalaController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('g.controle-ponto.escalas.index');
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
        $this->authorize('controle_ponto_escalas_insert');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'descricao' => 'required|min:3',
            'inicio' => 'required|min:10',
            'jornadas' => 'required|array|min:1',
        ]);


        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao salvar a escala',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                \DB::beginTransaction();

                $escala = EmpresaEscala::create([
                    'descricao'=>$request->descricao,
                    'inicio'=>(new DataHora($request->inicio))->dataInsert(),
                ]); //model raiz

                foreach ($request->jornadas as $jornada){
                    $modelJornada = $escala->Jornadas()->create($jornada);
                    //periodos
                    if(isset($jornada['periodos']) && $jornada['ocorrencia_id']!=OcorrenciaJornada::FOLGA){
                        foreach ($jornada['periodos'] as $periodo){
                            $modelJornada->Periodos()->create($periodo);
                        }
                    }
                }

                \DB::commit();

                return response()->json($escala, 201);

            }catch (\Exception $e){
                \DB::rollBack();
                return response()->json(['msg' => $e->getMessage()], 400);
            }


            return response()->json([], 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\EmpresaEscala $escala
     * @return \Illuminate\Http\Response
     */
    public function show(EmpresaEscala $escala) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\EmpresaEscala $escala
     * @return \Illuminate\Http\Response
     */
    public function edit(EmpresaEscala $escala) {
        $escala->load([
            'Jornadas.Ocorrencia:id,descricao',
            'Jornadas.Periodos',
        ]);
        return $escala;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\EmpresaEscala $escala
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EmpresaEscala $escala) {
        $this->authorize('controle_ponto_escalas_update');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'descricao' => 'required|min:3',
            'inicio' => 'required|min:10',
            'jornadas' => 'required|array|min:1',
        ]);


        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao salvar a escala',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                \DB::beginTransaction();

                $escala->update([
                    'descricao'=>$request->descricao,
                    'inicio'=>(new DataHora($request->inicio))->dataInsert(),
                ]); //model raiz

                // delete Jornadas..
                $escala->Jornadas()->whereIn('id',$request->jornadasDelete)->delete();

                foreach ($request->jornadas as $jornada){
                    if($jornada['id']==null){
                        $modelJornada = $escala->Jornadas()->create($jornada);
                        //periodos
                        if(isset($jornada['periodos']) && $jornada['ocorrencia_id']!=OcorrenciaJornada::FOLGA){
                            foreach ($jornada['periodos'] as $periodo){
                                $modelJornada->Periodos()->create($periodo);
                            }
                        }

                    }else{
                        $modelJornada = $escala->Jornadas()->where('id',$jornada['id'])->first();

                        //delete Periodos... (se tiver nessa jornada)
                        $modelJornada->Periodos()->whereIn('id',$request->periodosDelete)->delete();

                        $modelJornada->update($jornada);
                        if($modelJornada->ocorrencia_id==OcorrenciaJornada::FOLGA){ // se é folga, apagar todos os periodos
                            $modelJornada->Periodos()->delete();
                        }
                        //periodos
                        if(isset($jornada['periodos']) && $modelJornada->ocorrencia_id!=OcorrenciaJornada::FOLGA){
                            foreach ($jornada['periodos'] as $periodo){
                                if($periodo['id']==null){
                                    $modelJornada->Periodos()->create($periodo);
                                }else{

                                    $modelPeriodo = $modelJornada->Periodos()->where('id',$periodo['id'])->first();
                                    if($modelPeriodo){
                                        $modelPeriodo->update($periodo);
                                    }

                                }
                            }
                        }

                    }

                }



                \DB::commit();

                return response()->json($escala, 201);

            }catch (\Exception $e){
                \DB::rollBack();
                return response()->json(['msg' => $e->getMessage()], 400);
            }


            return response()->json([], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\EmpresaEscala $escala
     * @return \Illuminate\Http\Response
     */
    public function destroy(EmpresaEscala $escala) {
        $this->authorize('controle_ponto_escalas_delete');
        $escala->delete();
        return response()->json([],200);
    }

    public function getPermissoes(Request $request) {

        return response()->json([
            'escalas_insert' => auth()->user()->can('escalas_insert'),
            'escalas_update' => auth()->user()->can('escalas_update'),
            'escalas_delete' => auth()->user()->can('escalas_delete'),
            'escalas_funcionarios' => auth()->user()->can('escalas_funcionarios'),
            'ocorrencias_jornadas' => OcorrenciaJornada::withoutGlobalScopes()->whereAtivo(true)->whereEmpresaId(null)->whereIn('id',OcorrenciaJornada::Fixas())->get(['id','descricao','trabalhado']),
            'ocorrencia_id_padrao' => OcorrenciaJornada::DIA_TRABALHADO
        ]);
    }

    public function atualizarFuncionarios(Request $request) {

        $resultado = auth()->user()->Empresa->EmpresaFuncionarios();
        $porPagina = $request->get('porPagina');
        $busca = false;
        if ($request->filled('campoBusca')) {
            $busca = $request->get('campoBusca');
            $resultado = $resultado->where('nome', 'like', '%' . $busca . '%');
        } else {
            $resultado = $resultado->orderBy('nome'); // senao busca tudo
        }
        $resultado->with([
            'Empresa:id,nome',
            'EscalasFuncionario:id,descricao'
        ]);

        $resultado = $resultado->paginate($porPagina);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => $resultado->items(),
        ]);
    }

    public function assosicarEscalas(Request $request) {

        $dados = $request->input();
        $dadosValidados = \Validator::make($dados, [
            'escala_id' => 'required|min:1',
            'funcionariosSelecionados' => 'required|array|min:1',
        ]);


        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao associar escalas',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            foreach ($request->funcionariosSelecionados as $funcnionario_id) {
                $user = User::find($funcnionario_id);
                if ($request->escala_id > 0) {
                    $user->EscalasFuncionario()->sync($request->escala_id);
                } else {
                    $user->EscalasFuncionario()->detach();
                }
            }


            return response()->json([], 200);
        }
    }

    public function atualizarEscalas(Request $request) {

        $resultado = auth()->user()->EmpresaEscalas();


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
