<?php

namespace App\Http\Controllers;

use App\Jobs\Movimentacao\AdmissaoPrevista\JobAdmissaoPrevistaAprovar;
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
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $dados = $request->input();
        $dados['salario'] = $dados['salario_format'];
        $dados['user_id'] = auth()->user()->id;

        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'tipo_contrato' => 'required',
                'cargo_id' => 'required',
                'salario_format' => 'required',
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
                return response()->json(['msg' => $msg], 400);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
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
        $admissoesPrevista->autocomplete_label_gestor_modal = $admissoesPrevista->GestorAprovacao ? $admissoesPrevista->GestorAprovacao->nome : '';
        $admissoesPrevista->autocomplete_label_gestor_modal_anterior = $admissoesPrevista->GestorAprovacao ? $admissoesPrevista->GestorAprovacao->nome : '';

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
                'centro_custo_id' => 'required',
                'tipo_contrato' => 'required',
                'cargo_id' => 'required',
                'salario_format' => 'required',
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

    public function aprovar(Request $request, AdmissoesPrevista $admissoesPrevista)
    {
        $this->authorize('aprovar_por_gestor');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $admissoesPrevista->update([
                'user_aprovacao_id' => auth()->id(),
                'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                'obs_aprovacao' => $dados['obs_aprovacao'],
                'status_aprovacao' => $dados['status_aprovacao'],
            ]);
            DB::commit();

            JobAdmissaoPrevistaAprovar::dispatch($admissoesPrevista);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar ADMISSÃO PREVISTA:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }


    public function atualizar(Request $request)
    {
        $resultado = AdmissoesPrevista::with(
            'Cargo',
            'CentroCusto',
            'UserCadastrou:id,nome',
            'Colaborador:id,nome,login,tipo,ativo','GestorAprovacao:id,nome');

        $filtroPeriodo = $request->filtroPeriodo == 'true' ? true : false;

        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0], ' 00:00:00');
            $dataFim = new DataHora($periodo[1], ' 23:59:59');
            $resultado->where('created_at', '>=', $dataInicio->dataHoraInsert())->where('created_at', '<=', $dataFim->dataHoraInsert());
        }

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Cargo', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == "aberto" ? null : $request->campoStatus;
            $resultado->whereStatusAprovacao($status);
        }

        if (!auth()->user()->can('gestao_rh')){
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
}
