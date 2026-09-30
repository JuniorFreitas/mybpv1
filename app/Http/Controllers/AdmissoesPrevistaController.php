<?php

namespace App\Http\Controllers;

use App\Jobs\Movimentacao\AdmissaoPrevista\JobAdmissaoPrevistaExportaExcel;
use App\Jobs\Movimentacao\AdmissaoPrevista\JobNotificacaoRecursiva;
use App\Models\Admissao;
use App\Models\AdmissoesPrevista;
use App\Models\AprovacaoExtraConfig;
use App\Models\Arquivo;
use App\Models\CentroCusto;
use App\Services\AdmissoesPrevista\AdmissoesPrevistaEditPayloadMapper;
use App\Services\AdmissoesPrevista\AdmissoesPrevistaFilterApplier;
use App\Services\Planejamento\Movimentacao\LotacaoLabelResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class AdmissoesPrevistaController extends Controller
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
        $dados['salario'] = $dados['salario_format'];
        $dados['user_id'] = auth()->user()->id;

        $dadosValidados = \Validator::make(
            $dados,
            [
                'centro_custo_id' => 'required',
                'centro_custo_filial_id' => 'required_if:filial,true',
                'tipo_contrato' => 'required|in:' . implode(',', Admissao::TODOS_TIPOS_ADMISSAO),
                'cargo_id' => 'required',
                'salario_format' => 'required',
                'nome_pessoa' => ['nullable', 'string', 'max:255'],
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Admissão',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            DB::beginTransaction();
            $admPrevista = AdmissoesPrevista::create($dados);
            if (isset($dados['anexos'])) {
                foreach ($dados['anexos'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $admPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }
            DB::commit();
            JobNotificacaoRecursiva::dispatch($admPrevista->id, $admPrevista->empresa_id);
            return response()->json('', 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao salvar Solicitação de Admissão:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome . " | " . auth()->user()->Empresa->razao_social;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\AdmissoesPrevista $admissoesPrevista
     * @return AdmissoesPrevista|\Illuminate\Http\Response
     */
    public function edit(AdmissoesPrevista $admissoesPrevista)
    {
        $mapper = new AdmissoesPrevistaEditPayloadMapper();

        $item = AdmissoesPrevista::query()
            ->select(AdmissoesPrevistaEditPayloadMapper::ADMISSAO_COLUMNS)
            ->with([
                'GestorAprovacao:' . implode(',', AdmissoesPrevistaEditPayloadMapper::USER_COLUMNS),
                'UserAprovacao:' . implode(',', AdmissoesPrevistaEditPayloadMapper::USER_COLUMNS),
                'UserAprovacaoExtra:' . implode(',', AdmissoesPrevistaEditPayloadMapper::USER_COLUMNS),
                'RhAprovacao:' . implode(',', AdmissoesPrevistaEditPayloadMapper::USER_COLUMNS),
                'Cargo:' . implode(',', AdmissoesPrevistaEditPayloadMapper::CARGO_COLUMNS),
                'Anexos' => function ($query) {
                    $query->select(AdmissoesPrevistaEditPayloadMapper::ANEXO_COLUMNS);
                },
            ])
            ->whereKey($admissoesPrevista->id)
            ->firstOrFail();

        return response()->json($mapper->map($item));
    }

    /**
     * Lista de tipos de contrato (mesmo do processo de admissão) para o filtro e campo "Tipo de contrato".
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function tiposContrato()
    {
        return response()->json(Admissao::TODOS_TIPOS_ADMISSAO);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\AdmissoesPrevista $admissoesPrevista
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function update(Request $request, AdmissoesPrevista $admissoesPrevista)
    {
        $dados = $request->input();
        $dados['salario'] = $dados['salario_format'];

        $dadosValidados = \Validator::make(
            $dados,
            [
                'centro_custo_id' => 'required',
                'tipo_contrato' => 'required|in:' . implode(',', Admissao::TODOS_TIPOS_ADMISSAO),
                'cargo_id' => 'required',
                'salario_format' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Admissão',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            DB::beginTransaction();
            $admissoesPrevista->update($dados);
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
                        $admissoesPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }
            DB::commit();
            return response()->json('', 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao salvar Solicitação de Admissão:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function aprovar(Request $request, AdmissoesPrevista $admissoesPrevista)
    {
        $this->authorize('privilegio_aprovar_por_gestor');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $admissoesPrevista->update([
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
                        $admissoesPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }

            DB::commit();

            \Log::info("=== DISPARANDO NOTIFICAÇÃO RECURSIVA - ADMISSÃO #{$admissoesPrevista->id} ===");
            \Log::info("Status: {$dados['status_aprovacao']}");

            // Dispara notificação recursiva que determina automaticamente os destinatários
            JobNotificacaoRecursiva::dispatch($admissoesPrevista->id, $admissoesPrevista->empresa_id);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar ADMISSÃO PREVISTA:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function aprovarRH(Request $request, AdmissoesPrevista $admissoesPrevista)
    {
        $this->authorize('privilegio_aprovar_por_rh');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $admissoesPrevista->update([
                'rh_aprovacao_id' => auth()->id(),
                'status_aprovacao_rh' => $dados['status_aprovacao_rh'],
                'obs_rh' => $dados['obs_rh'],
                'data_aprovacao_rh' => (new DataHora())->dataHoraInsert(),
            ]);

            DB::commit();

            \Log::info("=== DISPARANDO NOTIFICAÇÃO RECURSIVA - ADMISSÃO #{$admissoesPrevista->id} ===");
            \Log::info("Status RH: {$dados['status_aprovacao_rh']}");

            // Dispara notificação recursiva
            JobNotificacaoRecursiva::dispatch($admissoesPrevista->id, $admissoesPrevista->empresa_id);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar solicitação RH:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro. Por favor, tente novamente'], 400);
        }
    }

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->pages);
        $empresaMatriz = auth()->user()->Empresa;
        $itens = collect($resultado->items())->map(function ($item) use ($empresaMatriz) {
            $payload = $item->toArray();
            $payload['lotacao'] = LotacaoLabelResolver::forCentroCustoFilialFlags(
                $item->filial,
                $item->CentroCustoFilial,
                $empresaMatriz
            );
            $rawData = $item->getRawOriginal('data_admissao');
            $payload['data_admissao'] = $rawData
                ? (new DataHora($rawData))->dataCompleta()
                : '';
            $payload['data_aprovacao'] = $this->formatDateTimeListagem($item->getRawOriginal('data_aprovacao'));
            $payload['data_aprovacao_extra'] = $this->formatDateTimeListagem($item->getRawOriginal('data_aprovacao_extra'));
            $payload['data_aprovacao_rh'] = $this->formatDateTimeListagem($item->getRawOriginal('data_aprovacao_rh'));
            $payload['created_at'] = $this->formatDateTimeListagem($item->getRawOriginal('created_at'));

            return $payload;
        })->values();

        // Busca configuração de aprovação extra ativa
        $config = AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, 'admissao');
        $podeAprovarExtra = false;
        $nomeAprovacaoExtra = '';

        if ($config) {
            $podeAprovarExtra = $config->podeAprovar(auth()->id());
            $nomeAprovacaoExtra = $config->nome_aprovacao;
        }

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $itens,
                'aprovar_por_gestor' => auth()->user()->can('privilegio_aprovar_por_gestor'),
                'aprovar_por_rh' => auth()->user()->can('privilegio_aprovar_por_rh'),
                'pode_aprovar_extra' => $podeAprovarExtra,
                'tem_aprovacao_extra' => $config ? true : false,
                'nome_aprovacao_extra' => $nomeAprovacaoExtra,
                'cc' => (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id),
            ]
        ]);
    }

    public function atualizacaoStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            foreach ($request->selecionados[0] as $selecionado) {

                $dados = AdmissoesPrevista::find($selecionado);

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

    public function filtro(Request $request)
    {
        $user = auth()->user();
        $resultado = AdmissoesPrevista::query()
            ->select([
                'id',
                'nome_pessoa',
                'cargo_id',
                'centro_custo_id',
                'filial',
                'centro_custo_filial_id',
                'data_admissao',
                'tipo_contrato',
                'salario',
                'status_aprovacao',
                'status_aprovacao_rh',
                'status_aprovacao_extra',
                'data_aprovacao',
                'data_aprovacao_rh',
                'data_aprovacao_extra',
                'user_id',
                'user_aprovacao_id',
                'aprovacao_extra_id',
                'rh_aprovacao_id',
                'aprovado_via_script',
                'created_at',
                'updated_at',
                'empresa_id',
            ])
            ->with([
                'Cargo:id,nome',
                'CentroCusto:id,label',
                'CentroCustoFilial:id,cliente_filial_id',
                'CentroCustoFilial.Filial:id,dados',
                'UserCadastrou:id,nome',
                'UserAprovacao:id,nome',
                'UserAprovacaoExtra:id,nome',
                'RhAprovacao:id,nome',
            ])
            ->where('empresa_id', $user->empresa_id);

        $filterApplier = new AdmissoesPrevistaFilterApplier($request->all(), $user);
        $filterApplier->apply($resultado);

        return $resultado;
    }

    private function formatDateTimeListagem(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $data = new DataHora($value);

        return $data->dataCompleta() . ' às ' . $data->hora() . ':' . $data->minuto() . ':' . $data->segundo();
    }

    public function aprovarExtra(Request $request, AdmissoesPrevista $admissoesPrevista)
    {
        $dados = $request->input();

        // Verifica se o usuário pode aprovar
        $config = AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, 'admissao');
        if (!$config || !$config->podeAprovar(auth()->id())) {
            return response()->json(['msg' => 'Você não tem permissão para aprovar esta etapa'], 403);
        }

        try {
            DB::beginTransaction();

            $admissoesPrevista->update([
                'aprovacao_extra_id' => auth()->id(),
                'data_aprovacao_extra' => now(),
                'obs_aprovacao_extra' => $dados['obs_aprovacao_extra'] ?? null,
                'status_aprovacao_extra' => $dados['status_aprovacao_extra'],
            ]);

            DB::commit();

            \Log::info("=== DISPARANDO NOTIFICAÇÃO RECURSIVA - ADMISSÃO #{$admissoesPrevista->id} ===");
            \Log::info("Status Aprovação Extra: {$dados['status_aprovacao_extra']}");

            // Dispara notificação recursiva que determina automaticamente os destinatários
            JobNotificacaoRecursiva::dispatch($admissoesPrevista->id, $admissoesPrevista->empresa_id);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error("Erro ao aprovar extra admissão prevista: {$e->getMessage()}", [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user' => auth()->user()->nome
            ]);
            return response()->json(['msg' => 'Houve um erro. Por favor, tente novamente'], 400);
        }
    }

    // Notificações agora são gerenciadas pelo JobNotificacaoRecursiva
    // Não há mais necessidade de métodos separados de notificação

    //Excel
    public function export(Request $request)
    {
        $filtros = $request->all();
        $filtros['_full_export_access'] = auth()->user()->can('privilegio_gestao_rh')
            || auth()->user()->can('privilegio_aprovar_por_rh')
            || auth()->user()->can('privilegio_aprovar_rh');

        $nomeArquivo = 'admissao_prevista_' . rand(1000, 9999) . '_' . date('YmdHis') . '.csv';
        JobAdmissaoPrevistaExportaExcel::dispatch(auth()->id(), 'Planejamento - Movimentação - Admissão', $nomeArquivo, $filtros);
        return response()->json(['msg' => 'Estamos gerando seu arquivo, assim que finalizado você será notificado.']);
    }
}
