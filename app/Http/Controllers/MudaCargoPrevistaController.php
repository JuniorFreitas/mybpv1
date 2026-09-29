<?php

namespace App\Http\Controllers;


use App\Jobs\Movimentacao\MudaCargoPrevista\JobMudaCargoPrevistaAprovar;
use App\Jobs\Movimentacao\MudaCargoPrevista\JobMudaCargoPrevistaAprovarRH;
use App\Jobs\Movimentacao\MudaCargoPrevista\JobMudaCargoPrevistaExportaExcel;
use App\Models\Arquivo;
use App\Models\AprovacaoExtraConfig;
use App\Models\CentroCusto;
use App\Models\Cliente;
use App\Models\MudaCargoPrevista;
use App\Services\Cih\CihLotacaoResolver;
use App\Services\Planejamento\Movimentacao\LotacaoLabelResolver;
use App\Services\MudaCargoPrevista\MudaCargoPrevistaEditPayloadMapper;
use App\Services\MudaCargoPrevista\MudaCargoPrevistaFilterApplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class MudaCargoPrevistaController extends Controller
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
        $dados['salario_anterior'] = $dados['salario_anterior_format'];
        $dados['novo_salario'] = $dados['novo_salario_format'];
        $dados['user_id'] = auth()->user()->id;

        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'cargo_anterior_id' => 'required',
                'salario_anterior_format' => 'required',
                'novo_cargo_id' => 'required',
                'novo_salario_format' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Demissão',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

            try {
                DB::beginTransaction();
                $mudaCargoPrevista = MudaCargoPrevista::create($dados);
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
                            $mudaCargoPrevista->Anexos()->attach($arquivo->id);
                        }
                    }
                }
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Mudança de Cargo:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\MudaCargoPrevista $mudaCargoPrevista
     * @return MudaCargoPrevista|\Illuminate\Http\Response
     */
    public function edit(MudaCargoPrevista $mudaCargoPrevista)
    {
        $mapper = new MudaCargoPrevistaEditPayloadMapper();

        $item = MudaCargoPrevista::query()
            ->select(MudaCargoPrevistaEditPayloadMapper::MUDA_CARGO_COLUMNS)
            ->with([
                'Colaborador:' . implode(',', MudaCargoPrevistaEditPayloadMapper::USER_COLUMNS),
                'GestorAprovacao:' . implode(',', MudaCargoPrevistaEditPayloadMapper::USER_COLUMNS),
                'UserAprovacao:' . implode(',', MudaCargoPrevistaEditPayloadMapper::USER_COLUMNS),
                'AprovacaoExtra:' . implode(',', MudaCargoPrevistaEditPayloadMapper::USER_COLUMNS),
                'CargoAnterior:' . implode(',', MudaCargoPrevistaEditPayloadMapper::VAGA_COLUMNS),
                'NovoCargo:' . implode(',', MudaCargoPrevistaEditPayloadMapper::VAGA_COLUMNS),
                'Anexos' => function ($query) {
                    $query->select(MudaCargoPrevistaEditPayloadMapper::ANEXO_COLUMNS);
                },
            ])
            ->whereKey($mudaCargoPrevista->id)
            ->firstOrFail();

        return response()->json($mapper->map($item));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\MudaCargoPrevista $mudaCargoPrevista
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, MudaCargoPrevista $mudaCargoPrevista)
    {
        $dados = $request->input();
        $dados['salario_anterior'] = $dados['salario_anterior_format'];
        $dados['novo_salario'] = $dados['novo_salario_format'];
        $dados['user_id'] = auth()->user()->id;

        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'cargo_anterior_id' => 'required',
                'salario_anterior_format' => 'required',
                'novo_cargo_id' => 'required',
                'novo_salario_format' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Demissão',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $mudaCargoPrevista->update($dados);
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
                            $mudaCargoPrevista->Anexos()->attach($arquivo->id);
                        }
                    }
                }
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Mudança de Cargo:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\MudaCargoPrevista $mudaCargoPrevista
     * @return \Illuminate\Http\Response
     */
    public function destroy(MudaCargoPrevista $mudaCargoPrevista)
    {
        //
    }

    public function aprovarRH(Request $request, MudaCargoPrevista $mudaCargoPrevista)
    {
        $this->authorize('rh_aprova_movimentacao');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $mudaCargoPrevista->update([
                'user_rh_id' => auth()->id(),
                'resposta_rh' => $dados['resposta_rh'],
                'obs_rh' => $dados['obs_rh'],
                'data_aprovacao_rh' => (new DataHora())->dataHoraInsert(),
            ]);

            DB::commit();

            JobMudaCargoPrevistaAprovarRH::dispatch($mudaCargoPrevista);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar solicitação RH:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    public function aprovar(Request $request, MudaCargoPrevista $mudaCargoPrevista)
    {
        $this->authorize('privilegio_aprovar_por_gestor');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $mudaCargoPrevista->update([
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
                        $mudaCargoPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }
            DB::commit();
            JobMudaCargoPrevistaAprovar::dispatch($mudaCargoPrevista);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar Mudança de Cargo:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->pages);

        $config = AprovacaoExtraConfig::getConfigAtiva(auth()->user()->empresa_id, AprovacaoExtraConfig::TIPO_MUDANCA_CARGO);
        $podeAprovarExtra = false;
        $nomeAprovacaoExtra = '';

        if ($config) {
            $podeAprovarExtra = $config->podeAprovar(auth()->id());
            $nomeAprovacaoExtra = $config->nome_aprovacao;
        }

        $empresaId = (int) auth()->user()->empresa_id;
        $empresa = Cliente::query()
            ->select(['id', 'nome_fantasia', 'razao_social', 'cnpj'])
            ->find($empresaId);
        $lotacaoResolver = new CihLotacaoResolver($empresaId);

        $itens = $resultado->items();
        foreach ($itens as $item) {
            $item->lotacao = LotacaoLabelResolver::fromCentroCustoId(
                $empresaId,
                $item->centro_custo_id ? (int) $item->centro_custo_id : null,
                $empresa,
                $item->CentroCusto?->label,
                $lotacaoResolver
            );
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
        $resultado = MudaCargoPrevista::query()
            ->select([
                'id',
                'centro_custo_id',
                'colaborador_id',
                'cargo_anterior_id',
                'novo_cargo_id',
                'salario_anterior',
                'novo_salario',
                'user_id',
                'gestor_id',
                'user_aprovacao_id',
                'status_aprovacao',
                'data_aprovacao',
                'created_at',
                'updated_at',
            ])
            ->with([
                'CentroCusto:id,label',
                'CargoAnterior:id,nome',
                'NovoCargo:id,nome',
                'UserCadastrou:id,nome',
                'Colaborador:id,nome',
                'GestorAprovacao:id,nome',
                'UserAprovacao:id,nome',
            ]);

        (new MudaCargoPrevistaFilterApplier($request->all(), $user))->apply($resultado);

        return $resultado;
    }

    public function export(Request $request)
    {
        $filtros = $request->all();
        $filtros['_full_export_access'] = auth()->user()->can('privilegio_gestao_rh')
            || auth()->user()->can('privilegio_aprovar_por_rh')
            || auth()->user()->can('privilegio_aprovar_rh');

        $nomeArquivo = 'muda_cargo_prevista_' . rand(1000, 9999) . '_' . date('YmdHis') . '.csv';
        JobMudaCargoPrevistaExportaExcel::dispatch(
            auth()->id(),
            'Planejamento - Movimentação - Mudança de Cargo Prevista',
            $nomeArquivo,
            $filtros
        );

        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }
    public function atualizacaoStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            foreach ($request->selecionados[0] as $selecionado) {

                $dados = MudaCargoPrevista::find($selecionado);

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
