<?php

namespace App\Http\Controllers;

use App\Jobs\Movimentacao\AdmissaoPrevista\JobAdmissaoPrevistaAprovar;
use App\Jobs\Movimentacao\AdmissaoPrevista\JobAdmissaoPrevistaAprovarRH;
use App\Jobs\Movimentacao\AdmissaoPrevista\JobAdmissaoPrevistaExportaExcel;
use App\Jobs\Movimentacao\AdmissaoPrevista\JobAdmissaoPrevistaStore;
use App\Models\AdmissoesPrevista;
use App\Models\Arquivo;
use App\Models\Cliente;
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

        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'centro_custo_filial_id' => 'required_if:filial,true',
                'tipo_contrato' => 'required',
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
            JobAdmissaoPrevistaStore::dispatch($admPrevista);
            return response()->json('', 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao salvar Solicitação de Admissão:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome. " | " . auth()->user()->Empresa->razao_social;
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
        $admissoesPrevista->autocomplete_label_gestor_modal = $admissoesPrevista->GestorAprovacao ? $admissoesPrevista->GestorAprovacao->nome : '';
        $admissoesPrevista->autocomplete_label_gestor_modal_anterior = $admissoesPrevista->GestorAprovacao ? $admissoesPrevista->GestorAprovacao->nome : '';

        $admissoesPrevista->autocomplete_label_cargo = $admissoesPrevista->Cargo ? $admissoesPrevista->Cargo->nome : '';
        $admissoesPrevista->autocomplete_label_cargo_anterior = $admissoesPrevista->Cargo ? $admissoesPrevista->Cargo->nome : '';

        $admissoesPrevista->anexosDel = [];
        $admissoesPrevista->load('Anexos');

        $admissoesPrevista->user_aprovacao = $admissoesPrevista->UserAprovacao ? $admissoesPrevista->UserAprovacao->nome : '';
        $admissoesPrevista->rh_aprovacao = $admissoesPrevista->RhAprovacao ? $admissoesPrevista->RhAprovacao->nome : '';
        $admissoesPrevista->status_aprovacao = $admissoesPrevista->status_aprovacao ?: '';
        $admissoesPrevista->status_aprovacao_rh = $admissoesPrevista->status_aprovacao_rh ?: '';

        return $admissoesPrevista;
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

        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'tipo_contrato' => 'required',
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

            JobAdmissaoPrevistaAprovar::dispatch($admissoesPrevista);

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

            $dados_email = [
                'dados_quem_cadastrou' => [
                    'nome_de' => auth()->user()->nome,
                    'nome_para' => $admissoesPrevista->UserCadastrou->nome,
                    'email_para' => $admissoesPrevista->UserCadastrou->login,
                    'cargo' => $admissoesPrevista->Cargo->nome,
                    'status_aprovacao' => $admissoesPrevista->status_aprovacao_rh,
                    'id' => $admissoesPrevista->id,
                    'empresa_id' => auth()->user()->empresa_id,
                    'nome_empresa' => Cliente::find(auth()->user()->empresa_id)->razao_social
                ],
                'dados_gestor' => [
                    'nome_de' => auth()->user()->nome,
                    'nome_para' => $admissoesPrevista->RhAprovacao->nome,
                    'email_para' => $admissoesPrevista->RhAprovacao->login,
                    'cargo' => $admissoesPrevista->Cargo->nome,
                    'status_aprovacao' => $admissoesPrevista->status_aprovacao_rh,
                    'id' => $admissoesPrevista->id,
                    'empresa_id' => auth()->user()->empresa_id,
                    'nome_empresa' => Cliente::find(auth()->user()->empresa_id)->razao_social
                ]
            ];

            JobAdmissaoPrevistaAprovarRH::dispatch($dados_email);

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

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'aprovar_por_gestor' => auth()->user()->can('privilegio_aprovar_por_gestor'),
                'aprovar_por_rh' => auth()->user()->can('privilegio_aprovar_por_rh'),
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
        $resultado = AdmissoesPrevista::with(
            'Cargo',
            'CentroCusto',
            'CentroCustoFilial',
            'UserCadastrou:id,nome',
            'GestorAprovacao:id,nome', 'UserAprovacao:id,nome', 'RhAprovacao:id,nome');

        $filtroPeriodo = $request->filtroPeriodo == 'true';

        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0]. ' 00:00:00');
            $dataFim = new DataHora($periodo[1]. ' 23:59:59');
            $resultado->where('created_at', '>=', $dataInicio->dataHoraInsert())->where('created_at', '<=', $dataFim->dataHoraInsert());
        }

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Cargo', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoStatusAprovacao')) {
            $status = $request->campoStatusAprovacao;
            if ($request->campoStatusAprovacao == "aberto"){
                $resultado->whereNull('status_aprovacao');
            }
            elseif ($request->campoStatusAprovacao == "aprovado_gestor"){
                $resultado->where('status_aprovacao',AdmissoesPrevista::STATUS_APROVADO)->whereNull('status_aprovacao_rh');
            }elseif ($request->campoStatusAprovacao == "aprovado_rh"){
                $resultado->where('status_aprovacao_rh', AdmissoesPrevista::STATUS_APROVADO);
            }else{
                $resultado->whereStatusAprovacao(AdmissoesPrevista::STATUS_REPROVADO)->orWhere('status_aprovacao_rh', AdmissoesPrevista::STATUS_REPROVADO);
            }
        }

        if (!auth()->user()->can('privilegio_gestao_rh')) {
            $resultado->whereUserId(auth()->user()->id)->orWhere('gestor_id', auth()->user()->id);
        }

        return $resultado->orderByDesc('created_at');

    }

    //Excel
    public function export(Request $request)
    {
        JobAdmissaoPrevistaExportaExcel::dispatch(auth()->user(), $this->filtro($request));
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }
}
