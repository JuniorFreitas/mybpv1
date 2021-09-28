<?php

namespace App\Http\Controllers;

use App\Models\FeriasPrevista;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class FeriasPrevistaController extends Controller
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados,
            [
                'cliente_id' => 'required',
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'qnt_dias' => 'required',
                'dias_saldo' => 'required',
                'solicitante' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Férias',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                FeriasPrevista::create($dados);
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Solicitação de Férias:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\FeriasPrevista $feriasPrevista
     * @return \Illuminate\Http\Response
     */
    public function show(FeriasPrevista $feriasPrevista)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\FeriasPrevista $feriasPrevista
     * @return \Illuminate\Http\Response
     */
    public function edit(FeriasPrevista $feriasPrevista)
    {
        $feriasPrevista->autocomplete_label_colaborador = $feriasPrevista->Colaborador ? $feriasPrevista->Colaborador->nome : '';
        $feriasPrevista->autocomplete_label_colaborador_anterior = $feriasPrevista->Colaborador ? $feriasPrevista->Colaborador->nome : '';

        $feriasPrevista->autocomplete_label_cliente_modal = $feriasPrevista->Cliente ? $feriasPrevista->Cliente->razao_social . ' | ' . $feriasPrevista->Cliente->cnpj : '';
        $feriasPrevista->autocomplete_label_cliente_modal_anterior = $feriasPrevista->Cliente ? $feriasPrevista->Cliente->razao_social . ' | ' . $feriasPrevista->Cliente->cnpj : '';

        return $feriasPrevista;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\FeriasPrevista $feriasPrevista
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FeriasPrevista $feriasPrevista)
    {
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados,
            [
                'cliente_id' => 'required',
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'qnt_dias' => 'required',
                'dias_saldo' => 'required',
                'solicitante' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Férias',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $feriasPrevista->update($dados);
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Solicitação de Férias:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\FeriasPrevista $feriasPrevista
     * @return \Illuminate\Http\Response
     */
    public function destroy(FeriasPrevista $feriasPrevista)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $resultado = FeriasPrevista::with(
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
