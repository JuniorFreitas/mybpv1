<?php

namespace App\Http\Controllers;

use App\Models\OcorrenciaJornada;
use App\Models\PontoEletronico;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class AjusteJornadaController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        return view('g.controle-ponto.ajuste-jornadas.index');
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, PontoEletronico $ponto) {
        $ponto->load([
            'Funcionario:id,nome',
            'Periodos'=> function($q){
                $q->orderByDesc('created_at')->select(['id','ponto_id','autenticacao_entrada','entrada','saida']);
            },
        ]);
        $ponto->Jornada->Escala->load([
            'Jornadas.Ocorrencia:id,descricao',
            'Jornadas.Periodos',
        ]);

        return response()->json([
            'ponto'=>$ponto,
            'ocorrencias_jornadas' => OcorrenciaJornada::withoutGlobalScopes()->whereAtivo(true)->whereEmpresaId(null)->get(['id','descricao','trabalhado'])->concat(OcorrenciaJornada::get(['id','descricao','trabalhado'])),
            'ocorrencia_id_padrao' => OcorrenciaJornada::DIA_TRABALHADO,
            'jornada_atual' => PontoEletronico::getJornadaAtual($ponto->Jornada->Escala->id,$ponto->dia),
        ],200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PontoEletronico $ponto) {
        //primeiro verificar se pode alterar uma jornada que ainda esta em andamento hoje
        $hoje = new DataHora();
        $pontoDeHoje = $hoje->dataCompleta() === $ponto->dia;
        /*if($ponto->Periodos()->whereNull('saida')->count() > 0 && $pontoDeHoje){
            return response()->json(['msg' => 'Jornada em andamento.'], 400);
        }*/
        if($pontoDeHoje){
            return response()->json(['msg' => 'Pontos gerados hoje não podem ser modificados.'], 400);
        }
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados, [
            'periodos' => 'required',
            'ocorrencia_id'=> 'required|numeric',
            'periodos.*.entrada' => 'required|min:5',
            'periodos.*.saida' => 'required|min:5',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao registrar o ponto',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {

                \DB::beginTransaction();

                $ponto->justificativa = $request->justificativa;
                $ponto->ocorrencia_id = $request->ocorrencia_id;
                $ponto->verificado = $request->verificado;
                $ponto->save();
                //Apagar periodos
                if($request->filled('periodosDelete')){
                    foreach ($request->periodosDelete as $periodos_id){

                        $periodo = $ponto->Periodos()->where('id',$periodos_id)->first();
                        $periodo->FotoEntrada->excluir();
                        $periodo->FotoSaida->excluir();
                        $periodo->delete();
                    }

                }
                if($request->filled('periodos')){

                    foreach ($request->periodos as $periodo){

                        if(intval($periodo['id'])>0){
                            $entrada =  explode(' às ',$periodo['entradaCompleto']);
                            $entrada = new DataHora($entrada[0].' '.$entrada[1]);
                            $saida =  explode(' às ',$periodo['saidaCompleto']);
                            $saida = new DataHora($saida[0].' '.$saida[1]);
                            $duracao = DataHora::diferencaMinutos($entrada->dataHoraInsert(),$saida->dataHoraInsert());
                            $ponto->Periodos()->where('id',$periodo['id'])->update([
                                'entrada'=>$entrada->dataHoraInsert(),
                                'saida'=>$saida->dataHoraInsert(),
                                'minutos'=> $duracao
                            ]);
                        }else{
                            $entrada =  explode(' às ',$periodo['entradaCompleto']);
                            $entrada = new DataHora($entrada[0].' '.$entrada[1]);
                            $saida =  explode(' às ',$periodo['saidaCompleto']);
                            $saida = new DataHora($saida[0].' '.$saida[1]);
                            $duracao = DataHora::diferencaMinutos($entrada->dataHoraInsert(),$saida->dataHoraInsert());
                            $ponto->Periodos()->where('id',$periodo['id'])->create([
                                'entrada'=>$entrada->dataHoraInsert(),
                                'facial_entrada'=>false,
                                'saida'=>$saida->dataHoraInsert(),
                                'facial_saida'=>false,
                                'minutos'=> $duracao
                            ]);
                        }
                    }
                }else{
                    throw new \Exception('O ponto eletrônico está sem periodos!. Verificar os periodos');
                }
                $ponto->recalcularDuracoes();

                \DB::commit();
                return response()->json($ponto, 201);

            }catch (\Exception $exception) {
                \DB::rollBack();

                return response()->json(['msg' => $exception->getMessage()], 500);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        //
    }

    public function atualizaJornadasPendentes(Request $request){
        $hoje =  new DataHora();
        $porPagina = $request->get('porPagina');


        $intervalo = explode(" até ",$request->intervalo);
        $inicio = new DataHora($intervalo[0]." 00:00:00");
        $fim = new DataHora($intervalo[1]." 23:59:59");


        $resultado = PontoEletronico::whereHas('Ocorrencia',function($q){
            $q->whereTrabalhado(true);
        })
        ->whereVerificado(false)
        /*->where(function($q){
            $q->whereHas('Periodos',function($q){
                $q->select(\DB::raw('sum(minutos) as duracao'))
                    ->whereNotNull('entrada')->whereNotNull('saida');
                $q->havingRaw('duracao >= ponto_eletronicos.duracao + ponto_eletronicos.limite_tolerancia');
            })
                ->orWhereHas('Periodos',function($q) {
                    $q->select(\DB::raw('sum(minutos) as duracao'))
                        ->whereNotNull('entrada')->whereNotNull('saida');
                    $q->havingRaw('duracao <= ponto_eletronicos.duracao - ponto_eletronicos.limite_tolerancia');
                });
        })*/
        ->whereBetween('created_at',[$inicio->dataHoraInsert(),$fim->dataHoraInsert()])
        ->whereDate('created_at','<',$hoje->dataInsert());
        //pode ter jornadas incompletas ainda hoje que dever aguardar terminar

        if ($request->filled('funcionario_id')) {
            $resultado->whereFuncionarioId($request->funcionario_id);
        }
        $resultado->orderByDesc('created_at');
        $resultado->with([
            'PeriodosEmAberto:id,ponto_id',
            'Funcionario:id,nome',
            'Ocorrencia:id,descricao,trabalhado,conta_horas',
            'Periodos' => function ($q) {
                $q->orderByDesc('created_at')
                    //->whereNotNull('entrada')
                    //->whereNotNull('saida')
                    ->select(['id', 'ponto_id', 'entrada', 'saida']);
            },
        ]);

        $resultado = $resultado->paginate($porPagina);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => $resultado->items(),
        ]);
    }

    public function atualizaJornadasIncompletas(Request $request){
        $hoje =  new DataHora();
        $porPagina = $request->get('porPagina');
        $intervalo = explode(" até ",$request->intervalo);
        $inicio = new DataHora($intervalo[0]." 00:00:00");
        $fim = new DataHora($intervalo[1]." 23:59:59");

        //Jornada de hoje
        $resultado = PontoEletronico::whereHas('Ocorrencia',function($q){
            $q->whereTrabalhado(true);
        })
        ->whereVerificado(false)
        /*->whereHas('Periodos',function($q){
            $q ->whereNotNull('entrada')->whereNull('saida');
        })*/
        /*->where(function($q){
            $q->whereHas('Periodos',function($q){
                $q->select(\DB::raw('sum(minutos) as duracao'))
                    ->whereNotNull('entrada')->whereNotNull('saida');
                $q->havingRaw('duracao >= ponto_eletronicos.duracao + ponto_eletronicos.limite_tolerancia');
            })
            ->orWhereHas('Periodos',function($q) {
                $q->select(\DB::raw('sum(minutos) as duracao'))
                    ->whereNotNull('entrada')->whereNotNull('saida');
                $q->havingRaw('duracao <= ponto_eletronicos.duracao - ponto_eletronicos.limite_tolerancia');
            })
            ->orWhereHas('Periodos',function($q){
                $q ->whereNotNull('entrada')->whereNull('saida');
            });
        })*/

        ->whereBetween('created_at',[$inicio->dataHoraInsert(),$fim->dataHoraInsert()])
        ->whereDate('created_at','=',$hoje->dataInsert());

        if ($request->filled('funcionario_id')) {
            $resultado->whereFuncionarioId($request->funcionario_id);
        }
        $resultado->orderByDesc('created_at');
        $resultado->with([
            'PeriodosEmAberto:id,ponto_id',
            'Funcionario:id,nome',
            'Ocorrencia:id,descricao,trabalhado,conta_horas',
            'Periodos' => function ($q) {
                $q->orderByDesc('created_at')->select(['id', 'ponto_id', 'entrada', 'saida']);
            },
        ]);

        $resultado = $resultado->paginate($porPagina);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => $resultado->items(),
        ]);
    }

    public function atualizaJornadasVerificadas(Request $request){
        $hoje =  new DataHora();
        $porPagina = $request->get('porPagina');
        $intervalo = explode(" até ",$request->intervalo);
        $inicio = new DataHora($intervalo[0]." 00:00:00");
        $fim = new DataHora($intervalo[1]." 23:59:59");

        //Pontos eletronicos, ou com correto dentro dos limites de horarios, ou com ocorrencias "nao trabalhadas"
        $resultado = PontoEletronico::where(function($q){
            /*$q->where(function($q){
                $q->whereHas('Periodos',function($q){
                    $q->select(\DB::raw('sum(minutos) as duracao'))
                        ->whereNotNull('entrada')->whereNotNull('saida');
                    $q->havingRaw('duracao <= ponto_eletronicos.duracao + ponto_eletronicos.limite_tolerancia');
                })
                    ->WhereHas('Periodos',function($q) {
                        $q->select(\DB::raw('sum(minutos) as duracao'))
                            ->whereNotNull('entrada')->whereNotNull('saida');
                        $q->havingRaw('duracao >= ponto_eletronicos.duracao - ponto_eletronicos.limite_tolerancia');
                    });
            })->orWhereHas('Ocorrencia',function($q){
                $q->whereTrabalhado(false);
            });*/
        })
        ->whereBetween('created_at',[$inicio->dataHoraInsert(),$fim->dataHoraInsert()])
        ->whereDate('created_at','<',$hoje->dataInsert())
        ->whereVerificado(true);

        if ($request->filled('funcionario_id')) {
            $resultado->whereFuncionarioId($request->funcionario_id);
        }
        $resultado->orderByDesc('created_at');
        $resultado->with([
            'Funcionario:id,nome',
            'Ocorrencia:id,descricao,trabalhado,conta_horas',
            'Periodos' => function ($q) {
                $q->orderByDesc('created_at')->select(['id', 'ponto_id', 'entrada', 'saida']);
            },
        ]);

        $resultado = $resultado->paginate($porPagina);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => $resultado->items(),
        ]);
    }
}
