<?php

namespace App\Http\Controllers;

use App\Jobs\Movimentacao\MudaCargoPrevista\JobMudaCargoPrevistaAprovarRH;
use App\Jobs\Movimentacao\MudaCargoPrevista\JobMudaCargoPrevistaExportaExcel;
use App\Jobs\Movimentacao\MudaCargoPrevista\JobMudaCargoPrevistaStore;
use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\Ferias;
use App\Models\LogHistorico;
use App\Models\MudancaCargo;
use App\Models\PeriodoAquisitivo;
use App\Models\Sistema;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class MudancaCargoController extends Controller
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

        $dadosValidados = \Validator::make($dados,
            [
                'novo_centro_custo_id' => 'required_if:mantem_centro_custo,false',
                'novo_centro_custo_filial_id' => 'required_if:novo_filial,true',
                'nova_vaga_aberta_id' => 'required_if:mantem_cargo,false',
                'nova_funcao' => 'required_if:mantem_funcao,false',
                'novo_salario' => 'required_if:mantem_salario,false',
                'colaborador_id' => [
                    function ($attribute, $value, $fail) use ($dados) {
                        if (strlen($value) == 0) {
                            $fail('Informe um colaborar para continuar');
                        }
                    }
                ],
                'gestor_id' => [
                    function ($attribute, $value, $fail) use ($dados) {
                        if (strlen($value) == 0) {
                            $fail('Informe um gestor responsável');
                        }
                    }
                ],
                'treinamento_data_inicio' => 'required_if:treinamento_funcao,true',
                'treinamento_data_fim' => 'required_if:treinamento_funcao,true',
                'treinamento_termo_ciencia' => [
                    function ($attribute, $value, $fail) use ($dados) {
                        if (isset($dados['treinamento_funcao']) && $dados['treinamento_funcao'] == true) {
                            if (empty($value) || !is_array($value) || count($value) == 0) {
                                $fail('O Termo de Ciência de Treinamento é obrigatório quando há treinamento na função');
                            }
                        }
                    }
                ],
                'treinamento_certificado' => [
                    function ($attribute, $value, $fail) use ($dados) {
                        if (isset($dados['treinamento_funcao']) && $dados['treinamento_funcao'] == true) {
                            if (empty($value) || !is_array($value) || count($value) == 0) {
                                $fail('O Certificado de Treinamento é obrigatório quando há treinamento na função');
                            }
                        }
                    }
                ],
            ]
        );

        // Validação de datas de treinamento
        if (isset($dados['treinamento_funcao']) && $dados['treinamento_funcao'] == true) {
            if (isset($dados['treinamento_data_inicio']) && isset($dados['treinamento_data_fim']) && 
                !empty($dados['treinamento_data_inicio']) && !empty($dados['treinamento_data_fim'])) {
                
                try {
                    $dataInicio = new DataHora($dados['treinamento_data_inicio']);
                    $dataFim = new DataHora($dados['treinamento_data_fim']);
                    
                    if ($dataInicio->dataInsert() > $dataFim->dataInsert()) {
                        $dadosValidados->errors()->add('treinamento_data_inicio', 'A data de início do treinamento não pode ser maior que a data de fim do treinamento');
                        $dadosValidados->errors()->add('treinamento_data_fim', 'A data de fim do treinamento não pode ser menor que a data de início do treinamento');
                    }
                } catch (\Exception $e) {
                    // Se houver erro na conversão das datas, a validação padrão já vai pegar
                }
            }
        }
        
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Mudança de Cargo',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            DB::beginTransaction();

            $temMudancaCargoAprovar = MudancaCargo::where('admissao_id', $dados['admissao_id'])
                ->whereNull('status_aprovacao_gestor')
                ->whereNull('status_aprovacao_rh')
                ->first();

            if (!is_null($temMudancaCargoAprovar)) {
                return response()->json([
                    'msg' => 'Colaborador com mudança de cargo pendente de aprovação'
                ], 400);
            }

            $dados['empresa_id'] = auth()->user()->empresa_id;
            $dados['data_solicitacao'] = (new DataHora())->dataHoraInsert();
            $dados['solicitante_id'] = auth()->user()->id;

            $dadosMudancaCargo = [
                'empresa_id' => $dados['empresa_id'],
                'admissao_id' => $dados['admissao_id'],
                'colaborador_id' => $dados['colaborador_id'],
                'mantem_centro_custo' => $dados['mantem_centro_custo'],
                'anterior_centro_custo_id' => $dados['anterior_centro_custo_id'],
                'anterior_filial' => $dados['anterior_filial'],
                'anterior_centro_custo_filial_id' => $dados['anterior_filial'] ? $dados['anterior_centro_custo_filial_id'] : null,
                'novo_centro_custo_id' => !$dados['mantem_centro_custo'] ? $dados['novo_centro_custo_id'] : null,
                'novo_filial' => $dados['novo_filial'],
                'novo_centro_custo_filial_id' => $dados['novo_filial'] ? $dados['novo_centro_custo_filial_id'] : null,
                'mantem_cargo' => $dados['mantem_cargo'],
                'anterior_vaga_aberta_id' => $dados['anterior_vaga_aberta_id'],
                'nova_vaga_aberta_id' => !$dados['mantem_cargo'] ? $dados['nova_vaga_aberta_id'] : null,
                'mantem_funcao' => $dados['mantem_funcao'],
                'anterior_funcao' => $dados['anterior_funcao'],
                'nova_funcao' => !$dados['mantem_funcao'] ? $dados['nova_funcao'] : null,
                'mantem_salario' => $dados['mantem_salario'],
                'anterior_salario' => $dados['anterior_salario'] ?? '0,00',
                'novo_salario' => $dados['mantem_salario'] ? '0,00' : ($dados['novo_salario'] ?? '0,00'),
                'solicitante_id' => $dados['solicitante_id'],
                'obs_solicitante' => $dados['obs_solicitante'],
                'data_solicitacao' => $dados['data_solicitacao'],
                'gestor_id' => $dados['gestor_id'],
                'treinamento_funcao' => $dados['treinamento_funcao'] ?? false,
                'treinamento_data_inicio' => isset($dados['treinamento_data_inicio']) && !empty($dados['treinamento_data_inicio']) ? (new DataHora($dados['treinamento_data_inicio']))->dataInsert() : null,
                'treinamento_data_fim' => isset($dados['treinamento_data_fim']) && !empty($dados['treinamento_data_fim']) ? (new DataHora($dados['treinamento_data_fim']))->dataInsert() : null,
                'aprovado_via_script' => false,
            ];

            $mudancaCargo = MudancaCargo::create($dadosMudancaCargo);

            if (isset($dados['anexos'])) {
                foreach ($dados['anexos'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $mudancaCargo->Anexos()->attach($arquivo->id, ['tipo_anexo' => 'anexo_default']);
                    }
                }
            }

            if (isset($dados['treinamento_termo_ciencia'])) {
                foreach ($dados['treinamento_termo_ciencia'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $mudancaCargo->Anexos()->attach($arquivo->id, ['tipo_anexo' => 'termo_ciencia_treinamento']);
                    }
                }
            }

            if (isset($dados['treinamento_certificado'])) {
                foreach ($dados['treinamento_certificado'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $mudancaCargo->Anexos()->attach($arquivo->id, ['tipo_anexo' => 'certificado_treinamento']);
                    }
                }
            }

            DB::commit();
            JobMudaCargoPrevistaStore::dispatch($mudancaCargo);
            return response()->json('', 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao salvar Solicitação de Mudança de Cargo:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            Sistema::LogFormatado($dados);
            return response()->json(['msg' => $msg], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\MudancaCargo $mudancaCargo
     * @return \Illuminate\Http\Response
     */
    public function show(MudancaCargo $mudancaCargo)
    {
        //
    }

    /**
     * @param MudancaCargo $mudancaCargo
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(MudancaCargo $mudancaCargo)
    {
        $mudancaCargo->autocomplete_label_colaborador = $mudancaCargo->Admissao->Feedback->Curriculo ? $mudancaCargo->Admissao->Feedback->Curriculo->nome : '';
        $mudancaCargo->autocomplete_label_colaborador_anterior = $mudancaCargo->Admissao->Feedback->Curriculo ? $mudancaCargo->Admissao->Feedback->Curriculo->nome : '';
        $mudancaCargo->autocomplete_label_gestor_modal = $mudancaCargo->Gestor ? $mudancaCargo->Gestor->nome : '';
        $mudancaCargo->autocomplete_label_gestor_modal_anterior = $mudancaCargo->Gestor ? $mudancaCargo->Gestor->nome : '';
        $mudancaCargo->autocomplete_label_vaga_nova = $mudancaCargo->VagaAbertaNova ? $mudancaCargo->VagaAbertaNova->titulo : '';
        $mudancaCargo->autocomplete_label_vaga_anterior = $mudancaCargo->VagaAbertaAnterior ? $mudancaCargo->VagaAbertaAnterior->titulo : '';
        $mudancaCargo->gestor_aprovacao = $mudancaCargo->GestorAprovacao ? $mudancaCargo->GestorAprovacao->nome : '';
        $mudancaCargo->rh_aprovacao = $mudancaCargo->RhAprovacao ? $mudancaCargo->RhAprovacao->nome : '';
        $mudancaCargo->solicitante = $mudancaCargo->Solicitante->nome ?? '';
        $mudancaCargo->status_aprovacao_gestor = $mudancaCargo->status_aprovacao_gestor ?: '';
        $mudancaCargo->status_aprovacao_rh = $mudancaCargo->status_aprovacao_rh ?: '';
        $mudancaCargo->anexosDel = [];
        $mudancaCargo->treinamento_termo_cienciaDel = [];
        $mudancaCargo->treinamento_certificadoDel = [];
        $mudancaCargo->load('Anexos');

        // Separa os anexos por tipo
        $mudancaCargo->treinamento_termo_ciencia = $mudancaCargo->Anexos->filter(function ($anexo) {
            return ($anexo->pivot->tipo_anexo ?? 'anexo_default') === 'termo_ciencia_treinamento';
        })->values();
        $mudancaCargo->treinamento_certificado = $mudancaCargo->Anexos->filter(function ($anexo) {
            return ($anexo->pivot->tipo_anexo ?? 'anexo_default') === 'certificado_treinamento';
        })->values();
        $mudancaCargo->anexos = $mudancaCargo->Anexos->filter(function ($anexo) {
            $tipo = $anexo->pivot->tipo_anexo ?? null;
            // Inclui apenas anexos com tipo_anexo = 'anexo_default' ou null (anexos antigos sem tipo)
            return $tipo === 'anexo_default' || $tipo === null;
        })->values();

        // Remove o relacionamento Anexos para evitar que todos os anexos sejam serializados
        $mudancaCargo->unsetRelation('Anexos');

        return response()->json($mudancaCargo, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\MudancaCargo $mudancaCargo
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, MudancaCargo $mudancaCargo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\MudancaCargo $mudancaCargo
     * @return \Illuminate\Http\Response
     */
    public function destroy(MudancaCargo $mudancaCargo)
    {
        //
    }


    public function aprovarGestor(Request $request)
    {
        $this->authorize('privilegio_aprovar_por_gestor');
        $dados = $request->input();

        try {
            DB::beginTransaction();

            $mudanca_cargo = MudancaCargo::find($dados['id']);

            $mudanca_cargo->update([
                'gestor_aprovacao_id' => auth()->id(),
                'data_aprovacao_gestor' => (new DataHora())->dataHoraInsert(),
                'obs_gestor_aprovacao' => $dados['obs_gestor_aprovacao'],
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
                        $mudanca_cargo->Anexos()->attach($arquivo->id, ['tipo_anexo' => 'anexo_default']);
                    }
                }
            }

            if (isset($dados['treinamento_termo_cienciaDel'])) {
                foreach ($dados['treinamento_termo_cienciaDel'] as $id_anexo) {
                    $arquivo = Arquivo::find($id_anexo);
                    if ($arquivo) {
                        DB::table('mudanca_cargo_anexos')
                            ->where('mudanca_cargo_id', $mudanca_cargo->id)
                            ->where('arquivo_id', $id_anexo)
                            ->where('tipo_anexo', 'termo_ciencia_treinamento')
                            ->delete();
                        $arquivo->excluir();
                    }
                }
            }

            if (isset($dados['treinamento_termo_ciencia'])) {
                foreach ($dados['treinamento_termo_ciencia'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $mudanca_cargo->Anexos()->attach($arquivo->id, ['tipo_anexo' => 'termo_ciencia_treinamento']);
                    }
                }
            }

            if (isset($dados['treinamento_certificadoDel'])) {
                foreach ($dados['treinamento_certificadoDel'] as $id_anexo) {
                    $arquivo = Arquivo::find($id_anexo);
                    if ($arquivo) {
                        DB::table('mudanca_cargo_anexos')
                            ->where('mudanca_cargo_id', $mudanca_cargo->id)
                            ->where('arquivo_id', $id_anexo)
                            ->where('tipo_anexo', 'certificado_treinamento')
                            ->delete();
                        $arquivo->excluir();
                    }
                }
            }

            if (isset($dados['treinamento_certificado'])) {
                foreach ($dados['treinamento_certificado'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $mudanca_cargo->Anexos()->attach($arquivo->id, ['tipo_anexo' => 'certificado_treinamento']);
                    }
                }
            }
            DB::commit();

            $dados_email = [
                'dados_quem_cadastrou' => [
                    'nome_de' => auth()->user()->nome,
                    'nome_para' => $mudanca_cargo->Solicitante->nome,
                    'email_para' => $mudanca_cargo->Solicitante->login,
                    'status_aprovacao' => $mudanca_cargo->status_aprovacao_gestor,
                    'mudanca_cargo_id' => $mudanca_cargo->id,
                    'colaborador' => $mudanca_cargo->Admissao->Feedback->Curriculo->nome,
                    'empresa_id' => auth()->user()->empresa_id,
                    'nome_empresa' => Cliente::find(auth()->user()->empresa_id)->razao_social
                ],
                'dados_gestor' => [
                    'nome_de' => auth()->user()->nome,
                    'nome_para' => $mudanca_cargo->GestorAprovacao->nome,
                    'email_para' => $mudanca_cargo->GestorAprovacao->login,
                    'status_aprovacao' => $mudanca_cargo->status_aprovacao_gestor,
                    'mudanca_cargo_id' => $mudanca_cargo->id,
                    'colaborador' => $mudanca_cargo->Admissao->Feedback->Curriculo->nome,
                    'empresa_id' => auth()->user()->empresa_id,
                    'nome_empresa' => Cliente::find(auth()->user()->empresa_id)->razao_social
                ]
            ];

            JobMudaCargoPrevistaAprovarRH::dispatch($dados_email);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar Solicitação de Mudança de Cargo - Gestor:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            Sistema::LogFormatado($dados);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    public function aprovarRH(Request $request)
    {
        $this->authorize('privilegio_aprovar_por_rh');
        $dados = $request->input();
        $mudancaCargo = MudancaCargo::find($dados['id']);
        try {
            DB::beginTransaction();

            $mudancaCargo->update([
                'rh_aprovacao_id' => auth()->id(),
                'status_aprovacao_rh' => $dados['status_aprovacao_rh'],
                'obs_rh' => $dados['obs_rh'],
                'data_aprovacao_rh' => (new DataHora())->dataHoraInsert()
            ]);

            $mudancaCargo->load([
                'CentroCustoAnterior',
                'CentroCustoNovo',
                'VagaAbertaAnterior.Vaga',
                'VagaAbertaNova.Vaga'
            ]);

            $admissao = Admissao::find($dados['admissao_id']);
            $admissao->load('CentroCusto');
        

            if ($dados['status_aprovacao_rh'] === 'aprovado') {
                if (!$dados['mantem_cargo']) {
                    $admissao->Feedback->update([
                        'vagas_abertas_id' => $mudancaCargo->VagaAbertaNova->id
                    ]);
                }
                
                $admissao->update([
                    'centro_custo_filial_id' => !$dados['mantem_centro_custo'] && $dados['novo_filial'] ? $dados['novo_centro_custo_filial_id'] : $admissao->centro_custo_filial_id,
                    'filial' => !$dados['mantem_centro_custo'] ? $dados['novo_filial'] : $admissao->filial,
                    'centro_custo_id' => !$dados['mantem_centro_custo'] ? $dados['novo_centro_custo_id'] : $admissao->centro_custo_id,
                    'funcao' => !$dados['mantem_funcao'] ? $dados['nova_funcao'] : $admissao->funcao,
                    'cargo' => !$dados['mantem_cargo'] ? $mudancaCargo->VagaAbertaNova->Vaga->nome : $admissao->cargo,
                    'salario' => !$dados['mantem_salario'] ? $dados['novo_salario'] : $admissao->salario,
                ]);
            }

            // Log de mudança de centro de custo
            if (!$dados['mantem_centro_custo'] && $mudancaCargo->CentroCustoAnterior && $mudancaCargo->CentroCustoNovo) {
                LogHistorico::createLog(
                    $admissao->Feedback->id,
                    'Solicitação foi '.$dados['status_aprovacao_rh'].' pelo RH na mudança de Centro de Custo de ' . $mudancaCargo->CentroCustoAnterior->label . ' para ' . $mudancaCargo->CentroCustoNovo->label . ' na solicitação de mudança de cargo #' . $mudancaCargo->id
                );
            }
            
            // Log de mudança de cargo
            if (!$dados['mantem_cargo'] && $mudancaCargo->VagaAbertaAnterior && $mudancaCargo->VagaAbertaNova) {
                $cargoAnterior = $mudancaCargo->VagaAbertaAnterior->Vaga ? $mudancaCargo->VagaAbertaAnterior->Vaga->nome : ($admissao->cargo ?? 'sem cargo');
                $cargoNovo = $mudancaCargo->VagaAbertaNova->Vaga ? $mudancaCargo->VagaAbertaNova->Vaga->nome : 'sem cargo';
                LogHistorico::createLog(
                    $admissao->Feedback->id,
                    'Solicitação foi '.$dados['status_aprovacao_rh'].' pelo RH na mudança de Cargo de ' . $cargoAnterior . ' para ' . $cargoNovo . ' na solicitação de mudança de cargo #' . $mudancaCargo->id
                );
            }
            
            // Log de mudança de função
            if (!$dados['mantem_funcao'] && $mudancaCargo->anterior_funcao && $mudancaCargo->nova_funcao) {
                LogHistorico::createLog(
                    $admissao->Feedback->id,
                    'Solicitação foi '.$dados['status_aprovacao_rh'].' pelo RH na mudança de Função de ' . $mudancaCargo->anterior_funcao . ' para ' . $mudancaCargo->nova_funcao . ' na solicitação de mudança de cargo #' . $mudancaCargo->id
                );
            }
            
            // Log de mudança de salário
            if (!$dados['mantem_salario'] && isset($mudancaCargo->anterior_salario) && isset($mudancaCargo->novo_salario)) {
                LogHistorico::createLog(
                    $admissao->Feedback->id,
                   'Solicitação foi '.$dados['status_aprovacao_rh'].' pelo RH na mudança de Salário na solicitação de mudança de cargo #' . $mudancaCargo->id
                );
            }

            DB::commit();

            $dados_email = [
                'dados_quem_cadastrou' => [
                    'nome_de' => auth()->user()->nome,
                    'nome_para' => $mudancaCargo->Solicitante->nome,
                    'email_para' => $mudancaCargo->Solicitante->login,
                    'status_aprovacao' => $mudancaCargo->status_aprovacao_gestor,
                    'mudanca_cargo_id' => $mudancaCargo->id,
                    'colaborador' => $mudancaCargo->Admissao->Feedback->Curriculo->nome,
                    'empresa_id' => auth()->user()->empresa_id,
                    'nome_empresa' => Cliente::find(auth()->user()->empresa_id)->razao_social
                ],
                'dados_gestor' => [
                    'nome_de' => auth()->user()->nome,
                    'nome_para' => $mudancaCargo->GestorAprovacao->nome,
                    'email_para' => $mudancaCargo->GestorAprovacao->login,
                    'status_aprovacao' => $mudancaCargo->status_aprovacao_gestor,
                    'mudanca_cargo_id' => $mudancaCargo->id,
                    'colaborador' => $mudancaCargo->Admissao->Feedback->Curriculo->nome,
                    'empresa_id' => auth()->user()->empresa_id,
                    'nome_empresa' => Cliente::find(auth()->user()->empresa_id)->razao_social
                ]
            ];

            JobMudaCargoPrevistaAprovarRH::dispatch($dados_email);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar solicitação - RH:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            Sistema::LogFormatado($dados);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }


    public function atualizar(Request $request)
    {

        $resultado = $this->filtro($request)->paginate($request->pages);

        $periodo = PeriodoAquisitivo::whereIn('ano_inicial', [date('Y') - 2, date('Y') - 1, date('Y')])->get();

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
        $resultado = MudancaCargo::with(
            'CentroCustoAnterior',
            'CentroCustoNovo',
            'CentroCustoFilialAnterior',
            'CentroCustoFilialNovo',
            'VagaAbertaAnterior',
            'VagaAbertaNova',
            'Solicitante:id,nome',
            'GestorAprovacao:id,nome',
            'Gestor:id,nome',
            'RhAprovacao:id,nome',
            'QuemDeletou:id,nome',
            'VagaAbertaNova',
            'Colaborador:id,nome,login,tipo,ativo');

        $filtroPeriodo = $request->filtroPeriodo == 'true';

        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0] . ' 00:00:00');
            $dataFim = new DataHora($periodo[1] . ' 23:59:59');
            $resultado->where('created_at', '>=', $dataInicio->dataHoraInsert())
                ->where('created_at', '<=', $dataFim->dataHoraInsert());
        }

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Colaborador', function ($q) use ($request) {
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
            $resultado->whereSolicitanteId(auth()->user()->id)->orWhere('gestor_id', auth()->user()->id);
        }

        return $resultado->orderByDesc('created_at');
    }

    //Excel
    public function export(Request $request)
    {
        JobMudaCargoPrevistaExportaExcel::dispatch(auth()->user(), $this->filtro($request));
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }
}
