<?php

namespace App\Http\Controllers;

use App\Models\AdmissoesPrevista;
use App\Models\DemissaoPrevista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class AdmissoesPrevistaController extends Controller
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
        $dados['salario'] = $dados['salario_format'];

        $dadosValidados = \Validator::make($dados,
            [
                'cliente_id' => 'required',
                'centro_custo_id' => 'required',
                'tipo_contrato' => 'required',
                'cargo_id' => 'required',
                'salario_format' => 'required',
                'solicitante' => 'required',
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
                AdmissoesPrevista::create($dados);
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Solicitação de Admissão:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\AdmissoesPrevista $admissoesPrevista
     * @return \Illuminate\Http\Response
     */
    public function show(AdmissoesPrevista $admissoesPrevista)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\AdmissoesPrevista $admissoesPrevista
     * @return AdmissoesPrevista|\Illuminate\Http\Response
     */
    public function edit(AdmissoesPrevista $admissoesPrevista)
    {
        $admissoesPrevista->autocomplete_label_colaborador = $admissoesPrevista->Colaborador ? $admissoesPrevista->Colaborador->nome : '';
        $admissoesPrevista->autocomplete_label_colaborador_anterior = $admissoesPrevista->Colaborador ? $admissoesPrevista->Colaborador->nome : '';

        $admissoesPrevista->autocomplete_label_cliente_modal = $admissoesPrevista->Cliente ? $admissoesPrevista->Cliente->razao_social . ' | ' . $admissoesPrevista->Cliente->cnpj : '';
        $admissoesPrevista->autocomplete_label_cliente_modal_anterior = $admissoesPrevista->Cliente ? $admissoesPrevista->Cliente->razao_social . ' | ' . $admissoesPrevista->Cliente->cnpj : '';

        $admissoesPrevista->autocomplete_label_cargo = $admissoesPrevista->Cargo ? $admissoesPrevista->Cargo->nome : '';
        $admissoesPrevista->autocomplete_label_cargo_anterior = $admissoesPrevista->Cargo ? $admissoesPrevista->Cargo->nome : '';

        return $admissoesPrevista;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\AdmissoesPrevista $admissoesPrevista
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AdmissoesPrevista $admissoesPrevista)
    {
        $dados = $request->input();
        $dados['salario'] = $dados['salario_format'];

        $dadosValidados = \Validator::make($dados,
            [
                'cliente_id' => 'required',
                'centro_custo_id' => 'required',
                'tipo_contrato' => 'required',
                'cargo_id' => 'required',
                'salario_format' => 'required',
                'solicitante' => 'required',
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
                $admissoesPrevista->update($dados);
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Solicitação de Admissão:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\AdmissoesPrevista $admissoesPrevista
     * @return \Illuminate\Http\Response
     */
    public function destroy(AdmissoesPrevista $admissoesPrevista)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $resultado = AdmissoesPrevista::with(
            'Cliente:id,razao_social,area_id',
            'Cargo',
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
