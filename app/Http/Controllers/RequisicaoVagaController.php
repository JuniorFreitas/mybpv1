<?php

namespace App\Http\Controllers;

use App\Jobs\JobExportaExcel;
use App\Jobs\RequisicaoVaga\JobRequisicaoVagaAprovar;
use App\Models\Arquivo;
use App\Models\RequisicaoVaga;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class RequisicaoVagaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.planejamento.requisicao-vagas.index');
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
//        $this->authorize('');
        $dados = $request->input();
        $dados['previsao_inicio'] = $dados['imediata'] ? null : $dados['previsao_inicio'];
        $dados['outras_informacoes']['salario_valor'] = $dados['outras_informacoes']['salario'] == 'exceção' ? $dados['outras_informacoes']['salario_valor_format'] : null;
        $dados['user_id'] = auth()->user()->id;
        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'cargo_id' => 'required',
                'area_id' => 'required',
                'quantidade' => 'required',
                'tipo_contratacao' => 'required',
                'prioridade' => 'required',
                'solicitante' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Vaga',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $requisicao = RequisicaoVaga::create($dados);
                $requisicao->OutrasInformacoes()->create($dados['outras_informacoes']);
                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Solicitação:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\RequisicaoVaga $requisicaoVaga
     * @return \Illuminate\Http\Response
     */
    public function show(RequisicaoVaga $requisicaoVaga)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\RequisicaoVaga $requisicaoVaga
     * @return \Illuminate\Http\Response
     */
    public function edit(RequisicaoVaga $requisicaoVaga)
    {
        $requisicaoVaga->load('OutrasInformacoes');

        $requisicaoVaga->autocomplete_label_cargo_modal = $requisicaoVaga->Cargo ? $requisicaoVaga->Cargo->nome : '';
        $requisicaoVaga->autocomplete_label_cargo_modal_anterior = $requisicaoVaga->Cargo ? $requisicaoVaga->Cargo->nome : '';

        $requisicaoVaga->autocomplete_label_cliente_modal = $requisicaoVaga->Cliente ? $requisicaoVaga->Cliente->razao_social . ' | ' . $requisicaoVaga->Cliente->cnpj : '';
        $requisicaoVaga->autocomplete_label_cliente_modal_anterior = $requisicaoVaga->Cliente ? $requisicaoVaga->Cliente->razao_social . ' | ' . $requisicaoVaga->Cliente->cnpj : '';

        return $requisicaoVaga;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\RequisicaoVaga $requisicaoVaga
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, RequisicaoVaga $requisicaoVaga)
    {
        $dados = $request->input();
        $dados['previsao_inicio'] = $dados['imediata'] ? null : $dados['previsao_inicio'];
        $dados['outras_informacoes']['salario_valor'] = $dados['outras_informacoes']['salario'] == 'exceção' ? $dados['outras_informacoes']['salario_valor_format'] : null;

        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'cargo_id' => 'required',
                'area_id' => 'required',
                'quantidade' => 'required',
                'tipo_contratacao' => 'required',
                'prioridade' => 'required',
                'solicitante' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao alterar Solicitação de vaga',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $requisicaoVaga->update($dados);
                $requisicaoVaga->OutrasInformacoes->update($dados['outras_informacoes']);
                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error ao alterar Solicitação:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\RequisicaoVaga $requisicaoVaga
     * @return \Illuminate\Http\Response
     */
    public function destroy(RequisicaoVaga $requisicaoVaga)
    {
        //
    }

    public function aprovar(Request $request, RequisicaoVaga $requisicaoVaga)
    {
        $this->authorize('privilegio_aprovar_por_gestor');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $requisicaoVaga->update([
                'user_aprovacao_id' => auth()->user()->id,
                'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                'obs_aprovacao' => $dados['obs_aprovacao'],
                'status_aprovacao' => $dados['status_aprovacao'],
            ]);
            DB::commit();


            JobRequisicaoVagaAprovar::dispatch($requisicaoVaga);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao alterar Solicitação:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
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
            ]
        ]);
    }

    public function filtro(Request $request)
    {
        $resultado = RequisicaoVaga::with(
            'CentroCusto',
            'Cargo',
            'Area','UserCadastrou:id,nome',
            'UserAprovacao:id,nome');

        $filtroPeriodo = $request->filtroPeriodo == 'true' ? true : false;
        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0]);
            $dataFim = new DataHora($periodo[1]);
            $resultado->where('created_at', '>=', $dataInicio->dataInsert() . ' 00:00:00')->where('created_at', '<=', $dataFim->dataInsert() . ' 23:59:59');
        }

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Cargo', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->campoBusca . '%');
            });
        }

        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == "aberto" ? null : $request->campoStatus;
            $resultado->whereStatusAprovacao($status);
        }
        return $resultado->orderByDesc('created_at');

    }

    public function export(Request $request)
    {
        $resultado = $this->filtro($request)->get();
        $head = [
            "Quem Solicitou",
            "Cargo",
            "Data da Solicitação",
            "Centro de Custo",
            "Tipo de Contrato",
            "Prioridade",
            "Data do Inicio",
            "Quem Aprovou/Reprovou",
            "Data da Aprovação/Reprovação",
            "Status"
        ];

        $rows = [];

        foreach ($resultado as $row) {
            $rows[] = [
                $row->solicitante,
                $row->Cargo->nome,
                (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5),
                $row->CentroCusto->label,
                $row->tipo_contratacao,
                $row->prioridade,
                $row->previsao_inicio ? $row->previsao_inicio : "Imediata",
                $row->UserAprovacao ? $row->UserAprovacao->nome : "aguardando",
                $row->data_aprovacao ? (new DataHora($row->data_aprovacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao))->horaCompleta(), 0, 5) : "aguardando",
                $row->status_aprovacao ? $row->status_aprovacao : "aberto",
            ];
        }

        $nameArquivo = "requisicao_vaga" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Requisição - Vaga", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }
    // Anexos-------------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, array_merge(Arquivo::MIMEAPENASIMAGENSPDF, Arquivo::MIMEAPENASDOCUMENTOS), Arquivo::DISCO_REQUISICAO_VAGA);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_REQUISICAO_VAGA, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_REQUISICAO_VAGA, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_REQUISICAO_VAGA, $arquivo);
    }
}
