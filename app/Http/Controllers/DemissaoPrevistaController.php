<?php

namespace App\Http\Controllers;

use App\Models\DemissaoPrevista;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;

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

        $dadosValidados = \Validator::make($dados,
            [
                'cliente_id' => 'required',
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'valor_format' => 'required',
                'solicitante' => 'required',
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
                DemissaoPrevista::create($dados);
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

        $demissaoPrevista->autocomplete_label_cliente_modal = $demissaoPrevista->Cliente ? $demissaoPrevista->Cliente->razao_social . ' | ' . $demissaoPrevista->Cliente->cnpj : '';
        $demissaoPrevista->autocomplete_label_cliente_modal_anterior = $demissaoPrevista->Cliente ? $demissaoPrevista->Cliente->razao_social . ' | ' . $demissaoPrevista->Cliente->cnpj : '';

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
                'cliente_id' => 'required',
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'valor_format' => 'required',
                'solicitante' => 'required',
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
            'Cliente:id,razao_social,area_id',
            'CentroCusto',
            'UserCadastrou:id,nome',
            'Colaborador:id,nome,login,tipo,ativo');

        $filtroPeriodo = $request->filtroPeriodo == 'true' ? true : false;

        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0], ' 00:00:00');
            $dataFim = new DataHora($periodo[1], ' 23:59:59');
            $resultado->where('created_at', '>=', $dataInicio->dataInsert())->where('created_at', '<=', $dataFim->dataInsert());
        }

        if ($request->filled('campoCliente')) {
            $resultado->whereClienteId($request->campoCliente);
        }

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
}
