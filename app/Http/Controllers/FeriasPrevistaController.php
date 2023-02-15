<?php

namespace App\Http\Controllers;

use App\Exports\planejamento\movimentacao\feriasPrevistaExport;
use App\Jobs\JobExportaExcel;
use App\Jobs\Movimentacao\FeriasPrevista\JobFeriasPrevistaAprovar;
use App\Jobs\Movimentacao\FeriasPrevista\JobFeriasPrevistaAprovarRH;
use App\Jobs\Movimentacao\FeriasPrevista\JobFeriasPrevistaStore;
use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\Curriculo;
use App\Models\FeedbackCurriculo;
use App\Models\Ferias;
use App\Models\FeriasAdquiridas;
use App\Models\FeriasPrevista;
use App\Models\FeriasPrevistaDados;
use App\Models\FeriasPrevistaMov;
use App\Models\PeriodoAquisitivo;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;
use ParagonIE\Sodium\Core\Curve25519\Fe;

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
        $dados['user_id'] = auth()->id();
        $data = new DataHora($dados['ultima_data']);
        $dados['ultima_data'] = $data->dataInsert();
        $dados['tem_faltas'] = $dados['tem_faltas'] == 'true' ? true : false;
        $dados['qnt_faltas'] = $dados['qnt_faltas'] == null ? 0 : $dados['qnt_faltas'];

        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'qnt_dias' => 'required',
                'dias_saldo' => 'required',
                'periodo_aquisitivo_id' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Férias',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            DB::beginTransaction();

            $colaborador = FeriasPrevista::whereColaboradorId($dados['colaborador_id'])->get();

            if (count($colaborador) == 0) {

                $data_admissao = FeedbackCurriculo::whereCurriculoId($dados['colaborador_id'])
                    ->with('Admissao')->first();

                $dataAdmissao = $data_admissao->Admissao->data_admissao;

                $periodo = PeriodoAquisitivo::where('id', $dados['periodo_aquisitivo_id'])->first();

                $date = new DataHora($dataAdmissao);
                $ultimoAnoPeriodoAquisitivo = $periodo->ano_final . '-' . $date->mes() . '-' . $date->dia();
                $newDate = new DataHora($ultimoAnoPeriodoAquisitivo);
                $newDate->addDia(330);
                $dados['ultima_data'] = $newDate->dataInsert();
            }

            $feriasPrevista = FeriasPrevista::create($dados);

            if (isset($dados['anexos'])) {
                foreach ($dados['anexos'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $feriasPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }

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
     * @param Ferias $ferias
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Ferias $ferias)
    {
        $ferias->autocomplete_label_colaborador = $ferias->Admissao->Feedback->Curriculo ? $ferias->Admissao->Feedback->Curriculo->nome : '';
        $ferias->autocomplete_label_colaborador_anterior = $ferias->Admissao->Feedback->Curriculo ? $ferias->Admissao->Feedback->Curriculo->nome : '';
        $ferias->colaborador_id = $ferias->Admissao->Feedback->Curriculo ? $ferias->Admissao->Feedback->Curriculo->id : '';
        $ferias->autocomplete_label_gestor_modal = $ferias->Gestor ? $ferias->Gestor->nome : '';
        $ferias->autocomplete_label_gestor_modal_anterior = $ferias->Gestor ? $ferias->Gestor->nome : '';
        $ferias->gestor_aprovacao = $ferias->GestorAprovacao ? $ferias->GestorAprovacao->nome : '';
        $ferias->centro_custo_id = $ferias->Admissao->centro_custo_id ? $ferias->Admissao->centro_custo_id : $ferias->FeriasPrevista->centro_custo_id;
        $ferias->centro_custo = $ferias->Admissao->CentroCusto ? $ferias->Admissao->CentroCusto : $ferias->FeriasPrevista->CentroCusto;
        $ferias->rh_aprovacao = $ferias->RhAprovacao ? $ferias->RhAprovacao->nome : '';
        $ferias->solicitante = $ferias->Solicitante->nome ?? '';
        $ferias->gestor_id = $ferias->gestor_id?? '';
        $data_admissao = $ferias->Admissao->data_admissao;
        $ferias->data_admissao = $data_admissao;
        $ferias->status_aprovacao_gestor = $ferias->status_aprovacao_gestor ?: '';
        $ferias->status_aprovacao_rh = $ferias->status_aprovacao_rh ?: '';
        $ferias->periodo_label = $ferias->PeriodoAquisitivo->label;
        $ferias->anexosDel = [];
        $ferias->load('Anexos');

        return response()->json($ferias, 200);
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
        }
        try {
            DB::beginTransaction();
            $feriasPrevista->update($dados);
            if (isset($dados['anexosDel'])) {
                foreach ($dados['anexosDel'] as $id_anexo) {
                    $arquivo = Arquivo::find($id_anexo);
                    $arquivo->excluir();
                }
            }

            if (isset($dados['anexos'])) {
                foreach ($dados['anexos'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $feriasPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }
            DB::commit();
            return response()->json('', 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao salvar Solicitação de Férias:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
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
        $dados = $request->input();

        try {
            DB::beginTransaction();

            $ferias = Ferias::find($dados['id']);

            $ferias->update([
                'gestor_aprovacao_id' => auth()->id(),
                'data_aprovacao_gestor' => (new DataHora())->dataHoraInsert(),
                'obs_gestor' => $dados['obs_gestor'],
                'status_aprovacao_gestor' => $dados['status_aprovacao_gestor'],
            ]);

            if (isset($dados['anexosDel'])) {
                foreach ($dados['anexosDel'] as $id_anexo) {
                    $arquivo = Arquivo::find($id_anexo);
                    $arquivo->excluir();
                }
            }

            if (isset($dados['anexos'])) {
                foreach ($dados['anexos'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $ferias->Anexos()->attach($arquivo->id);
                    }
                }
            }
            DB::commit();

            $dados_email = [
                'dados_quem_cadastrou' => [
                    'nome_de' => auth()->user()->nome,
                    'nome_para' => $ferias->Solicitante->nome,
                    'email_para' => $ferias->Solicitante->login,
                    'status_aprovacao' => $ferias->status_aprovacao_gestor,
                    'ferias_id' => $ferias->id,
                    'colaborador' => $ferias->Admissao->Feedback->Curriculo->nome,
                    'empresa_id' => auth()->user()->empresa_id,
                    'nome_empresa' => Cliente::find(auth()->user()->empresa_id)->razao_social
                ],
                'dados_gestor' => [
                    'nome_de' => auth()->user()->nome,
                    'nome_para' => $ferias->GestorAprovacao->nome,
                    'email_para' => $ferias->GestorAprovacao->login,
                    'status_aprovacao' => $ferias->status_aprovacao_gestor,
                    'ferias_id' => $ferias->id,
                    'colaborador' => $ferias->Admissao->Feedback->Curriculo->nome,
                    'empresa_id' => auth()->user()->empresa_id,
                    'nome_empresa' => Cliente::find(auth()->user()->empresa_id)->razao_social
                ]
            ];

            JobFeriasPrevistaAprovarRH::dispatch($dados_email);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar Solicitação de Férias:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    public function aprovarRH(Request $request, Ferias $ferias)
    {
        $this->authorize('privilegio_aprovar_por_rh');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            if($dados['status_aprovacao_rh'] === 'aprovado'){

                $hoje = (new DataHora())->dataInsert();
                $lista_periodos_aquisitivos = PeriodoAquisitivo::select(["id", "label"])->get();
                $periodo_aquisitivo = [];

                foreach ($lista_periodos_aquisitivos as $pa){
                    $periodo_aquisitivo[$pa->id] = $pa->label;
                }

                if($ferias->data_saida <= $hoje && $ferias->data_retorno >= $hoje){
                    $status = 'gozando';
                }

                if($ferias->data_retorno < $hoje){
                    $status = 'gozada';
                }

                if($ferias->data_saida > $hoje){
                    $status = 'aguardando';
                }

                $ferias->update([
                    'rh_aprovacao_id' => auth()->id(),
                    'status_aprovacao_rh' => $dados['status_aprovacao_rh'],
                    'obs_rh' => $dados['obs_rh'],
                    'data_aprovacao_rh' => (new DataHora())->dataHoraInsert(),
                    'status_ferias' => $status,
                    'data_status_ferias' => (new DataHora())->dataHoraInsert(),
                ]);
            }

            DB::commit();

            $dados_email = [
                'dados_quem_cadastrou' => [
                    'nome_de' => auth()->user()->nome,
                    'nome_para' => $ferias->Solicitante->nome,
                    'email_para' => $ferias->Solicitante->login,
                    'status_aprovacao' => $ferias->status_aprovacao_rh,
                    'ferias_id' => $ferias->id,
                    'colaborador' => $ferias->Admissao->Feedback->Curriculo->nome,
                    'empresa_id' => auth()->user()->empresa_id,
                    'nome_empresa' => Cliente::find(auth()->user()->empresa_id)->razao_social
                ],
                'dados_gestor' => [
                    'nome_de' => auth()->user()->nome,
                    'nome_para' => $ferias->GestorAprovacao->nome,
                    'email_para' => $ferias->GestorAprovacao->login,
                    'status_aprovacao' => $ferias->status_aprovacao_rh,
                    'ferias_id' => $ferias->id,
                    'colaborador' => $ferias->Admissao->Feedback->Curriculo->nome,
                    'empresa_id' => auth()->user()->empresa_id,
                    'nome_empresa' => Cliente::find(auth()->user()->empresa_id)->razao_social
                ]
            ];

            JobFeriasPrevistaAprovarRH::dispatch($dados_email);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar solicitação RH:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }


    public function atualizar(Request $request)
    {

        $resultado = $this->filtro($request)->paginate($request->pages);

        $periodo = PeriodoAquisitivo::whereIn('ano_inicial', [date('Y')-2, date('Y')-1, date('Y')])->get();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'periodo' => $periodo,
                'aprovar_por_gestor' => auth()->user()->can('privilegio_aprovar_por_gestor'),
                'aprovar_por_rh' => auth()->user()->can('privilegio_aprovar_por_rh'),
                'mimes' => Arquivo::MIMEAPENASIMAGENSPDF
            ]
        ]);
    }

    public function filtro(Request $request)
    {

        $resultado = Ferias::with(
            'Admissao',
            'Admissao.Feedback',
            'Admissao.Feedback.Curriculo',
            'Admissao.Feedback.VagaSelecionada',
            'Admissao.CentroCusto',
            'Empresa',
            'Solicitante',
            'GestorAprovacao',
            'Gestor',
            'RhAprovacao',
            'PeriodoAquisitivo',
            'FeriasPrevista.CentroCusto'
        );

        $filtroPeriodo = $request->filtroPeriodo == 'true' ? true : false;
        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0]);
            $dataFim = new DataHora($periodo[1]);
            $resultado->where('data_solicitacao', '>=', $dataInicio->dataInsert() . ' 00:00:00')->where('data_solicitacao', '<=', $dataFim->dataInsert() . ' 23:59:59');
        }

        $filtroVencimento = $request->filtroVencimento == 'true' ? true : false;
        if ($filtroVencimento) {
            $periodo = explode(' até ', $request->vencimento);
            $dataInicio = new DataHora($periodo[0]);
            $dataFim = new DataHora($periodo[1]);
            $resultado->where('ultima_data', '>=', $dataInicio->dataInsert())->where('ultima_data', '<=', $dataFim->dataInsert());
        }

        $filtroInicioFerias = $request->filtroInicioFerias == 'true' ? true : false;
        if ($filtroInicioFerias) {
            $periodo = explode(' até ', $request->inicioFerias);
            $dataInicio = new DataHora($periodo[0]);
            $dataFim = new DataHora($periodo[1]);
            $resultado->where('data_saida', '>=', $dataInicio->dataInsert())->where('data_saida', '<=', $dataFim->dataInsert());
        }

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Admissao.Feedback.Curriculo', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoStatusAprovacao')) {
            $status = $request->campoStatusAprovacao;
            if ($request->campoStatusAprovacao == "aberto"){
                $resultado->whereNull('status_aprovacao_gestor');
            }
            elseif ($request->campoStatusAprovacao == "aprovado_gestor"){
                $resultado->where('status_aprovacao_gestor',Ferias::STATUS_APROVADO)->whereNull('status_aprovacao_rh');
            }elseif ($request->campoStatusAprovacao == "aprovado_rh"){
                $resultado->where('status_aprovacao_rh', Ferias::STATUS_APROVADO);
            }else{
                $resultado->whereStatusAprovacaoGestor(Ferias::STATUS_REPROVADO)->orWhere('status_aprovacao_rh', Ferias::STATUS_REPROVADO);
            }
        }

        if (!auth()->user()->can('privilegio_gestao_rh')) {
            $resultado->whereSolicitanteId(auth()->user()->id)->orWhere('gestor_id', auth()->user()->id);
        }

