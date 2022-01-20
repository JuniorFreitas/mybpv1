<?php

namespace App\Http\Controllers;

use App\Exports\planejamento\movimentacao\feriasPrevistaExport;
use App\Jobs\Movimentacao\FeriasPrevista\JobFeriasPrevistaAprovar;
use App\Jobs\Movimentacao\FeriasPrevista\JobFeriasPrevistaAprovarRH;
use App\Jobs\Movimentacao\FeriasPrevista\JobFeriasPrevistaStore;
use App\Models\Curriculo;
use App\Models\FeedbackCurriculo;
use App\Models\FeriasPrevista;
use App\Models\FeriasPrevistaDados;
use App\Models\FeriasPrevistaMov;
use App\Models\PeriodoAquisitivo;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class FeriasPrevistaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $dados = $request->input();
        $dados['solicitante_id'] = auth()->id();
        $data = new DataHora($dados['ultima_data']);
        $dados['ultima_data'] = $data->dataInsert();
        $dados['tem_faltas'] = $dados['tem_faltas'] == 'true' ? true : false;

        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'qnt_dias' => 'required',
                'dias_saldo' => 'required',
                'periodo_aquisitivo_id' => 'required',
                'ultima_data' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Férias',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                $colaborador = FeriasPrevistaMov::whereColaboradorId($dados['colaborador_id'])->first();

                if (isset($colaborador->id)) {
                    $colaborador->update([
                        'ultimo_periodo_aquisitivo_id' => $dados['periodo_aquisitivo_id'],
                        'ultima_data' => $dados['ultima_data'],
                        'dias_saldo' => $dados['dias_saldo'],
                    ]);

                    $dados['ferias_prevista_id'] = $colaborador->id;

                } else {
                    $dadosColaborador = [
                        'colaborador_id' => $dados['colaborador_id'],
                        'ultimo_periodo_aquisitivo_id' => $dados['periodo_aquisitivo_id'],
                        'ultima_data' => $dados['ultima_data'],
                        'dias_saldo' => $dados['dias_saldo'],
                    ];
                    $colaboradorNovo = FeriasPrevistaMov::create($dadosColaborador)->id;
                    $dados['ferias_prevista_id'] = $colaboradorNovo;
                }

                FeriasPrevistaDados::create($dados);

                DB::commit();
