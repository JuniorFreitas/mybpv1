<?php

namespace App\Http\Controllers;

use App\Exports\ModeloRowsExport;
use App\Jobs\JobExportaExcel;
use App\Jobs\Movimentacao\TransferenciaPrevista\JobTransferenciaPrevistaAprovar;
use App\Jobs\Movimentacao\TransferenciaPrevista\JobTransferenciaPrevistaAprovarRH;
use App\Jobs\Movimentacao\TransferenciaPrevista\JobTransferenciaPrevistaStore;
use App\Models\Arquivo;
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
//                JobTransferenciaPrevistaStore::dispatch($transferenciaPrevista);
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
        $this->authorize('rh_aprova_movimentacao');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $transferenciaPrevista->update([
                'user_rh_id' => auth()->id(),
                'resposta_rh' => $dados['resposta_rh'],
                'obs_rh' => $dados['obs_rh'],
                'data_aprovacao_rh' => (new DataHora())->dataHoraInsert(),
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
            'Colaborador','UserAprovacao:id,nome');

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


        if (!auth()->user()->can('privilegio_gestao_rh')){
            $resultado->whereUserId(auth()->user()->id)->orWhere('gestor_id', auth()->user()->id);
        }

        return $resultado->orderByDesc('created_at');
    }

    public function export(Request $request)
    {
        $resultado = $this->filtro($request)->get();

        $head = [
            "Quem Solicitou",
            "Data da Solicitação",
            "Colaborador",
            "Centro de Custo Origem",
            "Centro de Custo Destino",
            "Data da Tranferência",
            "Gestor Aprovação",
            "Observação",
            "Status",
            "Quem Aprovou/Reprovou",
            "Data da Aprovação/Reprovação",
            'Observação Aprovação/Reprovação',
        ];

        $rows = [];

        foreach ($resultado as $row) {
            $rows[] = [
                $row->UserCadastrou->nome,
                (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5),
                $row->Colaborador->nome,
                $row->CentroCustoOrigem->label,
                $row->CentroCustoDestino->label,
                (new DataHora($row->data_transferencia))->dataCompleta(),
                $row->GestorAprovacao->nome,
                $row->obs,
                $row->status_aprovacao ? $row->status_aprovacao : "aberto",
                $row->QuemAprovou ? $row->QuemAprovou->nome : "aguardando",
                $row->data_aprovacao ? (new DataHora($row->data_aprovacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao))->horaCompleta(), 0, 5) : '',
                $row->obs_aprovacao,
            ];
        }

        $nameArquivo = "tranferencia_prevista" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Transferência - Prevista", $head, $rows, $nameArquivo);
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
