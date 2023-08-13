<?php

namespace App\Http\Controllers;

use App\Jobs\Movimentacao\DemissaoPrevista\JobDemissaoPrevistaAprovar;
use App\Jobs\Movimentacao\DemissaoPrevista\JobDemissaoPrevistaAprovarRH;
use App\Jobs\Movimentacao\DemissaoPrevista\JobDemissaoPrevistaExportaExcel;
use App\Jobs\Movimentacao\DemissaoPrevista\JobDemissaoPrevistaStore;
use App\Models\Arquivo;
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
     */
    public function store(Request $request)
    {
        $dados = $request->input();
        $dados['valor'] = $dados['valor_format'];
        $dados['user_id'] = auth()->user()->id;

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
        } else {
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

                JobDemissaoPrevistaStore::dispatch($demissaoPrevista);
                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Solicitação de Demissão:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
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
        $demissaoPrevista->load('Anexos');

        return $demissaoPrevista;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\DemissaoPrevista $demissaoPrevista
     * @return \Illuminate\Http\Response
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
                'mimes' => Arquivo::MIMEAPENASIMAGENSPDF
            ]
        ]);

    }

    public function filtro(Request $request)
    {
        $resultado = DemissaoPrevista::with(
            'CentroCusto',
            'UserCadastrou:id,nome',
            'Colaborador:id,nome,login,tipo,ativo',
            'Colaborador.FeedBack:id,curriculo_id,vagas_abertas_id,vaga_id',
            'Colaborador.FeedBack.Admissao:id,feedback_id,data_admissao',
            'Colaborador.FeedBack.VagaSelecionada',
            'GestorAprovacao:id,nome', 'UserAprovacao:id,nome');

        $filtroPeriodo = $request->filtroPeriodo == 'true' ? true : false;

        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0]);
            $dataFim = new DataHora($periodo[1]);
            $resultado->where('created_at', '>=', $dataInicio->dataInsert() . ' 00:00:00')->where('created_at', '<=', $dataFim->dataInsert() . ' 23:59:59');
        }

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Colaborador', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == "aberto" ? null : $request->campoStatus;
            $resultado->whereStatusAprovacao($status);
        }

        if (!auth()->user()->can('privilegio_gestao_rh')) {
            $resultado->whereUserId(auth()->user()->id)->orWhere('gestor_id', auth()->user()->id);
        }

        return $resultado->orderByDesc('created_at');
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
        $this->authorize('rh_aprova_movimentacao');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $demissaoPrevista->update([
                'user_rh_id' => auth()->id(),
                'resposta_rh' => $dados['resposta_rh'],
                'obs_rh' => $dados['obs_rh'],
                'data_aprovacao_rh' => (new DataHora())->dataHoraInsert(),
            ]);

            DB::commit();

            JobDemissaoPrevistaAprovarRH::dispatch($demissaoPrevista);

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
        JobDemissaoPrevistaExportaExcel::dispatch(auth()->user(),$this->filtro($request));
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
