<?php

namespace App\Http\Controllers;

use App\Jobs\Movimentacao\ValorExtraPrevista\JobNotificacaoRecursiva;
use App\Jobs\Movimentacao\ValorExtraPrevista\JobValorExtraPrevistaExportaExcel;
use App\Models\Arquivo;
use App\Models\CentroCusto;
use App\Models\LogHistorico;
use App\Models\ValorExtraPrevista;
use App\Services\Planejamento\Movimentacao\LotacaoLabelResolver;
use App\Services\ValorExtraPrevista\ValorExtraPrevistaEditPayloadMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class ValorExtraPrevistaController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $dados = $request->input();

        $dados['user_id'] = auth()->user()->id;
        $dadosValidados = \Validator::make(
            $dados,
            [
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'centro_custo_filial_id' => 'required_if:filial,true',
                'tipo' => 'required',
                'periodo_dias' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Valor Extra',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            DB::beginTransaction();
            $valorExtraPrevista = ValorExtraPrevista::create($dados);

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
                        $valorExtraPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }
            DB::commit();
            JobNotificacaoRecursiva::dispatch($valorExtraPrevista->id, $valorExtraPrevista->empresa_id);
            return response()->json('', 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao salvar Solicitação de Valor Extra:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\ValorExtraPrevista $valorExtraPrevista
     * @return ValorExtraPrevista|\Illuminate\Http\Response
     */
    public function edit(ValorExtraPrevista $valorExtraPrevista)
    {
        $mapper = new ValorExtraPrevistaEditPayloadMapper();

        $item = ValorExtraPrevista::query()
            ->select(ValorExtraPrevistaEditPayloadMapper::VALOR_EXTRA_COLUMNS)
            ->with([
                'Colaborador:' . implode(',', ValorExtraPrevistaEditPayloadMapper::USER_COLUMNS),
                'GestorAprovacao:' . implode(',', ValorExtraPrevistaEditPayloadMapper::USER_COLUMNS),
                'UserAprovacao:' . implode(',', ValorExtraPrevistaEditPayloadMapper::USER_COLUMNS),
                'RhAprovacao:' . implode(',', ValorExtraPrevistaEditPayloadMapper::USER_COLUMNS),
                'AprovacaoExtra:' . implode(',', ValorExtraPrevistaEditPayloadMapper::USER_COLUMNS),
                'Anexos' => function ($query) {
                    $query->select(ValorExtraPrevistaEditPayloadMapper::ANEXO_COLUMNS);
                },
            ])
            ->whereKey($valorExtraPrevista->id)
            ->firstOrFail();

        return response()->json($mapper->map($item));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ValorExtraPrevista $valorExtraPrevista
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, ValorExtraPrevista $valorExtraPrevista)
    {
        $dados = $request->input();

        $dadosValidados = \Validator::make(
            $dados,
            [
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'tipo' => 'required',
                'periodo_dias' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Valor Extra',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            DB::beginTransaction();
            $valorExtraPrevista->update($dados);
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
                        $valorExtraPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }
            DB::commit();
            return response()->json('', 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao salvar Solicitação de Valor Extra:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }


    public function aprovar(Request $request, ValorExtraPrevista $valorExtraPrevista)
    {
        $this->authorize('privilegio_aprovar_por_gestor');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $valorExtraPrevista->update([
                'user_aprovacao_id' => auth()->id(),
                'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                'obs_aprovacao' => $dados['obs_aprovacao'],
                'status_aprovacao' => $dados['status_aprovacao'],
            ]);
            DB::commit();

            JobNotificacaoRecursiva::dispatch($valorExtraPrevista->id, $valorExtraPrevista->empresa_id);
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar VALOR EXTRA:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            //            return response()->json(['msg' => $msg], 400);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function aprovarExtra(Request $request, ValorExtraPrevista $valorExtraPrevista)
    {
        $dados = $request->input();

        // Busca configuração ativa de aprovação extra para valor extra
        $config = \App\Models\AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, 'valor_extra');

        if (!$config) {
            return response()->json(['msg' => 'Não existe configuração de aprovação extra ativa'], 400);
        }

        // Verifica se o usuário pode aprovar
        if (!$config->podeAprovar(auth()->id())) {
            return response()->json(['msg' => 'Você não tem permissão para aprovar esta solicitação'], 403);
        }

        try {
            DB::beginTransaction();

            $valorExtraPrevista->update([
                'aprovacao_extra_id' => auth()->id(),
                'data_aprovacao_extra' => (new DataHora())->dataHoraInsert(),
                'obs_aprovacao_extra' => $dados['obs_aprovacao_extra'] ?? null,
                'status_aprovacao_extra' => $dados['status_aprovacao_extra'],
            ]);

            DB::commit();

            JobNotificacaoRecursiva::dispatch($valorExtraPrevista->id, $valorExtraPrevista->empresa_id);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao aprovar Valor Extra - Aprovação Extra: {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function aprovarRH(Request $request, ValorExtraPrevista $valorExtraPrevista)
    {
        $this->authorize('privilegio_aprovar_por_rh');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $valorExtraPrevista->update([
                'rh_aprovacao_id' => auth()->id(),
                'status_aprovacao_rh' => $dados['status_aprovacao_rh'],
                'obs_rh' => $dados['obs_rh'],
                'data_aprovacao_rh' => (new DataHora())->dataHoraInsert()
            ]);

            LogHistorico::createLog(
                $valorExtraPrevista->Colaborador->FeedBack->id,
                'Solicitação foi ' . $dados['status_aprovacao_rh'] . ' pelo RH na solicitação de valor extra ' . $valorExtraPrevista->id
            );

            DB::commit();

            JobNotificacaoRecursiva::dispatch($valorExtraPrevista->id, $valorExtraPrevista->empresa_id);
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar VALOR EXTRA por RH:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => $msg], 400);
            //            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }


    public function atualizar(Request $request)
    {

        $resultado = $this->filtro($request)->paginate($request->pages);
        $empresaMatriz = auth()->user()->Empresa;
        $itens = collect($resultado->items())->map(function ($item) use ($empresaMatriz) {
            $item->lotacao = LotacaoLabelResolver::forCentroCustoFilialFlags(
                $item->filial,
                $item->CentroCustoFilial,
                $empresaMatriz
            );

            return $item;
        })->values();

        // Busca configuração de aprovação extra ativa
        $config = \App\Models\AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, 'valor_extra');
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
                'mimes' => Arquivo::MIMEAPENASIMAGENSPDF,
                'cc' => (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id),
            ]
        ]);
    }

    public function filtro(Request $request)
    {
        $user = auth()->user();
        $resultado = ValorExtraPrevista::query()
            ->select([
                'id',
                'colaborador_id',
                'centro_custo_id',
                'filial',
                'centro_custo_filial_id',
                'tipo',
                'periodo_dias',
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
                'CentroCusto:id,label',
                'CentroCustoFilial:id,cliente_filial_id',
                'CentroCustoFilial.Filial:id,dados',
                'Colaborador:id,nome',
                'UserCadastrou:id,nome',
                'UserAprovacao:id,nome',
                'UserAprovacaoExtra:id,nome',
                'RhAprovacao:id,nome',
            ])
            ->where('empresa_id', $user->empresa_id);

        $filterApplier = new \App\Services\ValorExtraPrevista\ValorExtraPrevistaFilterApplier($request->all(), $user);
        $filterApplier->apply($resultado);

        return $resultado;
    }

    public function export(Request $request)
    {
        $filtros = $request->all();
        $filtros['_full_export_access'] = auth()->user()->can('privilegio_gestao_rh')
            || auth()->user()->can('privilegio_aprovar_por_rh')
            || auth()->user()->can('privilegio_aprovar_rh');

        $nomeArquivo = 'valor_extra_prevista_' . rand(1000, 9999) . '_' . date('YmdHis') . '.csv';
        JobValorExtraPrevistaExportaExcel::dispatch(auth()->id(), 'Planejamento - Movimentação - Liderança de Pessoal e Valor Extra', $nomeArquivo, $filtros);
        return response()->json(['msg' => 'Estamos gerando seu arquivo, assim que finalizado você será notificado.']);
    }

    public function atualizacaoStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            foreach ($request->selecionados[0] as $selecionado) {

                $dados = ValorExtraPrevista::find($selecionado);

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
