<?php

namespace App\Http\Controllers;

use App\Jobs\Movimentacao\DemissaoPrevista\JobDemissaoPrevistaAprovar;
use App\Jobs\Movimentacao\DemissaoPrevista\JobDemissaoPrevistaAprovarRH;
use App\Jobs\Movimentacao\DemissaoPrevista\JobDemissaoPrevistaExportaExcel;
use App\Jobs\Movimentacao\DemissaoPrevista\JobDemissaoPrevistaStore;
use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\DemissaoPrevista;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use MasterTag\DataHora;
use PDF;

class DemissaoPrevistaController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $dados = $request->input();
        $dados['valor'] = $dados['valor_format'];
        $dados['user_id'] = auth()->user()->id;

        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'centro_custo_filial_id' => 'required_if:filial,true',
                'colaborador_id' => 'required',
                'valor_format' => 'required',
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
            $demissaoPrevista = DemissaoPrevista::create($dados);

            if (isset($dados['anexos'])) {
                foreach ($dados['anexos'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $demissaoPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }
            DB::commit();
            JobDemissaoPrevistaStore::dispatch($demissaoPrevista);
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao salvar Solicitação de Demissão:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\DemissaoPrevista $demissaoPrevista
     * @return DemissaoPrevista
     */
    public function edit(DemissaoPrevista $demissaoPrevista)
    {
        $demissaoPrevista->autocomplete_label_colaborador = $demissaoPrevista->Colaborador ? $demissaoPrevista->Colaborador->nome : '';
        $demissaoPrevista->autocomplete_label_colaborador_anterior = $demissaoPrevista->Colaborador ? $demissaoPrevista->Colaborador->nome : '';

        $demissaoPrevista->autocomplete_label_gestor_modal = $demissaoPrevista->GestorAprovacao ? $demissaoPrevista->GestorAprovacao->nome : '';
        $demissaoPrevista->autocomplete_label_gestor_modal_anterior = $demissaoPrevista->GestorAprovacao ? $demissaoPrevista->GestorAprovacao->nome : '';
        $demissaoPrevista->anexosDel = [];
        $demissaoPrevista->user_aprovacao = $demissaoPrevista->UserAprovacao ? $demissaoPrevista->UserAprovacao->nome : '';
        $demissaoPrevista->rh_aprovacao = $demissaoPrevista->RhAprovacao ? $demissaoPrevista->RhAprovacao->nome : '';
        $demissaoPrevista->status_aprovacao = $demissaoPrevista->status_aprovacao ?: '';
        $demissaoPrevista->status_aprovacao_rh = $demissaoPrevista->status_aprovacao_rh ?: '';
        $demissaoPrevista->load('Anexos');

        return $demissaoPrevista;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\DemissaoPrevista $demissaoPrevista
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function update(Request $request, DemissaoPrevista $demissaoPrevista)
    {
        $dados = $request->input();
        $dados['valor'] = $dados['valor_format'];

        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'valor_format' => 'required',
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
            $demissaoPrevista->update($dados);

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
                        $demissaoPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }
            DB::commit();
            return response()->json('', 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao atualizar Solicitação de Demissão:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
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
        $resultado = DB::table('demissao_previstas as dp')
            ->select(
                'dp.id',
                'us.nome as solicitante_nome',
                'dp.empresa_id',
                'dp.colaborador_id',
                'c.nome as colaborador_nome',
                'dp.centro_custo_id',
                'cc.label as centro_custo',
                'dp.filial',
                'dp.centro_custo_filial_id',
                'dp.data_aprovacao',
                'dp.data_aprovacao_rh',
                DB::raw("DATE_FORMAT(d.data_desmobilizacao, '%d/%m/%Y') as data_desmobilizacao"),
                DB::raw("DATE_FORMAT(a.data_admissao, '%d/%m/%Y') as data_admissao"),
                DB::raw("DATE_FORMAT(dp.data_demissao, '%d/%m/%Y') as data_demissao"),
                DB::raw("DATE_FORMAT(dp.created_at, '%d/%m/%Y às %H:%i:%s') as data_solicitacao"),
                DB::raw("DATE_FORMAT(dp.data_aprovacao_rh, '%d/%m/%Y às %H:%i:%s') as data_aprovacao_rh"),
                DB::raw("DATE_FORMAT(dp.data_aprovacao, '%d/%m/%Y às %H:%i:%s') as data_aprovacao"),
                'a.cargo',
                'dp.tipo_aviso',
                'dp.aprovado_via_script',
                'dp.status',
                'dp.status_aprovacao',
                'dp.status_aprovacao_rh',
                'ugestor.nome as gestor_nome',
                'usa.nome as user_aprovacao_nome',
                'urh.nome as rh_aprovacao_nome',
                'dp.created_at',
                'dp.obs',
                'dp.obs_rh'
            )
            ->join('users as u', 'dp.colaborador_id', '=', 'u.id')
            ->join('users as us', 'dp.user_id', '=', 'us.id')
            ->join('curriculos as c', 'u.id', '=', 'c.id')
            ->join('feedback_curriculos as fc', function ($join) {
                $join->on('u.id', '=', 'fc.curriculo_id')
                    ->whereNull('fc.deleted_at')
                    ->whereRaw('fc.id = (SELECT MAX(id) FROM feedback_curriculos WHERE curriculo_id = u.id AND feedback_curriculos.deleted_at IS NULL)');
            })
            ->join('admissoes as a', function ($join) {
                $join->on('fc.id', '=', 'a.feedback_id')
                    ->whereNull('a.deleted_at')
                    ->whereRaw('a.id = (SELECT MAX(id) FROM admissoes WHERE feedback_id = fc.id AND admissoes.deleted_at IS NULL)');
            })
            ->leftjoin('centro_custos as cc', 'dp.centro_custo_id', '=', 'cc.id')
            ->leftjoin('centro_custo_filials as ccf', 'dp.centro_custo_filial_id', '=', 'ccf.id')
            ->leftjoin('users as ugestor', 'ugestor.id', '=', 'dp.gestor_id')
            ->leftjoin('users as urh', 'urh.id', '=', 'dp.rh_aprovacao_id')
            ->leftjoin('users as usa', 'dp.user_aprovacao_id', '=', 'usa.id')
            ->leftjoin('demissaos as d', function ($join) {
                $join->on('fc.id', '=', 'd.feedback_id')
                    ->whereRaw('d.id = (SELECT MAX(id) FROM demissaos WHERE feedback_id = fc.id)');
            })
            ->where('dp.empresa_id', '=', auth()->user()->empresa_id)
            ->whereNull('dp.deleted_at');

        $filtroPeriodo = $request->filtroPeriodo == 'true';

        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0] . ' 00:00:00');
            $dataFim = new DataHora($periodo[1] . ' 23:59:59');
            $resultado->where('dp.created_at', '>=', $dataInicio->dataHoraInsert())
                ->where('dp.created_at', '<=', $dataFim->dataHoraInsert());
        }

        if ($request->filled('campoBusca')) {
            $resultado->where(function ($r) use ($request) {
                $r->where('c.nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('dp.id', $request->campoBusca);
            });
        }

        if ($request->filled('campoStatusAprovacao')) {
            $resultado->when($request->campoStatusAprovacao == 'aberto', function ($query) {
                return $query->whereNull('dp.status_aprovacao');
            })
                ->when($request->campoStatusAprovacao == 'aprovado_gestor', function ($query) {
                    return $query->where('dp.status_aprovacao', DemissaoPrevista::STATUS_APROVADO)
                        ->whereNull('dp.status_aprovacao_rh');
                })
                ->when($request->campoStatusAprovacao == 'aprovado_rh', function ($query) {
                    return $query->where('dp.status_aprovacao_rh', DemissaoPrevista::STATUS_APROVADO);
                })
                ->when($request->campoStatusAprovacao == 'reprovado', function ($query) {
                    return $query->where(function ($query) {
                        $query->where('dp.status_aprovacao', DemissaoPrevista::STATUS_REPROVADO)
                            ->orWhere('dp.status_aprovacao_rh', DemissaoPrevista::STATUS_REPROVADO);
                    });
                });
        }

        if (!auth()->user()->temPrivilegioGestaoRh()) {
            $resultado->where(function ($query) {
                $query->where('dp.user_id', auth()->user()->id)
                    ->orWhere('dp.gestor_id', auth()->user()->id);
            });
        }

        return $resultado->orderByDesc('dp.created_at');
    }

    public function aprovar(Request $request, DemissaoPrevista $demissaoPrevista)
    {
        $this->authorize('privilegio_aprovar_por_gestor');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $demissaoPrevista->update([
                'user_aprovacao_id' => auth()->id(),
                'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                'obs_aprovacao' => $dados['obs_aprovacao'],
                'status_aprovacao' => $dados['status_aprovacao'],
            ]);
            DB::commit();

            JobDemissaoPrevistaAprovar::dispatch($demissaoPrevista);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar Solicitação:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    public function aprovarRH(Request $request, DemissaoPrevista $demissaoPrevista)
    {
        $this->authorize('privilegio_aprovar_por_rh');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $demissaoPrevista->update([
                'rh_aprovacao_id' => auth()->id(),
                'status_aprovacao_rh' => $dados['status_aprovacao_rh'],
                'obs_rh' => $dados['obs_rh'],
                'data_aprovacao_rh' => (new DataHora())->dataHoraInsert()
            ]);

            DB::commit();

            $dados_email = [
                'dados_quem_cadastrou' => [
                    'nome_de' => auth()->user()->nome,
                    'nome_para' => $demissaoPrevista->UserCadastrou->nome,
                    'email_para' => $demissaoPrevista->UserCadastrou->login,
                    'status_aprovacao' => $demissaoPrevista->status_aprovacao_rh,
                    'id' => $demissaoPrevista->id,
                    'colaborador' => $demissaoPrevista->Colaborador->nome,
                    'empresa_id' => auth()->user()->empresa_id,
                    'nome_empresa' => Cliente::find(auth()->user()->empresa_id)->razao_social
                ],
                'dados_gestor' => [
                    'nome_de' => auth()->user()->nome,
                    'nome_para' => $demissaoPrevista->GestorAprovacao->nome,
                    'email_para' => $demissaoPrevista->GestorAprovacao->login,
                    'status_aprovacao' => $demissaoPrevista->status_aprovacao_rh,
                    'id' => $demissaoPrevista->id,
                    'colaborador' => $demissaoPrevista->Colaborador->nome,
                    'empresa_id' => auth()->user()->empresa_id,
                    'nome_empresa' => Cliente::find(auth()->user()->empresa_id)->razao_social
                ]
            ];

            JobDemissaoPrevistaAprovarRH::dispatch($dados_email);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar solicitação RH:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    //Excel
    public function export(Request $request)
    {
        JobDemissaoPrevistaExportaExcel::dispatch(auth()->user(), $this->filtro($request));
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }

    public function pdf(DemissaoPrevista $demissaoPrevista, Request $request)
    {
        $pdf = PDF::loadView('pdf.planejamento.movimentacao.demissao.avisoprevio', compact('demissaoPrevista'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("pdf_" . Str::slug($demissaoPrevista->Colaborador->nome) . (new DataHora())->nomeUnico() . ".pdf");
    }


    public function atualizacaoStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            foreach ($request->selecionados[0] as $selecionado) {

                $dados = DemissaoPrevista::find($selecionado);

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