//        $resultado->whereIn('status_ferias', [Ferias::STATUS_AGUARDANDO, Ferias::STATUS_GOZANDO, Ferias::STATUS_CANCELADA, null]);

        if ($request->filled('filtroPeriodoAquisitivo')) {
            $resultado->whereHas('PeriodoAquisitivo', function ($q) use ($request) {
                $q->where('id', $request->filtroPeriodoAquisitivo);
            });
        }else{
            $resultado->whereHas('PeriodoAquisitivo', function ($q) use ($request) {
                $q->whereIn('ano_inicial', [date('Y')-2, date('Y')-1, date('Y')]);
            });
        }

//        $resultado->whereHas('PeriodoAquisitivo', function ($q) use ($request) {
//            $q->whereIn('ano_inicial', [date('Y')-2, date('Y')-1, date('Y')]);
//        });

        return $resultado->orderBy('data_solicitacao');
    }

    public function buscaDataAdmissao(Request $request)
    {
        $ferias = Ferias::find($request->ferias_id);
        $dataAdmissao = $ferias->Admissao->data_admissao;

        $colaboradorPeriodo = $ferias->PeriodoAquisitivo;
        $dataHoje = new DataHora();
        $data_saida = $dataHoje->dataCompleta();
        $data_retorno = $dataHoje->dataCompleta();

        if ($colaboradorPeriodo !== null && !$request->visualizar) {

            $periodo = PeriodoAquisitivo::where('id', '>', $colaboradorPeriodo->id)->first();

            $date = new DataHora($dataAdmissao);
            $ultimoAnoPeriodoAquisitivo = $periodo->ano_final . '-' . $date->mes() . '-' . $date->dia();
            $data_saida = $date->dia() . '/' . $date->mes() . '/' . $periodo['ano_inicial'];
            $data_retorno = $date->dia() . '/' . $date->mes() . '/' . $periodo['ano_inicial'];
            $newDate = new DataHora($ultimoAnoPeriodoAquisitivo);
            $ultimaData = $newDate->addDia(330);

        } elseif ($colaboradorPeriodo !== null && $request->visualizar) {
            $periodo = $colaboradorPeriodo;
            $data_saida = $ferias->data_saida;
            $data_retorno = $ferias->data_retorno;
            $ultimaData = $ferias->ultima_data;
            $newDate = new DataHora($ultimaData);
            $ultimaData = $newDate->dataCompleta();
        } else {
            $periodo = PeriodoAquisitivo::all();
            $ultimaData = '';
        }

        return response()->json([
            'data_admissao' => $dataAdmissao,
            'periodo' => $periodo,
            'ultimaData' => $ultimaData,
            'data_saida' => $data_saida,
            'data_retorno' => $data_retorno,
        ]);
    }

    public function buscaPeriodosAquisitivos(Request $request)
    {

        $periodo = PeriodoAquisitivo::all();

        return response()->json(['periodos' => $periodo]);
    }

    public function atualizacaoStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            foreach ($request->selecionados[0] as $selecionado) {

                $feriasPrevista = FeriasPrevista::find($selecionado);

                if ($request->status_aprovacao === 'reprovado') {
                    $feriasPrevista->update([
                        'user_aprovacao_id' => auth()->id(),
                        'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                        'obs_aprovacao' => $request->obs_aprovacao,
                        'status_aprovacao' => $request->status_aprovacao,
                    ]);
                } else {
                    $feriasPrevista->update([
                        'user_aprovacao_id' => auth()->id(),
                        'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                        'obs_aprovacao' => $request->obs_aprovacao,
                        'status_aprovacao' => $request->status_aprovacao,
                    ]);
                }
                DB::commit();
            }
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar Solicitação de Férias:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    //Excel
    public function export(Request $request)
    {

        $resultado = $this->filtro($request)->get();

        $head = [
            'Nome',
            'Cargo',
            'Data da Admissão',
            'Centro de Custo',
            'Período Aquisitivo',
            'Última Data',
            'Quantidade de Faltas',
            'Data Saída',
            'Data Retorno',
            'Quantidade de Dias',
            'Saldo de Dias',
            'Quem Cadastrou',
            'Gestor Indicado',
            'Gestor Aprovação',
            'Data da Aprovação',
            'Status',
            'RH Aprovação',
            'Data da Aprovação RH',
            'Resposta RH',
        ];

        $rows = [];

        foreach ($resultado as $row) {
            $rows[] = array(
                $row->Admissao->Feedback->Curriculo->nome,
                $row->Admissao->FeedBack->VagaSelecionada->nome,
                $row->Admissao->data_admissao,
                $row->FeriasPrevista->CentroCusto->label,
                $row->PeriodoAquisitivo->label,
                $row->ultima_data,
                (string)$row->qnt_faltas,
                $row->data_saida,
                $row->data_retorno,
                (string)$row->qnt_dias,
                (string)$row->dias_saldo,
                $row->Solicitante->nome,
                $row->Gestor->nome,
                $row->GestorAprovacao ? $row->GestorAprovacao->nome : '',
                $row->status_aprovacao_gestor ? $row->data_aprovacao_gestor : '',
                $row->status_aprovacao_gestor,
                $row->status_aprovacao_rh ? $row->RhAprovacao->nome : '',
                $row->status_aprovacao_rh ? (new DataHora())->dataHoraCompleta($row->data_aprovacao_rh) : '',
                $row->status_aprovacao_rh,
            );
        }

        $nameArquivo = "ferias_" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Ferias", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);

    }
}
