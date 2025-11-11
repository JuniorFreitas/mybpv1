<?php

namespace App\Http\Controllers;

use App\Jobs\Movimentacao\ValorExtraPrevista\JobValorExtraPrevistaAprovar;
use App\Jobs\Movimentacao\ValorExtraPrevista\JobValorExtraPrevistaAprovarRH;
use App\Jobs\Movimentacao\ValorExtraPrevista\JobValorExtraPrevistaExportaExcel;
use App\Jobs\Movimentacao\ValorExtraPrevista\JobValorExtraPrevistaStore;
use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\LogHistorico;
use App\Models\ValorExtraPrevista;
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
        $dadosValidados = \Validator::make($dados,
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
            JobValorExtraPrevistaStore::dispatch($valorExtraPrevista);
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
        $valorExtraPrevista->autocomplete_label_colaborador = $valorExtraPrevista->Colaborador ? $valorExtraPrevista->Colaborador->nome : '';
        $valorExtraPrevista->autocomplete_label_colaborador_anterior = $valorExtraPrevista->Colaborador ? $valorExtraPrevista->Colaborador->nome : '';

        $valorExtraPrevista->autocomplete_label_gestor_modal = $valorExtraPrevista->GestorAprovacao ? $valorExtraPrevista->GestorAprovacao->nome : '';
        $valorExtraPrevista->autocomplete_label_gestor_modal_anterior = $valorExtraPrevista->GestorAprovacao ? $valorExtraPrevista->GestorAprovacao->nome : '';
        $valorExtraPrevista->anexosDel = [];
        $valorExtraPrevista->user_aprovacao = $valorExtraPrevista->UserAprovacao ? $valorExtraPrevista->UserAprovacao->nome : '';
        $valorExtraPrevista->rh_aprovacao = $valorExtraPrevista->RhAprovacao ? $valorExtraPrevista->RhAprovacao->nome : '';
        $valorExtraPrevista->status_aprovacao = $valorExtraPrevista->status_aprovacao ?: '';
        $valorExtraPrevista->status_aprovacao_rh = $valorExtraPrevista->status_aprovacao_rh ?: '';
        $valorExtraPrevista->load('Anexos');
        return $valorExtraPrevista;
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

        $dadosValidados = \Validator::make($dados,
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

            JobValorExtraPrevistaAprovar::dispatch($valorExtraPrevista);
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar VALOR EXTRA:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
//            return response()->json(['msg' => $msg], 400);
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
                'Solicitação foi '.$dados['status_aprovacao_rh']. ' pelo RH na solicitação de valor extra ' . $valorExtraPrevista->id
            );

            DB::commit();

            $dados_email = [
                'dados_quem_cadastrou' => [
                    'nome_de' => auth()->user()->nome,
                    'nome_para' => $valorExtraPrevista->UserCadastrou->nome,
                    'email_para' => $valorExtraPrevista->UserCadastrou->login,
                    'status_aprovacao' => $valorExtraPrevista->status_aprovacao_rh,
                    'id' => $valorExtraPrevista->id,
                    'colaborador' => $valorExtraPrevista->Colaborador->nome,
                    'empresa_id' => auth()->user()->empresa_id,
                    'nome_empresa' => Cliente::find(auth()->user()->empresa_id)->razao_social
                ],
                'dados_gestor' => [
                    'nome_de' => auth()->user()->nome,
                    'nome_para' => $valorExtraPrevista->GestorAprovacao->nome,
                    'email_para' => $valorExtraPrevista->GestorAprovacao->login,
                    'status_aprovacao' => $valorExtraPrevista->status_aprovacao_rh,
                    'id' => $valorExtraPrevista->id,
                    'colaborador' => $valorExtraPrevista->Colaborador->nome,
                    'empresa_id' => auth()->user()->empresa_id,
                    'nome_empresa' => Cliente::find(auth()->user()->empresa_id)->razao_social
                ]
            ];

            JobValorExtraPrevistaAprovarRH::dispatch($dados_email);
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

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'aprovar_por_gestor' => auth()->user()->can('privilegio_aprovar_por_gestor'),
                'aprovar_por_rh' => auth()->user()->can('privilegio_aprovar_por_rh'),
                'mimes' => Arquivo::MIMEAPENASIMAGENSPDF
            ]
        ]);
    }

    public function filtro(Request $request)
    {
        $resultado = ValorExtraPrevista::with(
            'CentroCusto',
            'UserCadastrou:id,nome',
            'Colaborador:id,nome,login,tipo,ativo',
            'Colaborador:id,nome,login,tipo,ativo',
            'Colaborador.FeedBack:id,curriculo_id,vagas_abertas_id,vaga_id',
            'Colaborador.FeedBack.Admissao:id,feedback_id,data_admissao',
            'Colaborador.FeedBack.VagaSelecionada',
            'GestorAprovacao:id,nome',
            'UserAprovacao:id,nome',
            'RhAprovacao:id,nome');

        $filtroPeriodo = $request->filtroPeriodo == 'true';

        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0]. ' 00:00:00');
            $dataFim = new DataHora($periodo[1]. ' 23:59:59');
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
            if ($request->campoStatusAprovacao == "aberto"){
                $resultado->whereNull('status_aprovacao');
            }
            elseif ($request->campoStatusAprovacao == "aprovado_gestor"){
                $resultado->where('status_aprovacao',ValorExtraPrevista::STATUS_APROVADO)->whereNull('status_aprovacao_rh');
            }elseif ($request->campoStatusAprovacao == "aprovado_rh"){
                $resultado->where('status_aprovacao_rh', ValorExtraPrevista::STATUS_APROVADO);
            }else{
                $resultado->whereStatusAprovacao(ValorExtraPrevista::STATUS_REPROVADO)->orWhere('status_aprovacao_rh', ValorExtraPrevista::STATUS_REPROVADO);
            }
        }

        if (!auth()->user()->can('privilegio_gestao_rh')) {
            $resultado->whereUserId(auth()->user()->id)->orWhere('gestor_id', auth()->user()->id);
        }

        return $resultado->orderByDesc('created_at');
    }

    public function export(Request $request)
    {
        JobValorExtraPrevistaExportaExcel::dispatch(auth()->user(),$this->filtro($request));
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
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
