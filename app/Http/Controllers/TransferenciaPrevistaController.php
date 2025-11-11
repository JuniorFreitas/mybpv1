<?php

namespace App\Http\Controllers;

use App\Jobs\Movimentacao\TransferenciaPrevista\JobTransferenciaPrevistaAprovar;
use App\Jobs\Movimentacao\TransferenciaPrevista\JobTransferenciaPrevistaAprovarRH;
use App\Jobs\Movimentacao\TransferenciaPrevista\JobTransferenciaPrevistaExportaExcel;
use App\Jobs\Movimentacao\TransferenciaPrevista\JobTransferenciaPrevistaStore;
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

        $dadosValidados = \Validator::make($dados,
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
                JobTransferenciaPrevistaStore::dispatch($transferenciaPrevista);
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

        $dadosValidados = \Validator::make($dados,
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

            JobTransferenciaPrevistaAprovar::dispatch($transferenciaPrevista);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar SOLICITAÇÃO DE TRANSFERÊNCIA:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    public function aprovarRH(Request $request, TransferenciaPrevista $transferenciaPrevista)
    {
        $this->authorize('privilegio_aprovar_por_rh');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $transferenciaPrevista->update([
                'rh_aprovacao_id' => auth()->id(),
                'status_aprovacao_rh' => $dados['status_aprovacao_rh'],
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
               'Solicitação foi '.$dados['status_aprovacao_rh'].' pelo RH na mudança Centro de Custo na solicitação de transferência #' . $transferenciaPrevista->id
            );

            DB::commit();

            JobTransferenciaPrevistaAprovarRH::dispatch($transferenciaPrevista);

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

    public function filtro(Request $request)
    {
        $resultado = TransferenciaPrevista::with(
            'CentroCustoOrigem',
            'CentroCustoDestino',
            'QuemAprovou:id,nome',
            'UserCadastrou:id,nome',
            'GestorAprovacao:id,nome',
            'Colaborador.Feedback.Admissao.CentroCusto:id,label',
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

        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == "aberto" ? null : $request->campoStatus;
            $resultado->whereStatusAprovacao($status);
        }


        if (!auth()->user()->can('privilegio_gestao_rh')){
            $resultado->whereUserId(auth()->user()->id)->orWhere('gestor_id', auth()->user()->id);
        }

        return $resultado->orderByDesc('created_at');
    }

    public function export(Request $request)
    {
        JobTransferenciaPrevistaExportaExcel::dispatch(auth()->user(),$this->filtro($request));
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);

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
