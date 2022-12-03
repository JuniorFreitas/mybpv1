<?php

namespace App\Http\Controllers;

use App\Models\AvaliacaoFeedback;
use App\Models\FeedbackCurriculo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AvaliadorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.cadastros.avaliacoes.avaliador.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\AvaliacaoFeedback $avaliador
     * @return \Illuminate\Http\Response
     */
    public function show(AvaliacaoFeedback $avaliador)
    {
        return $avaliador;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\AvaliacaoFeedback $avaliador
     * @return \Illuminate\Http\Response
     */
    public function edit(AvaliacaoFeedback $avaliador)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\AvaliacaoFeedback $avaliador
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AvaliacaoFeedback $avaliador)
    {

        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'tipo_frequencia' => 'required|min:1',
            'limite_tolerancia' => 'required|numeric|min:1',
            'tempo_limite_falta' => 'required|numeric|min:1',
            'tempo_limite_saida' => 'required|numeric|min:1',
            'dia_nova_frequencia' => 'required|numeric|min:1',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao salvar as configuraçôes da empresa',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                \DB::beginTransaction();
                $avaliador->update($dados);
                \DB::commit();
                return response()->json([], 200);
            } catch (\Exception $e) {
                \DB::rollBack();
                \Log::error($e->getMessage());
                return response()->json([
                    'msg' => 'Erro ao salvar as configuraçôes da empresa',
                    'erros' => $e->getMessage()
                ], 400);
            }

        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\AvaliacaoFeedback $avaliador
     * @return \Illuminate\Http\Response
     */
    public function destroy(AvaliacaoFeedback $avaliador)
    {
    }


    public function AvaliadorAssociadoSingle(Request $request)
    {
        $avaliadores = AvaliacaoFeedback::where('avaliacao_id', $request->avaliacao_id)->whereFeedbackId($request->feedback_id)->get();
        $avaliadores = $avaliadores->map(function ($item) {
            $user = User::find($item->avaliador_id);
            $avaliador['id'] = $user->id;
            $avaliador['nome'] = $user->nome;
            return $avaliador;
        });
        return $avaliadores;
    }

    public function atualizarFuncionarios(Request $request)
    {
        $resultado = FeedbackCurriculo::select(['id', 'curriculo_id'])
            ->with('Curriculo:id,nome,nascimento,rg,orgao_expeditor')
            ->with('Avaliadores', function ($query) use ($request) {
                $query->where('avaliacao_id', $request->avaliacao_id)->with('Avaliador:id,nome');
            })
            ->whereHas('Curriculo.User', function ($query) {
                $query->where('ativo', true);
            })
            ->admitidos();
        $porPagina = $request->get('porPagina');
        $busca = false;
        if ($request->filled('campoBusca')) {
            $busca = $request->get('campoBusca');
            $resultado = $resultado->where('nome', 'like', '%' . $busca . '%');
        }
//        $resultado->with([
//            'avaliadoresFuncionario:id,nome'
//        ]);

        $resultado = $resultado->paginate($porPagina);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => $resultado->items(),
        ]);
    }
}
