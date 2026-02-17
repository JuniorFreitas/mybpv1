<?php

namespace App\Http\Controllers;

use App\Jobs\JobExportaRequisicaoVaga;
use App\Jobs\RequisicaoVaga\JobNotificacaoRecursiva;
use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\RequisicaoVagaMovimentacao;
use App\Models\User;
use App\Models\Vaga;
use App\Services\RequisicaoVaga\RequisicaoVagaFilterApplier;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class RequisicaoVagaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.planejamento.requisicao-vagas.index');
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        //        $this->authorize('');
        $dados = $request->input();
        $dados['previsao_inicio'] = $dados['imediata'] ? null : $dados['previsao_inicio'];
        $dados['outras_informacoes']['salario_valor'] = $dados['outras_informacoes']['salario'] == 'exceção' ? $dados['outras_informacoes']['salario_valor_format'] : null;
        $dados['user_id'] = auth()->user()->id;
        $dadosValidados = \Validator::make(
            $dados,
            [
                'centro_custo_id' => 'required',
                'cargo_id' => 'required',
                'area_id' => 'required',
                'quantidade' => 'required',
                'tipo_contratacao' => 'required',
                'prioridade' => 'required',
                'solicitante' => 'required',
                'outras_informacoes.posicao' => 'required',
                'outras_informacoes.processo' => 'required',
                'outras_informacoes.contrato' => 'required',
                'outras_informacoes.ppra' => 'required',
                'outras_informacoes.salario' => 'required',
                'outras_informacoes.beneficio' => 'required',
                'outras_informacoes.gestor_id' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Vaga',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $dados['empresa_id'] = auth()->user()->empresa_id;
                $dadosMerge = array_merge($dados, $dados['outras_informacoes'] ?? []);
                $dadosMerge['salario_valor'] = ($dados['outras_informacoes']['salario'] ?? '') == 'exceção' ? ($dados['outras_informacoes']['salario_valor_format'] ?? null) : null;
                unset($dadosMerge['outras_informacoes']);
                $requisicao = RequisicaoVagaMovimentacao::create($dadosMerge);
                DB::commit();
                JobNotificacaoRecursiva::dispatch($requisicao->id, $requisicao->empresa_id);
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Solicitação:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
                //                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\RequisicaoVagaMovimentacao $requisicaoVaga
     * @return \Illuminate\Http\Response
     */
    public function show(RequisicaoVagaMovimentacao $requisicaoVaga)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * Performático: 3 queries enxutas (apenas colunas necessárias), sem carregar relações completas.
     *
     * @param \App\Models\RequisicaoVagaMovimentacao $requisicaoVaga
     * @return \App\Models\RequisicaoVagaMovimentacao
     */
    public function edit(RequisicaoVagaMovimentacao $requisicaoVaga)
    {
        $empresaId = $requisicaoVaga->empresa_id;

        // 1) Um único select de usuários: só id e nome (gestor, aprovador, extra, rh)
        $userIds = array_filter([
            $requisicaoVaga->gestor_id,
            $requisicaoVaga->user_aprovacao_id,
            $requisicaoVaga->aprovacao_extra_id,
            $requisicaoVaga->rh_aprovacao_id,
        ]);
        $nomesUsuarios = $userIds
            ? User::where('empresa_id', $empresaId)
                ->whereIn('id', $userIds)
                ->pluck('nome', 'id')
                ->all()
            : [];

        // 2) Cargo: só o nome
        $nomeCargo = $requisicaoVaga->cargo_id
            ? (Vaga::where('id', $requisicaoVaga->cargo_id)->value('nome') ?? '')
            : '';

        // 3) Cliente: só razao_social e cnpj para o label
        $cliente = $requisicaoVaga->cliente_id
            ? Cliente::where('id', $requisicaoVaga->cliente_id)->select('razao_social', 'cnpj')->first()
            : null;
        $labelCliente = $cliente ? $cliente->razao_social . ' | ' . $cliente->cnpj : '';

        $requisicaoVaga->autocomplete_label_cargo_modal = $nomeCargo;
        $requisicaoVaga->autocomplete_label_cargo_modal_anterior = $nomeCargo;
        $requisicaoVaga->autocomplete_label_cliente_modal = $labelCliente;
        $requisicaoVaga->autocomplete_label_cliente_modal_anterior = $labelCliente;

        $nomeGestor = $nomesUsuarios[$requisicaoVaga->gestor_id] ?? '';
        $requisicaoVaga->autocomplete_label_gestor = $nomeGestor;
        $requisicaoVaga->autocomplete_label_gestor_anterior = $nomeGestor;

        $requisicaoVaga->user_aprovacao = $nomesUsuarios[$requisicaoVaga->user_aprovacao_id] ?? '';
        $requisicaoVaga->aprovacao_extra_nome = $nomesUsuarios[$requisicaoVaga->aprovacao_extra_id] ?? '';
        $requisicaoVaga->rh_aprovacao_nome = $nomesUsuarios[$requisicaoVaga->rh_aprovacao_id] ?? '';
        $requisicaoVaga->status_aprovacao = $requisicaoVaga->status_aprovacao ?: '';
        $requisicaoVaga->status_aprovacao_extra = $requisicaoVaga->status_aprovacao_extra ?: '';
        $requisicaoVaga->status_aprovacao_rh = $requisicaoVaga->status_aprovacao_rh ?: '';

        $outrasInformacoes = [
            'posicao' => $requisicaoVaga->posicao,
            'processo' => $requisicaoVaga->processo,
            'nome_indicacao' => $requisicaoVaga->nome_indicacao,
            'contrato' => $requisicaoVaga->contrato,
            'local_trabalho' => $requisicaoVaga->local_trabalho,
            'horario' => $requisicaoVaga->horario,
            'gestor_id' => $requisicaoVaga->gestor_id,
            'gestor' => $requisicaoVaga->gestor,
            'ppra' => $requisicaoVaga->ppra,
            'salario' => $requisicaoVaga->salario,
            'salario_valor' => $requisicaoVaga->salario_valor,
            'beneficio' => $requisicaoVaga->beneficio,
            'beneficio_excecao' => $requisicaoVaga->beneficio_excecao,
            'treinamento' => $requisicaoVaga->treinamento,
            'treinamento_excecao' => $requisicaoVaga->treinamento_excecao,
            'autocomplete_label_gestor' => $nomeGestor,
            'autocomplete_label_gestor_anterior' => $nomeGestor,
        ];
        $requisicaoVaga->outras_informacoes = $outrasInformacoes;

        return $requisicaoVaga;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\RequisicaoVagaMovimentacao $requisicaoVaga
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, RequisicaoVagaMovimentacao $requisicaoVaga)
    {
        $dados = $request->input();
        $dados['previsao_inicio'] = $dados['imediata'] ? null : $dados['previsao_inicio'];
        $dados['outras_informacoes']['salario_valor'] = ($dados['outras_informacoes']['salario'] ?? '') == 'exceção' ? ($dados['outras_informacoes']['salario_valor_format'] ?? null) : null;

        $dadosValidados = \Validator::make(
            $dados,
            [
                'centro_custo_id' => 'required',
                'cargo_id' => 'required',
                'area_id' => 'required',
                'quantidade' => 'required',
                'tipo_contratacao' => 'required',
                'prioridade' => 'required',
                'solicitante' => 'required',
                'outras_informacoes.posicao' => 'required',
                'outras_informacoes.processo' => 'required',
                'outras_informacoes.contrato' => 'required',
                'outras_informacoes.ppra' => 'required',
                'outras_informacoes.salario' => 'required',
                'outras_informacoes.beneficio' => 'required',
                'outras_informacoes.gestor_id' => 'required',
            ]
        );
        if ($dadosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao alterar Solicitação de vaga',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            DB::beginTransaction();
            $dadosMerge = array_merge($dados, $dados['outras_informacoes'] ?? []);
            $dadosMerge['salario_valor'] = ($dados['outras_informacoes']['salario'] ?? '') == 'exceção' ? ($dados['outras_informacoes']['salario_valor_format'] ?? null) : null;
            unset($dadosMerge['outras_informacoes']);
            $requisicaoVaga->update($dadosMerge);
            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao alterar Solicitação:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\RequisicaoVagaMovimentacao $requisicaoVaga
     * @return \Illuminate\Http\Response
     */
    public function destroy(RequisicaoVagaMovimentacao $requisicaoVaga)
    {
        //
    }

    public function aprovar(Request $request, RequisicaoVagaMovimentacao $requisicaoVaga)
    {
        $this->authorize('privilegio_aprovar_por_gestor');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $requisicaoVaga->update([
                'user_aprovacao_id' => auth()->user()->id,
                'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                'obs_aprovacao' => $dados['obs_aprovacao'],
                'status_aprovacao' => $dados['status_aprovacao'],
            ]);
            DB::commit();

            JobNotificacaoRecursiva::dispatch($requisicaoVaga->id, $requisicaoVaga->empresa_id);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao alterar Solicitação:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function aprovarExtra(Request $request, RequisicaoVagaMovimentacao $requisicaoVaga)
    {
        $dados = $request->input();

        // Busca configuração ativa de aprovação extra para requisição de vaga
        $config = \App\Models\AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, 'requisicao_vaga');

        if (!$config) {
            return response()->json(['msg' => 'Não existe configuração de aprovação extra ativa'], 400);
        }

        // Verifica se o usuário pode aprovar
        if (!$config->podeAprovar(auth()->id())) {
            return response()->json(['msg' => 'Você não tem permissão para aprovar esta solicitação'], 403);
        }

        try {
            DB::beginTransaction();

            $requisicaoVaga->update([
                'aprovacao_extra_id' => auth()->id(),
                'data_aprovacao_extra' => (new DataHora())->dataHoraInsert(),
                'obs_aprovacao_extra' => $dados['obs_aprovacao_extra'] ?? null,
                'status_aprovacao_extra' => $dados['status_aprovacao_extra'],
            ]);

            DB::commit();

            JobNotificacaoRecursiva::dispatch($requisicaoVaga->id, $requisicaoVaga->empresa_id);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao aprovar Requisição de Vaga - Aprovação Extra: {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function aprovarRh(Request $request, RequisicaoVagaMovimentacao $requisicaoVaga)
    {
        $this->authorize('privilegio_aprovar_por_rh');
        $dados = $request->input();

        // Valida status obrigatório
        $statusRh = $dados['status_aprovacao_rh'] ?? null;
        if (empty($statusRh) || !in_array($statusRh, ['aprovado', 'reprovado'], true)) {
            return response()->json(['msg' => 'Selecione Aprovar ou Reprovar na aprovação RH.'], 400);
        }

        // Valida se está na sequência correta de aprovações
        if ($requisicaoVaga->status_aprovacao !== 'aprovado') {
            return response()->json(['msg' => 'Solicitação não foi aprovada pelo gestor'], 400);
        }

        $config = \App\Models\AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, 'requisicao_vaga');
        if ($config && $requisicaoVaga->status_aprovacao_extra !== 'aprovado') {
            return response()->json(['msg' => 'Solicitação não foi aprovada pela aprovação extra'], 400);
        }

        try {
            DB::beginTransaction();

            $requisicaoVaga->update([
                'rh_aprovacao_id' => auth()->user()->id,
                'data_aprovacao_rh' => (new DataHora())->dataHoraInsert(),
                'obs_rh' => $dados['obs_rh'] ?? null,
                'status_aprovacao_rh' => $statusRh,
            ]);

            DB::commit();

            JobNotificacaoRecursiva::dispatch($requisicaoVaga->id, $requisicaoVaga->empresa_id);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao aprovar Requisição de Vaga - RH: {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->input('porPagina', $request->pages) ?? 50);

        // Busca configuração de aprovação extra ativa
        $config = \App\Models\AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, 'requisicao_vaga');
        $podeAprovarExtra = false;
        $nomeAprovacaoExtra = '';

        if ($config) {
            $podeAprovarExtra = $config->podeAprovar(auth()->id());
            $nomeAprovacaoExtra = $config->nome_aprovacao;
        }

        // Mapear items para incluir nomes de aprovadores, datas formatadas e área (frontend usa item.area.label)
        $itens = collect($resultado->items())->map(function ($item) {
            $item->rh_aprovacao_nome = $item->AprovacaoRh ? $item->AprovacaoRh->nome : '';
            // Data da aprovação (Gestor)
            $item->data_aprovacao_br = $item->data_aprovacao ? (new \MasterTag\DataHora($item->data_aprovacao))->dataCompleta() . ' ' . substr((new \MasterTag\DataHora($item->data_aprovacao))->horaCompleta(), 0, 5) : '';
            // Data da aprovação RH (para exibição no fluxo)
            $item->data_aprovacao_rh_br = $item->data_aprovacao_rh ? (new \MasterTag\DataHora($item->data_aprovacao_rh))->dataCompleta() . ' ' . substr((new \MasterTag\DataHora($item->data_aprovacao_rh))->horaCompleta(), 0, 5) : '';
            // Data da solicitação
            $item->created_at_br = $item->created_at ? (new \MasterTag\DataHora($item->created_at))->dataCompleta() . ' ' . substr((new \MasterTag\DataHora($item->created_at))->horaCompleta(), 0, 5) : '';
            // Expor Area como "area" (minúsculo) para o frontend (item.area.label)
            $item->area = $item->Area;
            return $item;
        })->toArray();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $itens,
                'aprovar_por_gestor' => auth()->user()->can('privilegio_aprovar_por_gestor'),
                'pode_aprovar_extra' => $podeAprovarExtra,
                'aprovar_por_rh' => auth()->user()->can('privilegio_aprovar_por_rh'),
                'tem_aprovacao_extra' => $config ? true : false,
                'nome_aprovacao_extra' => $nomeAprovacaoExtra,
            ]
        ]);
    }

    /**
     * Filtro inteiro (mesmo da exportação). Usa RequisicaoVagaFilterApplier = padrão CIH.
     */
    public function filtro(Request $request)
    {
        $user = auth()->user();
        $resultado = RequisicaoVagaMovimentacao::with(
            'CentroCusto',
            'Cargo',
            'Area',
            'UserCadastrou:id,nome',
            'UserAprovacao:id,nome',
            'AprovacaoExtra:id,nome',
            'AprovacaoRh:id,nome',
            'GestorContratacao:id,nome'
        )->where('empresa_id', $user->empresa_id);

        $filterApplier = new RequisicaoVagaFilterApplier($request->all(), $user);
        $filterApplier->apply($resultado);

        return $resultado;
    }

    /**
     * Exportação no padrão CIH: só envia filtros ao job. Toda a query e formatação é feita no backend (job em chunks).
     */
    public function export(Request $request)
    {
        $filtros = $request->all();
        // Repassar no request se o usuário tem acesso total (evita can() no job falhar e restringir demais)
        $filtros['_full_export_access'] = auth()->user()->can('privilegio_gestao_rh')
            || auth()->user()->can('privilegio_aprovar_por_rh')
            || auth()->user()->can('privilegio_aprovar_rh');

        $nameArquivo = 'requisicao_vaga_' . rand(1000, 9999) . '_' . date('YmdHis') . '.csv';
        JobExportaRequisicaoVaga::dispatch(auth()->id(), 'Requisição - Vaga', $nameArquivo, $filtros);
        return response()->json(['msg' => 'Estamos gerando seu arquivo, assim que finalizado você será notificado.']);
    }
    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, array_merge(Arquivo::MIMEAPENASIMAGENSPDF, Arquivo::MIMEAPENASDOCUMENTOS), Arquivo::DISCO_REQUISICAO_VAGA);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_REQUISICAO_VAGA, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_REQUISICAO_VAGA, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_REQUISICAO_VAGA, $arquivo);
    }

    // Notificações agora são gerenciadas pelo JobNotificacaoRecursiva
    // Não há mais necessidade de métodos separados de notificação
}
