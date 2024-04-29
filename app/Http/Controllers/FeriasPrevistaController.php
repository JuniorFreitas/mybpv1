<?php

namespace App\Http\Controllers;

use App\Jobs\Movimentacao\FeriasPrevista\JobFeriasPrevistaAprovarRH;
use App\Jobs\Movimentacao\FeriasPrevista\JobFeriasPrevistaExportaExcel;
use App\Jobs\Movimentacao\FeriasPrevista\JobFeriasPrevistaStore;
use App\Jobs\Movimentacao\FeriasPrevista\JobFeriasPrevistaUpdate;
use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\Ferias;
use App\Models\FeriasPrevista;
use App\Models\PeriodoAquisitivo;
use App\Models\Sistema;
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
                'qnt_dias' => 'required',
                'dias_saldo' => 'required',
                'periodo_aquisitivo_id' => 'required',
                'colaborador_id' => [
                    function ($attribute, $value, $fail) use ($dados) {
                        if (strlen($value) == 0) {
                            $fail('Informe um colaborador para continuar');
                        }
                    }
                ],
                'gestor_id' => [
                    function ($attribute, $value, $fail) use ($dados) {
                        if (strlen($value) == 0) {
                            $fail('Informe um gestor para aprovação');
                        }
                    }
                ],
                'data_admissao' => [
                    function ($attribute, $value, $fail) use ($dados) {
                        if (strlen($value) == 0) {
                            $fail('Atualize a data de admissão do colaborador');
                        }
                    }
                ]
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

            $ferias = Ferias::where('admissao_id', $dados['admissao_id'])
                ->where('periodo_aquisitivo_id', $dados['periodo_aquisitivo_id'])
                ->where('dias_saldo', '>', 0)
                ->where('status_ferias', Ferias::STATUS_GOZADA)
                ->orderByDesc('id')
                ->first();
            
            $ferias_saldo = $ferias->dias_saldo ?? 0;

            $dados['dias_saldo'] = $ferias_saldo - $dados['qnt_dias'];

            if ($dados['dias_saldo'] < 0) {
                return response()->json([
                    'msg' => 'Tentando tirar mais dias do que tem de saldo o maximo que pode tirar é ' . $ferias_saldo . ' dias.'
                ], 400);
            }

            if (!is_null($ferias) && $ferias->dias_saldo == 0) {
                return response()->json([
                    'msg' => 'Colaborador sem saldo de férias para este período aquisitivo'
                ], 400);
            }

            $dataAdmissao = $dados['data_admissao'];

            $periodo = PeriodoAquisitivo::where('id', $dados['periodo_aquisitivo_id'])->first();

            $date = new DataHora($dataAdmissao);
            $ultimoAnoPeriodoAquisitivo = $periodo->ano_final . '-' . $date->mes() . '-' . $date->dia();
            $newDate = new DataHora($ultimoAnoPeriodoAquisitivo);
            $newDate->addDia(330);
            $dados['ultima_data'] = $newDate->dataInsert();

            $dados['data_solicitacao'] = (new DataHora())->dataHoraInsert();
            $dados['solicitante_id'] = auth()->user()->id;

            Admissao::find($dados['admissao_id'])->update([
                'centro_custo_id' => $dados['centro_custo_id']
            ]);

            $feriasPrevista = Ferias::create($dados);

            if (isset($dados['anexos'])) {
                foreach ($dados['anexos'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->nome = $anexo['nome'];
                        $arquivo->chave = '';
                        $arquivo->save();
                        $feriasPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }

            DB::commit();
            JobFeriasPrevistaStore::dispatch($feriasPrevista);
            return response()->json('', 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao salvar Solicitação de Férias:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            Sistema::LogFormatado($dados);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
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
        $admissao = $ferias->Admissao;
        $curriculo = optional($admissao->Feedback->Curriculo);
        $gestor = optional($ferias->Gestor);
        $centroCusto = optional($admissao->CentroCusto);
        $rhAprovacao = optional($ferias->RhAprovacao);
        $solicitante = optional($ferias->Solicitante);

        $ferias->autocomplete_label_colaborador = $curriculo->nome ?? '';
        $ferias->autocomplete_label_colaborador_anterior = $curriculo->nome ?? '';
        $ferias->colaborador_id = $curriculo->id ?? '';
        $ferias->autocomplete_label_gestor_modal = $gestor->nome ?? '';
        $ferias->autocomplete_label_gestor_modal_anterior = $gestor->nome ?? '';
        $ferias->gestor_aprovacao = $ferias->GestorAprovacao->nome ?? '';
        $ferias->centro_custo_id = $admissao ? $admissao->centro_custo_id : $ferias->FeriasPrevista->centro_custo_id;
        $ferias->centro_custo = $centroCusto ?? '';
        $ferias->rh_aprovacao = $rhAprovacao->nome ?? '';
        $ferias->solicitante = $solicitante->nome ?? '';
        $ferias->gestor_id = $ferias->gestor_id ?? '';
        $ferias->data_admissao = $admissao->data_admissao ?? '';
        $ferias->status_aprovacao_gestor = $ferias->status_aprovacao_gestor ?? '';
        $ferias->status_aprovacao_rh = $ferias->status_aprovacao_rh ?? '';
        $ferias->periodo_label = $ferias->PeriodoAquisitivo->label ?? '';
        $ferias->anexosDel = [];
        $ferias->load('Anexos');

        return response()->json($ferias);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Ferias $ferias
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Ferias $ferias)
    {
        $this->authorize('planejamento_movimentacao_ferias_editar');
        $dados = $request->only(['admissao_id', 'centro_custo_id', 'periodo_aquisitivo_id', 'data_saida',
            'data_retorno', 'ultima_data', 'qnt_dias', 'dias_saldo', 'tem_faltas', 'qnt_faltas', 'gestor_id',
            'solicitante_id', 'abono_pecuniario', 'adiantamento_decimo_terceiro',
            'obs_solicitante', 'anexosDel', 'anexos'
        ]);

        // Converter as datas para o formato desejado
        $camposData = ['ultima_data', 'data_retorno', 'data_saida'];
        foreach ($camposData as $campo) {
            if (isset($dados[$campo])) {
                $dados[$campo] = (new DataHora($dados[$campo]))->dataInsert();
            }
        }

        // Validar os dados
        $dadosValidados = \Validator::make($dados, [
            'periodo_aquisitivo_id' => 'required',
            'data_saida' => 'required',
            'data_retorno' => 'required',
            'ultima_data' => 'required',
            'qnt_dias' => 'required',
            'dias_saldo' => 'required',
            'tem_faltas' => 'required|boolean',
            'qnt_faltas' => 'required',
            'solicitante_id' => 'required',
            'abono_pecuniario' => 'required|boolean',
            'adiantamento_decimo_terceiro' => 'required|boolean',
        ]);

        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao Editar Férias',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Atualizar informações relacionadas à admissão
            Admissao::find($dados['admissao_id'])->update([
                'centro_custo_id' => $dados['centro_custo_id']
            ]);

            // Atualizar informações da solicitação de férias
            $ferias->update([
                'periodo_aquisitivo_id' => $dados['periodo_aquisitivo_id'],
                'data_saida' => $dados['data_saida'],
                'data_retorno' => $dados['data_retorno'],
                'ultima_data' => $dados['ultima_data'],
                'qnt_dias' => $dados['qnt_dias'],
                'dias_saldo' => $dados['dias_saldo'],
                'tem_faltas' => $dados['tem_faltas'],
                'qnt_faltas' => $dados['tem_faltas'] ? $dados['qnt_faltas'] : 0,
                'solicitante_id' => $dados['solicitante_id'],
                'obs_solicitante' => $dados['obs_solicitante'],
                'abono_pecuniario' => $dados['abono_pecuniario'],
                'gestor_id' => $dados['gestor_id'],
                'adiantamento_decimo_terceiro' => $dados['adiantamento_decimo_terceiro'],
            ]);

            // Remover anexos
            if (isset($dados['anexosDel'])) {
                foreach ($dados['anexosDel'] as $id_anexo) {
                    $arquivo = Arquivo::find($id_anexo);
                    $arquivo->excluir();
                }
            }

            // Associar novos anexos
            foreach ($dados['anexos'] as $index => $anexo) {
                Arquivo::find($anexo['id'])->update([
                    'nome' => $anexo['nome'],
                ]);

                $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                if ($arquivo) {
                    $arquivo->temporario = false;
                    $arquivo->nome = $anexo['nome'];
                    $arquivo->chave = '';
                    $arquivo->save();
                    $ferias->Anexos()->attach($arquivo->id);
                }
            }

            DB::commit();

            $dadosJobsEmail = [
                'nome_de' => auth()->user()->nome,
                'email_de' => auth()->user()->login,
                'nome_para' => $ferias->Gestor->nome,
                'email_para' => $ferias->Gestor->login,
                'ferias_id' => $ferias->id,
                'colaborador' => $ferias->Admissao->Feedback->Curriculo->nome,
                'empresa_id' => auth()->user()->empresa_id
            ];

            JobFeriasPrevistaUpdate::dispatch($dadosJobsEmail);

            return response()->json('', 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "Erro ao atualizar Solicitação de Férias:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            Sistema::LogFormatado($dados);
            return $e->getTrace();
            return response()->json(['msg' => $msg], 400);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Ferias $ferias
     * @return \Illuminate\Http\Response
     * @throws \Throwable
     */
    public function destroy(Ferias $ferias)
    {
        $this->authorize('planejamento_movimentacao_ferias_deletar');

        try {
            \DB::beginTransaction();

            if ($ferias->Anexos->count() > 0) {
                foreach ($ferias->Anexos as $anexo) {
                    $anexo->excluir($anexo->id);
                }
            }
            $ferias->update([
                'quem_deletou_id' => auth()->id()
            ]);

            $ferias->delete();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['msg' => $e->getMessage()], 400);
        }
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
            $msg = "error ao aprovar Solicitação de Férias - Gestor:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            Sistema::LogFormatado($dados);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    public function aprovarRH(Request $request, Ferias $ferias)
    {
        $this->authorize('privilegio_aprovar_por_rh');

        try {
            DB::beginTransaction();

            $dados = $request->input();
            $hoje = (new DataHora())->dataInsert();
            $status = Ferias::STATUS_REPROVADO;

            if ($dados['status_aprovacao_rh'] == Ferias::STATUS_APROVADO) {
                $status = $this->calcularStatusFerias($ferias, $hoje);
            }

            $ferias->update([
                'rh_aprovacao_id' => auth()->id(),
                'status_aprovacao_rh' => $dados['status_aprovacao_rh'],
                'obs_rh' => $dados['obs_rh'],
                'data_aprovacao_rh' => (new DataHora())->dataHoraInsert(),
                'status_ferias' => $status,
                'data_status_ferias' => (new DataHora())->dataHoraInsert(),
            ]);

            DB::commit();

            $dados_email = $this->prepararDadosEmail($ferias);
            JobFeriasPrevistaAprovarRH::dispatch($dados_email);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "Erro ao aprovar solicitação - RH: {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            Sistema::LogFormatado($dados);
            return response()->json(['msg' => 'Houve um erro, por favor tente novamente!'], 400);
        }
    }

    private function calcularStatusFerias($ferias, $hoje)
    {
        $status = Ferias::STATUS_REPROVADO;
        if ((new DataHora($ferias->data_saida))->dataInsert() <= $hoje && (new DataHora($ferias->data_retorno))->dataInsert() >= $hoje) {
            $status = Ferias::STATUS_GOZANDO;
        }
        if ((new DataHora($ferias->data_retorno))->dataInsert() < $hoje) {
            $status = Ferias::STATUS_GOZADA;
        }
        if ((new DataHora($ferias->data_saida))->dataInsert() > $hoje) {
            $status = Ferias::STATUS_AGUARDANDO;
        }
        return $status;
    }

    private function prepararDadosEmail($ferias)
    {
        $empresa = Cliente::find(auth()->user()->empresa_id);

        return [
            'dados_quem_cadastrou' => $this->prepararDadosEmailUsuario(auth()->user(), $ferias->Solicitante, $ferias, $empresa),
            'dados_gestor' => $this->prepararDadosEmailUsuario(auth()->user(), $ferias->GestorAprovacao, $ferias, $empresa),
        ];
    }

    private function prepararDadosEmailUsuario($de, $para, $ferias, $empresa)
    {
        return [
            'nome_de' => $de->nome,
            'nome_para' => $para->nome,
            'email_para' => $para->login,
            'status_aprovacao' => $ferias->status_aprovacao_rh,
            'ferias_id' => $ferias->id,
            'colaborador' => $ferias->Admissao->Feedback->Curriculo->nome,
            'empresa_id' => auth()->user()->empresa_id,
            'nome_empresa' => $empresa->razao_social,
        ];
    }

    public function atualizar(Request $request)
    {

        $resultado = $this->filtro($request)->paginate($request->pages);

        $periodo = PeriodoAquisitivo::whereIn('ano_inicial', [date('Y'), date('Y') - 1, date('Y') - 2, date('Y') - 3])->get();

        $permissoes = [
            'update' => auth()->user()->can('planejamento_movimentacao_ferias_editar'),
            'delete' => auth()->user()->can('planejamento_movimentacao_ferias_deletar')
        ];

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'periodo' => $periodo,
                'permissoes' => $permissoes,
                'aprovar_por_gestor' => auth()->user()->can('privilegio_aprovar_por_gestor'),
                'aprovar_por_rh' => auth()->user()->can('privilegio_aprovar_por_rh'),
                'mimes' => Arquivo::MIMEAPENASIMAGENSPDF
            ]
        ]);
    }

    public function filtro(Request $request)
    {

        $resultado = Ferias::with(
            'PeriodoAquisitivo',
            'Gestor:id,nome',
            'GestorAprovacao:id,nome',
            'RhAprovacao:id,nome',
            'Solicitante:id,nome',
            'Admissao:id,centro_custo_id,cargo,funcao,data_admissao,feedback_id',
            'Admissao.CentroCusto',
            'Admissao.Feedback:id,curriculo_id,vagas_abertas_id',
            'Admissao.Feedback.VagaSelecionada',
            'Admissao.Feedback.Curriculo:id,nome,nascimento,rg,orgao_expeditor',
            'Admissao.CentroCusto:id,label',
            'FeriasPrevista:id,centro_custo_id',
            'FeriasPrevista.CentroCusto:id,label',
        );

        $filtroPeriodo = $request->filtroPeriodo == 'true';
        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0] . ' 00:00:00');
            $dataFim = new DataHora($periodo[1] . ' 23:59:59');
            $resultado->where('data_solicitacao', '>=', $dataInicio->dataHoraInsert())
                ->where('data_solicitacao', '<=', $dataFim->dataHoraInsert());
        }

        $filtroVencimento = $request->filtroVencimento == 'true';
        if ($filtroVencimento) {
            $periodoVenc = explode(' até ', $request->vencimento);
            $dataInicioVenc = new DataHora($periodoVenc[0] . ' 00:00:00');
            $dataFimVenc = new DataHora($periodoVenc[1] . ' 23:59:59');
            $resultado->where('ultima_data', '>=', $dataInicioVenc->dataHoraInsert())
                ->where('ultima_data', '<=', $dataFimVenc->dataHoraInsert());
        }

        $filtroInicioFerias = $request->filtroInicioFerias == 'true';
        if ($filtroInicioFerias) {
            $periodoFer = explode(' até ', $request->inicioFerias);
            $dataInicioFer = new DataHora($periodoFer[0] . ' 00:00:00');
            $dataFimFer = new DataHora($periodoFer[1] . ' 23:59:59');
            $resultado->where('data_saida', '>=', $dataInicioFer->dataHoraInsert())
                ->where('data_saida', '<=', $dataFimFer->dataHoraInsert());
        }

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Admissao.Feedback.Curriculo', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoStatusAprovacao')) {
            $status = $request->campoStatusAprovacao;
            if ($request->campoStatusAprovacao == "aberto") {
                $resultado->whereNull('status_aprovacao_gestor');
            } elseif ($request->campoStatusAprovacao == "aprovado_gestor") {
                $resultado->where('status_aprovacao_gestor', Ferias::STATUS_APROVADO)->whereNull('status_aprovacao_rh');
            } elseif ($request->campoStatusAprovacao == "aprovado_rh") {
                $resultado->where('status_aprovacao_rh', Ferias::STATUS_APROVADO);
            } else {
                $resultado->whereStatusAprovacaoGestor(Ferias::STATUS_REPROVADO)->orWhere('status_aprovacao_rh', Ferias::STATUS_REPROVADO);
            }
        }


        if (!auth()->user()->can('privilegio_gestao_rh')) {
            $resultado->whereSolicitanteId(auth()->user()->id)
                ->orWhere('gestor_id', auth()->user()->id);
        }

        if ($request->filled('filtroPeriodoAquisitivo')) {
            $resultado->whereHas('PeriodoAquisitivo', function ($q) use ($request) {
                $q->where('id', $request->filtroPeriodoAquisitivo);
            });
        } else {
            $resultado->whereHas('PeriodoAquisitivo', function ($q) use ($request) {
                $q->whereIn('ano_inicial', [date('Y') - 3, date('Y') - 2, date('Y') - 1, (int)date('Y')]);
            });
        }

        // Fazer o filtro pra excluir gozada
        return $resultado->orderByDesc('data_solicitacao');
    }

    public function buscaDataAdmissao(Request $request)
    {
        $dados = $request->input();
        if (!is_null($request->ferias_id)) {
            $ferias = Ferias::find($request->ferias_id);
            $dataAdmissao = $ferias->Admissao->data_admissao;
            $colaboradorPeriodo = $ferias->PeriodoAquisitivo;
        } else {
            $admissao = Admissao::whereFeedbackId($dados['colaborador_id'])->Admitidos()->first();

            $admissaoId = $admissao->id;
            $dataAdmissao = $admissao->data_admissao;
            $ferias = Ferias::whereAdmissaoId($admissaoId)->whereStatusFerias(Ferias::STATUS_GOZADA)->orderByDesc('data_retorno')->select('periodo_aquisitivo_id')->first();

            if (is_null($ferias)) {
                $anoAtual = date('Y');
                $periodo = PeriodoAquisitivo::where('ano_inicial', $anoAtual)->first();
                $colaboradorPeriodo = $periodo;
            } else {
                $colaboradorPeriodo = $ferias->PeriodoAquisitivo;
            }
        }

        $dataHoje = new DataHora();
        $data_saida = $dataHoje->dataCompleta();
        $data_retorno = $dataHoje->dataCompleta();

        if ($colaboradorPeriodo !== null && !$request->visualizar) {

            $periodo = PeriodoAquisitivo::where('id', '>=', $colaboradorPeriodo->id)->first();

            $date = (new DataHora($dataAdmissao));
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
            $periodo = PeriodoAquisitivo::get();
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

        $periodo = PeriodoAquisitivo::get();

        return response()->json(['periodos' => $periodo]);
    }

    public function atualizacaoStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            foreach ($request->selecionados[0] as $selecionado) {
                $feriasPrevista = Ferias::find($selecionado);
                $dados = [
                    'user_aprovacao_id' => auth()->id(),
                    'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                    'obs_gestor' => $request->obs_aprovacao,
                    'status_aprovacao_gestor' => $request->status_aprovacao,
                ];
                $feriasPrevista->update($dados);
            }
            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao Atualizar Status em Massa:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            Sistema::LogFormatado($dados);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    //Excel
    public function export(Request $request)
    {
        JobFeriasPrevistaExportaExcel::dispatch(auth()->user(), $this->filtro($request));
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }
}
