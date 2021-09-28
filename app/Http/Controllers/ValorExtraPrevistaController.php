<?php

namespace App\Http\Controllers;

use App\Models\ValorExtraPrevista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class ValorExtraPrevistaController extends Controller
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados,
            [
                'cliente_id' => 'required',
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'tipo' => 'required',
                'periodo_dias' => 'required',
                'solicitante' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Valor Extra',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                ValorExtraPrevista::create($dados);
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Solicitação de Valor Extra:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ValorExtraPrevista  $valorExtraPrevista
     * @return \Illuminate\Http\Response
     */
    public function show(ValorExtraPrevista $valorExtraPrevista)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ValorExtraPrevista  $valorExtraPrevista
     * @return ValorExtraPrevista|\Illuminate\Http\Response
     */
    public function edit(ValorExtraPrevista $valorExtraPrevista)
    {
        $valorExtraPrevista->autocomplete_label_colaborador = $valorExtraPrevista->Colaborador ? $valorExtraPrevista->Colaborador->nome : '';
        $valorExtraPrevista->autocomplete_label_colaborador_anterior = $valorExtraPrevista->Colaborador ? $valorExtraPrevista->Colaborador->nome : '';

        $valorExtraPrevista->autocomplete_label_cliente_modal = $valorExtraPrevista->Cliente ? $valorExtraPrevista->Cliente->razao_social . ' | ' . $valorExtraPrevista->Cliente->cnpj : '';
        $valorExtraPrevista->autocomplete_label_cliente_modal_anterior = $valorExtraPrevista->Cliente ? $valorExtraPrevista->Cliente->razao_social . ' | ' . $valorExtraPrevista->Cliente->cnpj : '';

        return $valorExtraPrevista;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ValorExtraPrevista  $valorExtraPrevista
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, ValorExtraPrevista $valorExtraPrevista)
    {
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados,
            [
                'cliente_id' => 'required',
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'tipo' => 'required',
                'periodo_dias' => 'required',
                'solicitante' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Valor Extra',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $valorExtraPrevista->update($dados);
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Solicitação de Valor Extra:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ValorExtraPrevista  $valorExtraPrevista
     * @return \Illuminate\Http\Response
     */
    public function destroy(ValorExtraPrevista $valorExtraPrevista)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $resultado = ValorExtraPrevista::with(
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
