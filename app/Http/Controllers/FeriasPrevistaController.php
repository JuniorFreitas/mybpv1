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
use App\Models\FeriasAdquiridas;
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
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\FeriasPrevistaMov $feriasPrevista
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function edit(FeriasPrevista $feriasPrevista)
    {

        $feriasPrevista->autocomplete_label_colaborador = $feriasPrevista->Colaborador ? $feriasPrevista->Colaborador->nome : '';
        $feriasPrevista->autocomplete_label_colaborador_anterior = $feriasPrevista->Colaborador ? $feriasPrevista->Colaborador->nome : '';

        $feriasPrevista->autocomplete_label_gestor_modal = $feriasPrevista->GestorAprovacao ? $feriasPrevista->GestorAprovacao->nome : '';
        $feriasPrevista->autocomplete_label_gestor_modal_anterior = $feriasPrevista->GestorAprovacao ? $feriasPrevista->GestorAprovacao->nome : '';

        $feriasPrevista->quem_aprovou = $feriasPrevista->QuemAprovou ?? '';

        $feriasPrevista->rh_aprovacao = $feriasPrevista->RhAprovacao ?? '';

        $feriasPrevista->solicitante_nome = $feriasPrevista->UserCadastrou->nome ?? '';

        $data_admissao = FeedbackCurriculo::whereCurriculoId($feriasPrevista->colaborador_id)
            ->with('Admissao')->first();

        $feriasPrevista->data_admissao = $data_admissao->Admissao->data_admissao;

        $feriasPrevista->periodo_label = $feriasPrevista->PeriodoAquisitivo->label;
        $feriasPrevista->anexosDel = [];
        $feriasPrevista->load('Anexos');

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

            $feriasPrevista = FeriasPrevista::find($dados['id']);

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

//            JobFeriasPrevistaAprovar::dispatch($feriasPrevista);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar Solicitação de Férias:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    public function aprovarRH(Request $request, FeriasPrevista $feriasPrevista)
    {
        $this->authorize('privilegio_aprovar_por_rh');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $feriasPrevista->update([
                'user_rh_id' => auth()->id(),
                'resposta_rh' => $dados['resposta_rh'],
                'obs_rh' => $dados['obs_rh'],
                'data_aprovacao_rh' => (new DataHora())->dataHoraInsert(),
            ]);

            if($dados['resposta_rh'] === 'aprovado'){

                $hoje = (new DataHora())->dataInsert();
                $lista_periodos_aquisitivos = PeriodoAquisitivo::select(["id", "label"])->get();
                $periodo_aquisitivo = [];

                foreach ($lista_periodos_aquisitivos as $pa){
                    $periodo_aquisitivo[$pa->id] = $pa->label;
                }

                if($feriasPrevista->data_saida <= $hoje && $feriasPrevista->data_retorno >= $hoje){
                    $status = 'gozando';
                }

                if($feriasPrevista->data_retorno < $hoje){
                    $status = 'gozada';
                }

                if($feriasPrevista->data_saida > $hoje){
                    $status = 'aguardando';
                }

                $data_limite = (new DataHora($feriasPrevista->data_retorno))->addAno(1);

                $proximo_periodo = $feriasPrevista->periodo_aquisitivo_id + 1;

                $ferias_adquiridas = [
                    'admissao_id' => $feriasPrevista->Feedback->Admissao->id,
                    'periodo_gozado' => $feriasPrevista->PeriodoAquisitivo->label,
                    'qnt_dias' => $feriasPrevista->qnt_dias,
                    'data_saida' => (new DataHora($feriasPrevista->data_saida))->dataInsert(),
                    'data_retorno' => (new DataHora($feriasPrevista->data_retorno))->dataInsert(),
                    'proximo_periodo' => $periodo_aquisitivo[$proximo_periodo],
                    'data_limite' => (new DataHora($data_limite))->dataInsert(),
                    'user_cadastrou_id' => auth()->id(),
                    'created_at' => (new DataHora())->dataHoraInsert(),
                    'status' => $status,
                    'ferias_prevista_id' => $feriasPrevista->id
                ];

                $feriasAdquiridas = FeriasAdquiridas::where('admissao_id', $ferias_adquiridas['admissao_id'])
                    ->where('periodo_gozado', $ferias_adquiridas['periodo_gozado'])
                    ->where('data_saida', $ferias_adquiridas['data_saida'])
                    ->count();

                if($feriasAdquiridas == 0){
                    $ferias = FeriasAdquiridas::insert($ferias_adquiridas);
                }
            }

            DB::commit();

            $dados_email = [
                'dados_quem_cadastrou' => [
                    'nome_de' => auth()->user()->nome,
                    'nome_para' => $feriasPrevista->UserCadastrou->nome,
                    'email_para' => $feriasPrevista->UserCadastrou->login,
                    'status_aprovacao' => $feriasPrevista->resposta_rh,
                    'ferias_id' => $feriasPrevista->id,
                    'colaborador' => $feriasPrevista->Colaborador->nome,
                    'empresa_id' => auth()->user()->empresa_id,
                    'nome_empresa' => Cliente::find(auth()->user()->empresa_id)->razao_social
                ],
                'dados_gestor' => [
                    'nome_de' => auth()->user()->nome,
                    'nome_para' => $feriasPrevista->QuemAprovou->nome,
                    'email_para' => $feriasPrevista->QuemAprovou->login,
                    'status_aprovacao' => $feriasPrevista->resposta_rh,
                    'ferias_id' => $feriasPrevista->id,
                    'colaborador' => $feriasPrevista->Colaborador->nome,
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

        $periodo = PeriodoAquisitivo::all();

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

        $resultado = FeriasPrevista::with(
            'Colaborador:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,email,municipio_id,uf_vaga',
            'Colaborador.FeedBack:id,curriculo_id,vagas_abertas_id,vaga_id',
            'Colaborador.FeedBack.Admissao:id,feedback_id,data_admissao',
            'Colaborador.FeedBack.VagaSelecionada',
            'CentroCusto:id,label',
            'UserCadastrou:id,nome',
            'QuemAprovou:id,nome',
            'RhAprovacao:id,nome',
            'PeriodoAquisitivo',
        );

        $filtroPeriodo = $request->filtroPeriodo == 'true' ? true : false;
        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0]);
            $dataFim = new DataHora($periodo[1]);
            $resultado->where('created_at', '>=', $dataInicio->dataInsert() . ' 00:00:00')->where('created_at', '<=', $dataFim->dataInsert() . ' 23:59:59');
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
            $resultado->whereHas('Colaborador', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus;
            if ($request->campoStatus == "aberto"){
                $resultado->whereNull('status_aprovacao');
            }elseif ($request->campoStatus == "aprovado_rh"){
                $resultado->whereRespostaRh("aprovado");
            }elseif ($request->campoStatus == "aprovado"){
                $resultado->whereStatusAprovacao($status)->whereNull('resposta_rh');
            }else{
                $resultado->whereStatusAprovacao($status)->orWhere('resposta_rh', $status);
            }
        }

        if (!auth()->user()->can('privilegio_gestao_rh')) {
            $resultado->whereUserId(auth()->user()->id)->orWhere('gestor_id', auth()->user()->id);
        }

        return $resultado->orderByDesc('created_at');
    }

    public function buscaDataAdmissao(Request $request)
    {
        $data_admissao = FeedbackCurriculo::whereCurriculoId($request->colaborador_id)
            ->with('Admissao')->first();
        $dataAdmissao = $data_admissao->Admissao->data_admissao;

        $colaboradorPeriodo = FeriasPrevista::whereColaboradorId($request->colaborador_id)->latest('id')->first();
        $dataHoje = new DataHora();
        $data_saida = $dataHoje->dataCompleta();
        $data_retorno = $dataHoje->dataCompleta();

        if ($colaboradorPeriodo !== null && !$request->visualizar) {

            $periodo = PeriodoAquisitivo::where('id', '>', $colaboradorPeriodo->periodo_aquisitivo_id)->first();

            $date = new DataHora($dataAdmissao);
            $ultimoAnoPeriodoAquisitivo = $periodo->ano_final . '-' . $date->mes() . '-' . $date->dia();
            $data_saida = $date->dia() . '/' . $date->mes() . '/' . $periodo['ano_inicial'];
            $data_retorno = $date->dia() . '/' . $date->mes() . '/' . $periodo['ano_inicial'];
            $newDate = new DataHora($ultimoAnoPeriodoAquisitivo);
            $ultimaData = $newDate->addDia(330);

        } elseif ($colaboradorPeriodo !== null && $request->visualizar) {
            $periodo = $colaboradorPeriodo->PeriodoAquisitivo;
            $data_saida = $colaboradorPeriodo->data_saida;
            $data_retorno = $colaboradorPeriodo->data_retorno;
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
                $row->Colaborador->nome,
                $row->Colaborador->FeedBack->VagaSelecionada->nome,
                $row->Colaborador->FeedBack->Admissao->data_admissao,
                $row->CentroCusto->label,
                $row->PeriodoAquisitivo->label,
                $row->ultima_data,
                (string)$row->qnt_faltas,
                $row->data_saida,
                $row->data_retorno,
                (string)$row->qnt_dias,
                (string)$row->dias_saldo,
                $row->UserCadastrou->nome,
                $row->GestorAprovacao->nome,
                $row->QuemAprovou ? $row->QuemAprovou->nome : '',
                $row->status_aprovacao ? $row->data_aprovacao : '',
                $row->status_aprovacao,
                $row->RhAprovacao ? $row->RhAprovacao->nome : '',
                $row->resposta_rh ? (new DataHora())->dataHoraCompleta($row->data_aprovacao_rh) : '',
                $row->resposta_rh,
            );
        }

        $nameArquivo = "ferias_prevista" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Ferias - Prevista", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);

    }
}