//                JobFeriasPrevistaStore::dispatch($feriasPrevista);
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Solicitação de Férias:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\FeriasPrevista $feriasPrevista
     * @return \Illuminate\Http\Response
     */
    public function show(FeriasPrevista $feriasPrevista)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\FeriasPrevistaMov $feriasPrevista
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function edit(FeriasPrevistaMov $feriasPrevista)
    {

        $feriasPrevista->autocomplete_label_colaborador = $feriasPrevista->Colaborador ? $feriasPrevista->Colaborador->nome : '';
        $feriasPrevista->autocomplete_label_colaborador_anterior = $feriasPrevista->Colaborador ? $feriasPrevista->Colaborador->nome : '';

        $feriasPrevista->autocomplete_label_gestor_modal = $feriasPrevista->FeriasPrevistaDadosUltimo->GestorAprovacao ? $feriasPrevista->FeriasPrevistaDadosUltimo->GestorAprovacao->nome : '';
        $feriasPrevista->autocomplete_label_gestor_modal_anterior = $feriasPrevista->FeriasPrevistaDadosUltimo->GestorAprovacao ? $feriasPrevista->FeriasPrevistaDadosUltimo->GestorAprovacao->nome : '';

        $feriasPrevista->quem_aprovou = $feriasPrevista->FeriasPrevistaDadosUltimo->QuemAprovou ?? '';
        $feriasPrevista->rh_aprovacao = $feriasPrevista->FeriasPrevistaDadosUltimo->RhAprovacao ?? '';

        $data_admissao = FeedbackCurriculo::whereCurriculoId($feriasPrevista->colaborador_id)
            ->with('Admissao')->first();

        $feriasPrevista->data_admissao = $data_admissao->Admissao->data_admissao;

        return response()->json($feriasPrevista, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\FeriasPrevista $feriasPrevista
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, FeriasPrevista $feriasPrevista)
    {
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'qnt_dias' => 'required',
                'dias_saldo' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Férias',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $feriasPrevista->update($dados);
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Solicitação de Férias:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\FeriasPrevista $feriasPrevista
     * @return \Illuminate\Http\Response
     */
    public function destroy(FeriasPrevista $feriasPrevista)
    {
        //
    }


    public function aprovarGestor(Request $request)
    {
//        $this->authorize('aprovar_por _gestor');
        $dados = $request->input();

//        dd($dados);

        try {
            DB::beginTransaction();

            $feriasPrevista = FeriasPrevistaDados::find($dados['ferias_prevista_dados_ultimo']['id']);

            if ($dados['status_aprovacao'] === 'reprovado') {
                $feriasPrevista->update([
                    'user_aprovacao_id' => auth()->id(),
                    'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                    'obs_aprovacao' => $dados['obs_aprovacao'],
                    'status_aprovacao' => $dados['status_aprovacao'],
                ]);
            } else {
                $feriasPrevista->update([
                    'user_aprovacao_id' => auth()->id(),
                    'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                    'obs_aprovacao' => $dados['obs_aprovacao'],
                    'status_aprovacao' => $dados['status_aprovacao'],
                ]);
            }
            DB::commit();

//            JobFeriasPrevistaAprovar::dispatch($feriasPrevista);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar Solicitação de Férias:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
//            return response()->json(['msg' => $msg], 400);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }


    public function atualizar(Request $request)
    {

        $resultado = $this->filtro($request)->paginate($request->pages);

        $periodo = PeriodoAquisitivo::all();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'periodo' => $periodo,
                'aprovar_por_gestor' => auth()->user()->can('aprovar_por_gestor'),
            ]
        ]);
    }

    public function filtro(Request $request)
    {

        $resultado = FeriasPrevistaMov::with(
            'Colaborador',
            'FeriasPrevistaDadosUltimo',
            'FeriasPrevistaDadosUltimo.CentroCusto',
            'FeriasPrevistaDadosUltimo.UserCadastrou',
            'FeriasPrevistaDadosUltimo.QuemAprovou',
        );

        $filtroPeriodo = $request->filtroPeriodo == 'true' ? true : false;
        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0]);
            $dataFim = new DataHora($periodo[1]);
            $resultado->where('created_at', '>=', $dataInicio->dataInsert() . ' 00:00:00')->where('created_at', '<=', $dataFim->dataInsert() . ' 23:59:59');
        }

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Colaborador', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == "aberto" ? null : $request->campoStatus;
            $resultado->whereRespostaRh($status);
        }

        if (!auth()->user()->can('gestao_rh')) {
            $resultado->whereUserId(auth()->user()->id)->orWhere('gestor_id', auth()->user()->id);
        }

        return $resultado->orderByDesc('created_at');
    }

    public function export(Request $request)
    {

        $resultado = FeriasPrevista::with(
            'CentroCusto',
            'QuemAprovou:id,nome',
            'UserCadastrou:id,nome',
            'GestorAprovacao:id,nome',
            'Colaborador', 'RhAprovacao')->orderByDesc('created_at')->get();

        return \Excel::download(new feriasPrevistaExport($resultado), 'ferias_prevista.xlsx');
    }

    public function buscaDataAdmissao(Request $request)
    {
        $data_admissao = FeedbackCurriculo::whereCurriculoId($request->colaborador_id)
            ->with('Admissao')->first();
        $dataAdmissao = $data_admissao->Admissao->data_admissao;

        $colaboradorPeriodo = FeriasPrevistaMov::whereColaboradorId($request->colaborador_id)->first();

        if ($colaboradorPeriodo !== null && !$request->visualizar) {

            $periodo = PeriodoAquisitivo::where('id', '>', $colaboradorPeriodo->ultimo_periodo_aquisitivo_id)->orderBy('asc')->limit(1)->get();

            $date = new DataHora($dataAdmissao);
            $ultimoAnoPeriodoAquisitivo = $periodo[0]['ano_final'] . '-' . $date->mes() . '-' . $date->dia();
            $newDate = new DataHora($ultimoAnoPeriodoAquisitivo);
            $ultimaData = $newDate->addDia(330);

        } elseif ($colaboradorPeriodo !== null && $request->visualizar) {
            $periodo = $colaboradorPeriodo->PeriodoAquisitivo;
            $ultimaData = $colaboradorPeriodo->ultima_data;
            $newDate = new DataHora($ultimaData);
            $ultimaData = $newDate->dataCompleta();
        } else {
            $periodo = PeriodoAquisitivo::all();
            $ultimaData = '';
        }

        return response()->json([
            'data_admissao' => $data_admissao->Admissao->data_admissao,
            'periodo' => $periodo,
            'ultimaData' => $ultimaData,
        ]);
    }

    public function buscaPeriodosAquisitivos(Request $request)
    {

        $periodo = PeriodoAquisitivo::all();

        return response()->json(['periodos' => $periodo]);
    }
}
