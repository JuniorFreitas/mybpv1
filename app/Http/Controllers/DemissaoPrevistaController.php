<?php

namespace App\Http\Controllers;

use App\Exports\ModeloRowsExport;
use App\Jobs\Movimentacao\DemissaoPrevista\JobDemissaoPrevistaAprovar;
use App\Jobs\Movimentacao\DemissaoPrevista\JobDemissaoPrevistaAprovarRH;
use App\Jobs\Movimentacao\DemissaoPrevista\JobDemissaoPrevistaStore;
use App\Models\DemissaoPrevista;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use MasterTag\DataHora;
use PDF;

class DemissaoPrevistaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
                JobDemissaoPrevistaStore::dispatch($demissaoPrevista);
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Solicitação de Demissão:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\DemissaoPrevista $demissaoPrevista
     * @return \Illuminate\Http\Response
     */
    public function show(DemissaoPrevista $demissaoPrevista)
    {
        //
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

        return $demissaoPrevista;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\DemissaoPrevista $demissaoPrevista
     * @return \Illuminate\Http\Response
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
        } else {
            try {
                DB::beginTransaction();
                $demissaoPrevista->update($dados);
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao atualizar Solicitação de Demissão:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\DemissaoPrevista $demissaoPrevista
     * @return \Illuminate\Http\Response
     */
    public function destroy(DemissaoPrevista $demissaoPrevista)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $resultado = DemissaoPrevista::with(
            'CentroCusto',
            'UserCadastrou:id,nome',
            'Colaborador:id,nome,login,tipo,ativo', 'GestorAprovacao:id,nome');

        $filtroPeriodo = $request->filtroPeriodo == 'true' ? true : false;

        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0] . ' 00:00:00');
            $dataFim = new DataHora($periodo[1] . ' 23:59:59');

            $resultado->where('created_at', '>=', $dataInicio->dataHoraInsert())->where('created_at', '<=', $dataFim->dataHoraInsert());
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

        if (!auth()->user()->can('gestao_rh')) {
            $resultado->whereUserId(auth()->user()->id)->orWhere('gestor_id', auth()->user()->id);
        }

        $resultado = $resultado->orderByDesc('created_at')->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'aprovar_por_gestor' => auth()->user()->can('aprovar_por_gestor'),
            ]
        ]);
    }


    public function aprovar(Request $request, DemissaoPrevista $demissaoPrevista)
    {
        $this->authorize('aprovar_por_gestor');
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

    public function exportaExcel(Request $request)
    {
        $resultado = $this->filtro($request)->get();

        $head = [
            "ID",
            "QUEM SOLICITOU",
            "SOLICITAÇÃO",
            "EMPRESA",
            "CENTRO DE CUSTO",
            "COLABORADOR",
            "DATA DEMISSÃO",
            "TIPO DE AVISO",
            "GESTOR APROVAÇÃO",
            "OBSERVAÇÃO",

            "STATUS",
            "QUEM APROVOU/REPROVOU",
            "DATA DA APROVAÇÃO/REPROVAÇÃO",
            'OBSERVAÇÃO APROVAÇÃO/REPROVAÇÃO',
        ];

        $rows = [];

        foreach ($resultado as $row) {
            $rows[] = [
                $row->id,
                $row->UserCadastrou->nome,
                (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5),
                $row->Cliente ? $row->Cliente->razao_social : $row->Cliente->nome,
                $row->CentroCusto->label,
                $row->Colaborador->nome,
                (new DataHora($row->data_demissao))->dataCompleta(),
                $row->tipo_aviso,
                $row->GestorAprovacao->nome,
                $row->observacao,

                $row->status_aprovacao ? $row->status_aprovacao : "aberto",
                $row->QuemAprovou ? $row->QuemAprovou->nome : "aguardando",
                (new DataHora($row->data_aprovacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao))->horaCompleta(), 0, 5),
                $row->obs_aprovacao,
            ];
        }

        return \Excel::download(new ModeloRowsExport($head, $rows), 'Demissão Previstas - ' . (new DataHora())->nomeUnico() . '.xlsx');

    }

    public function pdf(DemissaoPrevista $demissaoPrevista, Request $request)
    {
        $pdf = PDF::loadView('pdf.planejamento.movimentacao.demissao.avisoprevio', compact('demissaoPrevista'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("pdf_" . Str::slug($demissaoPrevista->Colaborador->nome) . (new DataHora())->nomeUnico() . ".pdf");
    }

}
