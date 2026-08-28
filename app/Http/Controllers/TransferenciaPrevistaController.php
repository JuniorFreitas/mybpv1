<?php

namespace App\Http\Controllers;

use App\Jobs\Movimentacao\TransferenciaPrevista\JobNotificacaoRecursiva;
use App\Jobs\Movimentacao\TransferenciaPrevista\JobTransferenciaPrevistaExportaExcel;
use App\Models\AprovacaoExtraConfig;
use App\Models\Arquivo;
use App\Models\CentroCusto;
use App\Models\LogHistorico;
use App\Models\TransferenciaPrevista;
use App\Services\CentroCusto\CentroCustoGestorResolverService;
use App\Services\TransferenciaPrevista\TransferenciaPrevistaFluxoAprovacaoService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use MasterTag\DataHora;

class TransferenciaPrevistaController extends Controller
{
    public function __construct(
        private readonly TransferenciaPrevistaFluxoAprovacaoService $fluxoAprovacaoService,
        private readonly CentroCustoGestorResolverService $gestorResolverService
    ) {
    }

    public function store(Request $request)
    {
        $dados = $request->input();
        $dados['user_id'] = auth()->id();
        $dados['empresa_id'] = auth()->user()->empresa_id;
        $dados['centro_custo_origem_id'] = $this->normalizarCentroCustoOrigemId($dados['centro_custo_origem_id'] ?? null);
        unset($dados['gestor_id'], $dados['gestor_destino_id']);

        $dadosValidados = \Validator::make(
            $dados,
            [
                'centro_custo_origem_id' => [
                    'required',
                    'integer',
                    Rule::exists('centro_custos', 'id')->where('empresa_id', auth()->user()->empresa_id),
                ],
                'centro_custo_destino_id' => 'required',
                'colaborador_id' => 'required',
            ]
        );
        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao Solicitar Transferência',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $transferenciaPrevista = new TransferenciaPrevista($dados);
            $this->fluxoAprovacaoService->aplicarFluxoGestores($transferenciaPrevista, (int) auth()->id());
            $transferenciaPrevista->save();

            $this->processarAnexos($transferenciaPrevista, $dados);

            DB::commit();

            Log::info('transferencia.criada', [
                'transferencia_id' => $transferenciaPrevista->id,
                'centro_custo_origem_id' => $transferenciaPrevista->centro_custo_origem_id,
                'centro_custo_destino_id' => $transferenciaPrevista->centro_custo_destino_id,
                'gestor_origem_id' => $transferenciaPrevista->gestor_id,
                'gestor_destino_id' => $transferenciaPrevista->gestor_destino_id,
            ]);

            JobNotificacaoRecursiva::dispatch($transferenciaPrevista->id, $transferenciaPrevista->empresa_id);

            return response()->json('', 201);
        } catch (DomainException $e) {
            DB::rollBack();
            return response()->json(['msg' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = "erro ao salvar Solicitação de Transferência:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function edit(TransferenciaPrevista $transferenciaPrevista)
    {
        $transferenciaPrevista->loadMissing([
            'Anexos',
            'Colaborador.Feedback.Admissao',
            'GestorOrigem',
            'GestorDestino',
            'QuemAprovouGestorDestino',
            'UserAprovacao',
        ]);

        $transferenciaPrevista->autocomplete_label_colaborador = $transferenciaPrevista->Colaborador ? $transferenciaPrevista->Colaborador->nome : '';
        $transferenciaPrevista->autocomplete_label_colaborador_anterior = $transferenciaPrevista->autocomplete_label_colaborador;
        $transferenciaPrevista->label_gestor_origem = $transferenciaPrevista->GestorOrigem?->nome ?? 'Não informado';
        $transferenciaPrevista->label_gestor_destino = $transferenciaPrevista->GestorDestino?->nome ?? 'Não informado';
        $transferenciaPrevista->anexosDel = [];

        $admissao = $transferenciaPrevista->Colaborador?->Feedback?->Admissao;
        $transferenciaPrevista->centro_custo_id = $admissao?->centro_custo_id;

        $config = AprovacaoExtraConfig::getConfigAtiva($transferenciaPrevista->empresa_id, 'transferencia');
        $transferenciaPrevista->tem_aprovacao_extra = (bool) $config;
        $transferenciaPrevista->pode_aprovar_extra = $config ? $config->podeAprovar(auth()->id()) : false;
        $transferenciaPrevista->nome_aprovacao_extra = $config ? $config->nome_aprovacao : '';
        $transferenciaPrevista->pode_aprovar_gestor_origem = $this->podeAprovarGestorOrigem($transferenciaPrevista);
        $transferenciaPrevista->pode_aprovar_gestor_destino = $this->podeAprovarGestorDestino($transferenciaPrevista);

        return $transferenciaPrevista;
    }

    public function update(Request $request, TransferenciaPrevista $transferenciaPrevista)
    {
        $dados = $request->input();
        $dados['user_id'] = auth()->user()->id;
        $dados['centro_custo_origem_id'] = $this->normalizarCentroCustoOrigemId($dados['centro_custo_origem_id'] ?? null);
        unset($dados['gestor_id'], $dados['gestor_destino_id'], $dados['fluxo_gestores_automatico']);

        $dadosValidados = \Validator::make(
            $dados,
            [
                'centro_custo_origem_id' => [
                    'required',
                    'integer',
                    Rule::exists('centro_custos', 'id')->where('empresa_id', auth()->user()->empresa_id),
                ],
                'centro_custo_destino_id' => 'required',
                'colaborador_id' => 'required',
            ]
        );
        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao atualizar Solicitação de Transferência',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $centroMudou = (int) $transferenciaPrevista->centro_custo_origem_id !== (int) ($dados['centro_custo_origem_id'] ?? 0)
                || (int) $transferenciaPrevista->centro_custo_destino_id !== (int) ($dados['centro_custo_destino_id'] ?? 0);

            $transferenciaPrevista->fill($dados);

            if ($transferenciaPrevista->fluxo_gestores_automatico && $centroMudou && !$transferenciaPrevista->status_aprovacao) {
                $this->fluxoAprovacaoService->aplicarFluxoGestores($transferenciaPrevista, (int) auth()->id());
            } elseif (!$transferenciaPrevista->fluxo_gestores_automatico && $centroMudou && !$transferenciaPrevista->status_aprovacao) {
                $transferenciaPrevista->fluxo_gestores_automatico = true;
                $this->fluxoAprovacaoService->aplicarFluxoGestores($transferenciaPrevista, (int) auth()->id());
            }

            $transferenciaPrevista->save();
            DB::commit();

            return response()->json('', 201);
        } catch (DomainException $e) {
            DB::rollBack();
            return response()->json(['msg' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = "erro ao atualizar Solicitação de Transferência:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    private function normalizarCentroCustoOrigemId(mixed $valor): ?int
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        return (int) $valor;
    }

    public function aprovar(Request $request, TransferenciaPrevista $transferenciaPrevista)
    {
        if (!$this->podeAprovarGestorOrigem($transferenciaPrevista)) {
            return response()->json(['msg' => 'Você não tem permissão para aprovar esta etapa'], 403);
        }

        $dados = $request->input();

        try {
            DB::beginTransaction();

            $transferenciaPrevista = TransferenciaPrevista::query()
                ->whereKey($transferenciaPrevista->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$this->podeAprovarGestorOrigem($transferenciaPrevista)) {
                DB::rollBack();
                return response()->json(['msg' => 'Você não tem permissão para aprovar esta etapa'], 403);
            }

            $transferenciaPrevista->update([
                'user_aprovacao_id' => auth()->id(),
                'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                'obs_aprovacao' => $dados['obs_aprovacao'] ?? null,
                'status_aprovacao' => $dados['status_aprovacao'],
            ]);

            $this->processarAnexos($transferenciaPrevista, $dados);
            DB::commit();

            Log::info('transferencia.aprovacao_gestor_origem.' . $dados['status_aprovacao'], [
                'transferencia_id' => $transferenciaPrevista->id,
                'aprovador_id' => auth()->id(),
            ]);

            JobNotificacaoRecursiva::dispatch($transferenciaPrevista->id, $transferenciaPrevista->empresa_id);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = "error ao aprovar SOLICITAÇÃO DE TRANSFERÊNCIA:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function aprovarGestorDestino(Request $request, TransferenciaPrevista $transferenciaPrevista)
    {
        if (!$this->podeAprovarGestorDestino($transferenciaPrevista)) {
            return response()->json(['msg' => 'Você não tem permissão para aprovar esta etapa'], 403);
        }

        $dados = $request->input();

        try {
            DB::beginTransaction();

            $transferenciaPrevista = TransferenciaPrevista::query()
                ->whereKey($transferenciaPrevista->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$this->podeAprovarGestorDestino($transferenciaPrevista)) {
                DB::rollBack();
                return response()->json(['msg' => 'Você não tem permissão para aprovar esta etapa'], 403);
            }

            $transferenciaPrevista->update([
                'user_aprovacao_gestor_destino_id' => auth()->id(),
                'data_aprovacao_gestor_destino' => (new DataHora())->dataHoraInsert(),
                'obs_aprovacao_gestor_destino' => $dados['obs_aprovacao_gestor_destino'] ?? null,
                'status_aprovacao_gestor_destino' => $dados['status_aprovacao_gestor_destino'],
            ]);

            $this->processarAnexos($transferenciaPrevista, $dados);
            DB::commit();

            Log::info('transferencia.aprovacao_gestor_destino.' . $dados['status_aprovacao_gestor_destino'], [
                'transferencia_id' => $transferenciaPrevista->id,
                'aprovador_id' => auth()->id(),
            ]);

            JobNotificacaoRecursiva::dispatch($transferenciaPrevista->id, $transferenciaPrevista->empresa_id);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = "error ao aprovar gestor destino TRANSFERÊNCIA:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function aprovarExtra(Request $request, TransferenciaPrevista $transferenciaPrevista)
    {
        if (!$this->gestoresConcluidosParaProximasEtapas($transferenciaPrevista)) {
            return response()->json(['msg' => 'Aguardando aprovação dos gestores de origem e destino'], 403);
        }

        $config = AprovacaoExtraConfig::getConfigAtiva($transferenciaPrevista->empresa_id, 'transferencia');

        if (!$config || !$config->podeAprovar(auth()->id())) {
            return response()->json(['msg' => 'Você não tem permissão para aprovar esta etapa'], 403);
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

            $this->processarAnexos($transferenciaPrevista, $dados);
            DB::commit();

            JobNotificacaoRecursiva::dispatch($transferenciaPrevista->id, $transferenciaPrevista->empresa_id);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = "erro ao aprovar APROVAÇÃO EXTRA - TRANSFERÊNCIA:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function aprovarRH(Request $request, TransferenciaPrevista $transferenciaPrevista)
    {
        if (!auth()->user()->can('privilegio_gestao_rh') && !auth()->user()->can('privilegio_aprovar_por_rh') && !auth()->user()->can('privilegio_aprovar_rh')) {
            abort(403, 'This action is unauthorized.');
        }

        if (!$this->gestoresConcluidosParaProximasEtapas($transferenciaPrevista)) {
            return response()->json(['msg' => 'Aguardando aprovação dos gestores de origem e destino'], 403);
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
                    $centroCustoDestino = $transferenciaPrevista->CentroCustoDestino;
                    $filiaisAtivas = $centroCustoDestino && $centroCustoDestino->Filiais ? $centroCustoDestino->Filiais : collect();
                    $temFilial = $filiaisAtivas->count() > 0;

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

            $this->processarAnexos($transferenciaPrevista, $dados);

            LogHistorico::createLog(
                $transferenciaPrevista->Colaborador->Feedback->id,
                'Solicitação foi ' . $dados['status_aprovacao_rh'] . ' pelo RH na mudança Centro de Custo na solicitação de transferência #' . $transferenciaPrevista->id
            );

            DB::commit();

            JobNotificacaoRecursiva::dispatch($transferenciaPrevista->id, $transferenciaPrevista->empresa_id);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = "error ao aprovar solicitação RH:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function gestorResponsavel(CentroCusto $centrocusto)
    {
        if ((int) $centrocusto->empresa_id !== (int) auth()->user()->empresa_id) {
            abort(404);
        }

        $principal = $this->gestorResolverService->getGestorPrincipal($centrocusto->id);
        $substituto = $this->gestorResolverService->getGestorSubstituto($centrocusto->id);

        $resolvido = null;
        try {
            $resolvido = $this->gestorResolverService->resolverAprovador($centrocusto->id, (int) auth()->id());
        } catch (DomainException) {
            $resolvido = $principal;
        }

        return response()->json([
            'centro_custo_id' => $centrocusto->id,
            'label' => $centrocusto->label,
            'gestor_principal' => $principal ? ['id' => $principal->id, 'nome' => $principal->nome] : null,
            'gestor_substituto' => $substituto ? ['id' => $substituto->id, 'nome' => $substituto->nome] : null,
            'gestor_resolvido' => $resolvido ? ['id' => $resolvido->id, 'nome' => $resolvido->nome] : null,
        ]);
    }

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->pages);
        $config = AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, 'transferencia');
        $userId = auth()->id();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'aprovar_por_gestor' => auth()->user()->can('privilegio_aprovar_por_gestor'),
                'aprovar_por_rh' => auth()->user()->can('privilegio_gestao_rh') || auth()->user()->can('privilegio_aprovar_por_rh') || auth()->user()->can('privilegio_aprovar_rh'),
                'tem_aprovacao_extra' => (bool) $config,
                'pode_aprovar_extra' => $config ? $config->podeAprovar($userId) : false,
                'nome_aprovacao_extra' => $config ? $config->nome_aprovacao : '',
                'usuario_logado_id' => $userId,
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
            'GestorOrigem:id,nome',
            'GestorDestino:id,nome',
            'QuemAprovouGestorDestino:id,nome',
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
            }

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = "error ao aprovar solicitação em massa:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    private function podeAprovarGestorOrigem(TransferenciaPrevista $transferenciaPrevista): bool
    {
        if ($this->fluxoAprovacaoService->isFluxoLegado($transferenciaPrevista)) {
            return auth()->user()->can('privilegio_aprovar_por_gestor')
                && !$transferenciaPrevista->status_aprovacao;
        }

        return $this->fluxoAprovacaoService->podeAprovarGestorOrigem($transferenciaPrevista, auth()->user());
    }

    private function podeAprovarGestorDestino(TransferenciaPrevista $transferenciaPrevista): bool
    {
        if ($this->fluxoAprovacaoService->isFluxoLegado($transferenciaPrevista)) {
            return false;
        }

        return $this->fluxoAprovacaoService->podeAprovarGestorDestino($transferenciaPrevista, auth()->user());
    }

    private function gestoresConcluidosParaProximasEtapas(TransferenciaPrevista $transferenciaPrevista): bool
    {
        if ($this->fluxoAprovacaoService->isFluxoLegado($transferenciaPrevista)) {
            return $transferenciaPrevista->status_aprovacao === 'aprovado';
        }

        return $this->fluxoAprovacaoService->gestoresEtapasConcluidas($transferenciaPrevista);
    }

    private function processarAnexos(TransferenciaPrevista $transferenciaPrevista, array $dados): void
    {
        if (isset($dados['anexosDel'])) {
            foreach ($dados['anexosDel'] as $id_anexo) {
                $arquivo = Arquivo::find($id_anexo);
                if ($arquivo) {
                    $arquivo->excluir();
                }
            }
        }

        if (isset($dados['anexos'])) {
            foreach ($dados['anexos'] as $anexo) {
                $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                if ($arquivo) {
                    $arquivo->temporario = false;
                    $arquivo->chave = '';
                    $arquivo->save();
                    $transferenciaPrevista->Anexos()->attach($arquivo->id);
                }
            }
        }
    }
}
