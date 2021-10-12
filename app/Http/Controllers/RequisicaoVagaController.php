<?php

namespace App\Http\Controllers;

use App\Models\Arquivo;
use App\Models\RequisicaoVaga;
use DB;
use Illuminate\Http\Request;

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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
//        $this->authorize('');
        $dados = $request->input();
        $dados['previsao_inicio'] = $dados['imediata'] ? null : $dados['previsao_inicio'];
        $dadosValidados = \Validator::make($dados,
            [
                'cliente_id' => 'required',
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
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RequisicaoVaga $requisicaoVaga)
    {
        $dados = $request->input();
        $dados['previsao_inicio'] = $dados['imediata'] ? null : $dados['previsao_inicio'];
        $dadosValidados = \Validator::make($dados,
            [
                'cliente_id' => 'required',
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

    public function atualizar(Request $request)
    {
        $resultado = RequisicaoVaga::with(
            'Cliente',
            'CentroCusto',
            'Cargo',
            'Area');
        $resultado = $resultado->orderByDesc('created_at')->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
            ]
        ]);
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
