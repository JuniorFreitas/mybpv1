<?php

namespace App\Http\Controllers;

use App\Jobs\Movimentacao\MudaIntermitenteFixoPrevista\JobMudaIntermitenteFixoPrevistaAprovar;
use App\Models\IntermitenteFixoPrevista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class IntermitenteFixoPrevistaController extends Controller
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
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $dados = $request->input();
        $dados['salario_anterior'] = $dados['salario_anterior_format'];
        $dados['novo_salario'] = $dados['novo_salario_format'];
        $dados['user_id'] = auth()->user()->id;

        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'cargo_anterior_id' => 'required',
                'salario_anterior_format' => 'required',
                'novo_cargo_id' => 'required',
                'novo_salario_format' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Mudança Intermitente Fixo',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                IntermitenteFixoPrevista::create($dados);
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar  Mudança Intermitente Fixo:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\IntermitenteFixoPrevista  $intermitenteFixoPrevista
     * @return \Illuminate\Http\Response
     */
    public function show(IntermitenteFixoPrevista $intermitenteFixoPrevista)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\IntermitenteFixoPrevista  $intermitenteFixoPrevista
     * @return \Illuminate\Http\Response
     */
    public function edit(IntermitenteFixoPrevista $intermitenteFixoPrevista)
    {
        $intermitenteFixoPrevista->autocomplete_label_colaborador = $intermitenteFixoPrevista->Colaborador ? $intermitenteFixoPrevista->Colaborador->nome : '';
        $intermitenteFixoPrevista->autocomplete_label_colaborador_anterior = $intermitenteFixoPrevista->Colaborador ? $intermitenteFixoPrevista->Colaborador->nome : '';

        $intermitenteFixoPrevista->autocomplete_label_cargoanterior = $intermitenteFixoPrevista->CargoAnterior ? $intermitenteFixoPrevista->CargoAnterior->nome : '';
        $intermitenteFixoPrevista->autocomplete_label_cargoanterior_anterior = $intermitenteFixoPrevista->CargoAnterior ? $intermitenteFixoPrevista->CargoAnterior->nome : '';

        $intermitenteFixoPrevista->autocomplete_label_novo_cargo = $intermitenteFixoPrevista->NovoCargo ? $intermitenteFixoPrevista->NovoCargo->nome : '';
        $intermitenteFixoPrevista->autocomplete_label_novo_cargo_anterior = $intermitenteFixoPrevista->NovoCargo ? $intermitenteFixoPrevista->NovoCargo->nome : '';

        $intermitenteFixoPrevista->autocomplete_label_gestor_modal = $intermitenteFixoPrevista->GestorAprovacao ? $intermitenteFixoPrevista->GestorAprovacao->nome : '';
        $intermitenteFixoPrevista->autocomplete_label_gestor_modal_anterior = $intermitenteFixoPrevista->GestorAprovacao ? $intermitenteFixoPrevista->GestorAprovacao->nome : '';

        return $intermitenteFixoPrevista;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\IntermitenteFixoPrevista  $intermitenteFixoPrevista
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, IntermitenteFixoPrevista $intermitenteFixoPrevista)
    {
        $dados = $request->input();
        $dados['salario_anterior'] = $dados['salario_anterior_format'];
        $dados['novo_salario'] = $dados['novo_salario_format'];
        $dados['user_id'] = auth()->user()->id;


        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'cargo_anterior_id' => 'required',
                'salario_anterior_format' => 'required',
                'novo_cargo_id' => 'required',
                'novo_salario_format' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar  Mudança Intermitente Fixo',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $intermitenteFixoPrevista->update($dados);
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar  Mudança Intermitente Fixo:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\IntermitenteFixoPrevista  $intermitenteFixoPrevista
     * @return \Illuminate\Http\Response
     */
    public function destroy(IntermitenteFixoPrevista $intermitenteFixoPrevista)
    {
        //
    }


    public function aprovar(Request $request, IntermitenteFixoPrevista $intermitenteFixoPrevista)
    {
        $this->authorize('aprovar_por_gestor');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $intermitenteFixoPrevista->update([
                'user_aprovacao_id' => auth()->id(),
                'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                'obs_aprovacao' => $dados['obs_aprovacao'],
                'status_aprovacao' => $dados['status_aprovacao'],
            ]);
            DB::commit();
            JobMudaIntermitenteFixoPrevistaAprovar::dispatch($intermitenteFixoPrevista);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar Intermitente Fixo Prevista:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }


    public function atualizar(Request $request)
    {
        $resultado = IntermitenteFixoPrevista::with(
            'CentroCusto',
            'CargoAnterior',
            'NovoCargo',
            'UserCadastrou:id,nome',
            'Colaborador:id,nome,login,tipo,ativo','GestorAprovacao:id,nome');

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
