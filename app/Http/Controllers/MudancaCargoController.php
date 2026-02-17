<?php

namespace App\Http\Controllers;

use App\Jobs\Movimentacao\MudaCargoPrevista\JobMudaCargoPrevistaExportaExcel;
use App\Jobs\Movimentacao\MudancaCargo\JobNotificacaoRecursiva;
use App\Models\Admissao;
use App\Models\AprovacaoExtraConfig;
use App\Models\Arquivo;
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

        $dadosValidados = \Validator::make(
            $dados,
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
            ]
        );
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
                'anterior_salario' => $dados['anterior_salario'],
                'novo_salario' => !$dados['mantem_salario'] ? $dados['novo_salario'] : null,
                'solicitante_id' => $dados['solicitante_id'],
                'obs_solicitante' => $dados['obs_solicitante'],
                'data_solicitacao' => $dados['data_solicitacao'],
                'gestor_id' => $dados['gestor_id'],
                'aprovado_via_script' => false,
            ];

            $mudancaCargo = MudancaCargo::create($dados);

            if (isset($dados['anexos'])) {
                foreach ($dados['anexos'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $mudancaCargo->Anexos()->attach($arquivo->id);
                    }
                }
            }

            DB::commit();

            // Notifica próxima etapa + etapas anteriores (recursivo)
            JobNotificacaoRecursiva::dispatch($mudancaCargo->id, $mudancaCargo->empresa_id);

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
        // Recarrega com eager load restrito (apenas id,nome para usuários) para não expor dados sensíveis
        $mudancaCargo = MudancaCargo::where('empresa_id', auth()->user()->empresa_id)
            ->with([
                'Admissao.Feedback.Curriculo:id,nome',
                'Gestor:id,nome',
                'GestorAprovacao:id,nome',
                'RhAprovacao:id,nome',
                'AprovacaoExtra:id,nome',
                'Solicitante:id,nome',
                'VagaAbertaAnterior:id,titulo',
                'VagaAbertaNova:id,titulo',
                'Anexos',
            ])
            ->findOrFail($mudancaCargo->id);

        $mudancaCargo->autocomplete_label_colaborador = $mudancaCargo->Admissao->Feedback->Curriculo ? $mudancaCargo->Admissao->Feedback->Curriculo->nome : '';
        $mudancaCargo->autocomplete_label_colaborador_anterior = $mudancaCargo->Admissao->Feedback->Curriculo ? $mudancaCargo->Admissao->Feedback->Curriculo->nome : '';
        $mudancaCargo->autocomplete_label_gestor_modal = $mudancaCargo->Gestor ? $mudancaCargo->Gestor->nome : '';
        $mudancaCargo->autocomplete_label_gestor_modal_anterior = $mudancaCargo->Gestor ? $mudancaCargo->Gestor->nome : '';
        $mudancaCargo->autocomplete_label_vaga_nova = $mudancaCargo->VagaAbertaNova ? $mudancaCargo->VagaAbertaNova->titulo : '';
        $mudancaCargo->autocomplete_label_vaga_anterior = $mudancaCargo->VagaAbertaAnterior ? $mudancaCargo->VagaAbertaAnterior->titulo : '';
        $mudancaCargo->aprovacao_extra_nome = $mudancaCargo->AprovacaoExtra ? $mudancaCargo->AprovacaoExtra->nome : '';
        $mudancaCargo->solicitante = $mudancaCargo->Solicitante->nome ?? '';
        $mudancaCargo->status_aprovacao_gestor = $mudancaCargo->status_aprovacao_gestor ?: '';
        $mudancaCargo->status_aprovacao_extra = $mudancaCargo->status_aprovacao_extra ?: '';
        $mudancaCargo->status_aprovacao_rh = $mudancaCargo->status_aprovacao_rh ?: '';
        $mudancaCargo->anexosDel = [];

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
                        $mudanca_cargo->Anexos()->attach($arquivo->id);
                    }
                }
            }
            DB::commit();

            // Notifica próxima etapa + etapas anteriores (recursivo)
            JobNotificacaoRecursiva::dispatch($mudanca_cargo->id, $mudanca_cargo->empresa_id);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar Solicitação de Mudança de Cargo - Gestor:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            Sistema::LogFormatado($dados);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function aprovarExtra(Request $request)
    {
        $dados = $request->input();

        // Busca configuração ativa de aprovação extra para mudança de cargo
        $config = AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, 'mudanca_cargo');

        if (!$config) {
            return response()->json(['msg' => 'Não existe configuração de aprovação extra ativa'], 400);
        }

        // Verifica se o usuário pode aprovar
        if (!$config->podeAprovar(auth()->id())) {
            return response()->json(['msg' => 'Você não tem permissão para aprovar esta solicitação'], 403);
        }

        try {
            DB::beginTransaction();

            $mudanca_cargo = MudancaCargo::find($dados['id']);

            $mudanca_cargo->update([
                'aprovacao_extra_id' => auth()->id(),
                'data_aprovacao_extra' => (new DataHora())->dataHoraInsert(),
                'obs_aprovacao_extra' => $dados['obs_aprovacao_extra'] ?? null,
                'status_aprovacao_extra' => $dados['status_aprovacao_extra'],
            ]);

            DB::commit();

            // Notifica próxima etapa + etapas anteriores (recursivo)
            JobNotificacaoRecursiva::dispatch($mudanca_cargo->id, $mudanca_cargo->empresa_id);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao aprovar Solicitação de Mudança de Cargo - Aprovação Extra: {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
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

            if ($dados['status_aprovacao_rh'] === 'aprovado') {

                $admissao = Admissao::find($dados['admissao_id']);
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

            DB::commit();

            // Notifica etapas anteriores + finalização (recursivo)
            JobNotificacaoRecursiva::dispatch($mudancaCargo->id, $mudancaCargo->empresa_id);

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

        // Busca configuração de aprovação extra ativa
        $config = AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, 'mudanca_cargo');
        $podeAprovarExtra = false;
        $nomeAprovacaoExtra = '';

        if ($config) {
            $podeAprovarExtra = $config->podeAprovar(auth()->id());
            $nomeAprovacaoExtra = $config->nome_aprovacao;

            // Log para debug
            \Log::info('MudancaCargo - Verificação de aprovação extra', [
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
                'habilidades' => auth()->user()->listaDeHabilidades(),
                'config_id' => $config->id,
                'usuarios_autorizados' => $config->usuarios_autorizados,
                'pode_aprovar_extra' => $podeAprovarExtra
            ]);
        }

        // Mapear itens com dados mínimos de aprovadores (id,nome já vêm do filtro) e nomes/datas para o frontend
        $itens = collect($resultado->items())->map(function ($item) {
            $item->aprovacao_extra_nome = $item->AprovacaoExtra ? $item->AprovacaoExtra->nome : '';
            return $item;
        })->toArray();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $itens,
                'periodo' => $periodo,
                'aprovar_por_gestor' => auth()->user()->can('privilegio_aprovar_por_gestor'),
                'aprovar_por_rh' => auth()->user()->can('privilegio_aprovar_por_rh'),
                'pode_aprovar_extra' => $podeAprovarExtra,
                'tem_aprovacao_extra' => $config ? true : false,
                'nome_aprovacao_extra' => $nomeAprovacaoExtra,
                'mimes' => Arquivo::MIMEAPENASIMAGENSPDF
            ]
        ]);
    }

    public function filtro(Request $request)
    {
        $user = auth()->user();
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
            'AprovacaoExtra:id,nome',
            'RhAprovacao:id,nome',
            'QuemDeletou:id,nome',
            'VagaAbertaNova',
            'Colaborador:id,nome,login,tipo,ativo'
        )->where('empresa_id', $user->empresa_id);

        $filterApplier = new \App\Services\MudancaCargo\MudancaCargoFilterApplier($request->all(), $user);
        $filterApplier->apply($resultado);

        return $resultado;
    }

    //Excel
    public function export(Request $request)
    {
        $filtros = $request->all();
        $filtros['_full_export_access'] = auth()->user()->can('privilegio_gestao_rh')
            || auth()->user()->can('privilegio_aprovar_por_rh')
            || auth()->user()->can('privilegio_aprovar_rh');

        $nomeArquivo = 'mudanca_cargo_' . rand(1000, 9999) . '_' . date('YmdHis') . '.csv';
        JobMudaCargoPrevistaExportaExcel::dispatch(auth()->id(), 'Planejamento - Movimentação - Mudança de Cargo', $nomeArquivo, $filtros);
        return response()->json(['msg' => 'Estamos gerando seu arquivo, assim que finalizado você será notificado.']);
    }
}
