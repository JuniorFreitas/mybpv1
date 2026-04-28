<?php

namespace App\Http\Controllers;

use App\Jobs\Movimentacao\TransferenciaPrevista\JobNotificacaoRecursiva;
use App\Jobs\Movimentacao\TransferenciaPrevista\JobTransferenciaPrevistaExportaExcel;
use App\Models\AprovacaoExtraConfig;
use App\Models\Arquivo;
use App\Models\LogHistorico;
use App\Models\TransferenciaPrevista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class TransferenciaPrevistaController extends Controller
{
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

        $dadosValidados = \Validator::make(
            $dados,
            [
                'centro_custo_origem_id' => 'required',
                'centro_custo_destino_id' => 'required',
                'colaborador_id' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Admissão',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $transferenciaPrevista = TransferenciaPrevista::create($dados);
                if (isset($dados['anexos'])) {
                    foreach ($dados['anexos'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $transferenciaPrevista->Anexos()->attach($arquivo->id);
                        }
                    }
                }
                DB::commit();

                // Envia notificação para a próxima etapa (gestor)
                JobNotificacaoRecursiva::dispatch($transferenciaPrevista->id, $transferenciaPrevista->empresa_id);

                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Solicitação de Transferência:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\TransferenciaPrevista $transferenciaPrevista
     * @return \Illuminate\Http\Response
     */
    public function edit(TransferenciaPrevista $transferenciaPrevista)
    {
        $transferenciaPrevista->autocomplete_label_colaborador = $transferenciaPrevista->Colaborador ? $transferenciaPrevista->Colaborador->nome : '';
        $transferenciaPrevista->autocomplete_label_colaborador_anterior = $transferenciaPrevista->Colaborador ? $transferenciaPrevista->Colaborador->nome : '';

        $transferenciaPrevista->autocomplete_label_gestor_modal = $transferenciaPrevista->GestorAprovacao ? $transferenciaPrevista->GestorAprovacao->nome : '';
        $transferenciaPrevista->autocomplete_label_gestor_modal_anterior = $transferenciaPrevista->GestorAprovacao ? $transferenciaPrevista->GestorAprovacao->nome : '';
        $transferenciaPrevista->anexosDel = [];
        $transferenciaPrevista->load('Anexos');

        // Informações de aprovação extra
        $config = AprovacaoExtraConfig::getConfigAtiva($transferenciaPrevista->empresa_id, 'transferencia');
        $transferenciaPrevista->tem_aprovacao_extra = $config ? true : false;
        $transferenciaPrevista->pode_aprovar_extra = $config ? $config->podeAprovar(auth()->id()) : false;
        $transferenciaPrevista->nome_aprovacao_extra = $config ? $config->nome_aprovacao : '';

        return $transferenciaPrevista;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\TransferenciaPrevista $transferenciaPrevista
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, TransferenciaPrevista $transferenciaPrevista)
    {
        $dados = $request->input();
        $dados['user_id'] = auth()->user()->id;

        $dadosValidados = \Validator::make(
            $dados,
            [
                'centro_custo_origem_id' => 'required',
                'centro_custo_destino_id' => 'required',
                'colaborador_id' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Admissão',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $transferenciaPrevista->update($dados);
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao atualizar Solicitação de Transferência:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    public function aprovar(Request $request, TransferenciaPrevista $transferenciaPrevista)
    {
        $this->authorize('privilegio_aprovar_por_gestor');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $transferenciaPrevista->update([
                'user_aprovacao_id' => auth()->id(),
                'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                'obs_aprovacao' => $dados['obs_aprovacao'],
                'status_aprovacao' => $dados['status_aprovacao'],
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
                        $transferenciaPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }
            DB::commit();

            // Notifica próxima etapa (Aprovação Extra ou RH) + etapas anteriores
            JobNotificacaoRecursiva::dispatch($transferenciaPrevista->id, $transferenciaPrevista->empresa_id);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar SOLICITAÇÃO DE TRANSFERÊNCIA:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function aprovarExtra(Request $request, TransferenciaPrevista $transferenciaPrevista)
    {
        // Verifica se usu\u00e1rio pode aprovar (via config)
        $config = AprovacaoExtraConfig::getConfigAtiva($transferenciaPrevista->empresa_id, 'transferencia');

        if (!$config || !$config->podeAprovar(auth()->id())) {
            return response()->json(['msg' => 'Voc\u00ea n\u00e3o tem permiss\u00e3o para aprovar esta etapa'], 403);
        }

        $dados = $request->input();

        try {
            DB::beginTransaction();

            $transferenciaPrevista->update([
                'aprovacao_extra_id' => auth()->id(),
                'data_aprovacao_extra' => (new DataHora())->dataHoraInsert(),
                'obs_aprovacao_extra' => $dados['obs_aprovacao_extra'] ?? null,
                'status_aprovacao_extra' => $dados['status_aprovacao_extra'],
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
                        $transferenciaPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }

            DB::commit();

            // Notifica pr\u00f3xima etapa (RH) + etapas anteriores
            JobNotificacaoRecursiva::dispatch($transferenciaPrevista->id, $transferenciaPrevista->empresa_id);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao aprovar APROVA\u00c7\u00c3O EXTRA - TRANSFER\u00caNCIA:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function aprovarRH(Request $request, TransferenciaPrevista $transferenciaPrevista)
    {
        if (!auth()->user()->can('privilegio_gestao_rh') && !auth()->user()->can('privilegio_aprovar_por_rh') && !auth()->user()->can('privilegio_aprovar_rh')) {
            abort(403, 'This action is unauthorized.');
        }
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $transferenciaPrevista->update([
                'user_rh_id' => auth()->id(),
                'resposta_rh' => $dados['resposta_rh'],
                'obs_rh' => $dados['obs_rh'],
                'data_aprovacao_rh' => (new DataHora())->dataHoraInsert(),
            ]);

            if ($dados['status_aprovacao_rh'] === 'aprovado') {
                // Atualiza o centro de custo do colaborador na admissão
                $transferenciaPrevista->load([
                    'Colaborador.Feedback.Admissao',
                    'CentroCustoDestino' => function ($query) {
                        $query->with(['Filiais' => function ($q) {
                            $q->where('ativo', true);
                        }]);
                    }
                ]);

                if ($transferenciaPrevista->Colaborador &&
                    $transferenciaPrevista->Colaborador->Feedback &&
                    $transferenciaPrevista->Colaborador->Feedback->Admissao) {

                    $admissao = $transferenciaPrevista->Colaborador->Feedback->Admissao;

                    // Verifica se o centro de custo destino tem filiais ativas
                    $centroCustoDestino = $transferenciaPrevista->CentroCustoDestino;
                    $filiaisAtivas = $centroCustoDestino && $centroCustoDestino->Filiais ? $centroCustoDestino->Filiais : collect();
                    $temFilial = $filiaisAtivas->count() > 0;

                    // Se tem filial e foi informado o centro_custo_filial_id, usa ele, senão usa a primeira filial ativa
                    $centroCustoFilialId = null;
                    if ($temFilial) {
                        if (isset($dados['centro_custo_filial_id']) && $dados['centro_custo_filial_id']) {
                            $centroCustoFilialId = $dados['centro_custo_filial_id'];
                        } else {
                            $primeiraFilial = $filiaisAtivas->first();
                            $centroCustoFilialId = $primeiraFilial ? $primeiraFilial->id : null;
                        }
                    }

                    $admissao->update([
                        'centro_custo_id' => $transferenciaPrevista->centro_custo_destino_id,
                        'filial' => $temFilial,
                        'centro_custo_filial_id' => $centroCustoFilialId,
                    ]);
                }
            }

            if (isset($dados['anexosDel'])) {
                foreach ($dados['anexosDel'] as $id_anexo) {
                    $arquivo = Arquivo::find($id_anexo);
                    if ($arquivo) {
                        $arquivo->excluir();
                    }
                }
            }

            if (isset($dados['anexos'])) {
                foreach ($dados['anexos'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $transferenciaPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }

            LogHistorico::createLog(
                $transferenciaPrevista->Colaborador->Feedback->id,
                'Solicitação foi ' . $dados['status_aprovacao_rh'] . ' pelo RH na mudança Centro de Custo na solicitação de transferência #' . $transferenciaPrevista->id
            );

            DB::commit();

            // Notifica todas as etapas anteriores (aprovação final)
            JobNotificacaoRecursiva::dispatch($transferenciaPrevista->id, $transferenciaPrevista->empresa_id);

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

        // Busca configuração de aprovação extra
        $config = AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, 'transferencia');

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'aprovar_por_gestor' => auth()->user()->can('privilegio_aprovar_por_gestor'),
                'aprovar_por_rh' => auth()->user()->can('privilegio_gestao_rh') || auth()->user()->can('privilegio_aprovar_por_rh') || auth()->user()->can('privilegio_aprovar_rh'),
                'tem_aprovacao_extra' => $config ? true : false,
                'pode_aprovar_extra' => $config ? $config->podeAprovar(auth()->id()) : false,
                'nome_aprovacao_extra' => $config ? $config->nome_aprovacao : '',
            ]
        ]);
    }

    public function filtro(Request $request)
    {
        $user = auth()->user();
        $resultado = TransferenciaPrevista::with(
            'CentroCustoOrigem',
            'CentroCustoDestino',
            'QuemAprovou:id,nome',
            'UserCadastrou:id,nome',
            'GestorAprovacao:id,nome',
            'Colaborador',
            'UserAprovacao:id,nome',
            'UserAprovacaoExtra:id,nome',
            'RhAprovacao:id,nome'
        )->where('empresa_id', $user->empresa_id);

        $filterApplier = new \App\Services\TransferenciaPrevista\TransferenciaPrevistaFilterApplier($request->all(), $user);
        $filterApplier->apply($resultado);

        return $resultado;
    }

    public function export(Request $request)
    {
        $filtros = $request->all();
        $filtros['_full_export_access'] = auth()->user()->can('privilegio_gestao_rh')
            || auth()->user()->can('privilegio_aprovar_por_rh')
            || auth()->user()->can('privilegio_aprovar_rh');

        $nomeArquivo = 'transferencia_prevista_' . rand(1000, 9999) . '_' . date('YmdHis') . '.csv';
        JobTransferenciaPrevistaExportaExcel::dispatch(auth()->id(), 'Planejamento - Movimentação - Transferência', $nomeArquivo, $filtros);
        return response()->json(['msg' => 'Estamos gerando seu arquivo, assim que finalizado você será notificado.']);
    }

    public function atualizacaoStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            foreach ($request->selecionados[0] as $selecionado) {

                $dados = TransferenciaPrevista::find($selecionado);

                $dados->update([
                    'user_aprovacao_id' => auth()->id(),
                    'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                    'obs_aprovacao' => $request->obs_aprovacao,
                    'status_aprovacao' => $request->status_aprovacao,
                ]);

                DB::commit();
            }
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar solicitação em massa:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }
}
