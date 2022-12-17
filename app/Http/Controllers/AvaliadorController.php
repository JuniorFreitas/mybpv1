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
        $this->authorize('cadastro_avaliacao_vincular_avaliadores');
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
        $this->authorize('cadastro_avaliacao_vincular_avaliadores');
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
        $this->authorize('cadastro_avaliacao_vincular_avaliadores');
        $avaliadores = AvaliacaoFeedback::select(['id', 'empresa_id', 'avaliacao_id', 'avaliador_id', 'funcionario_id', 'status'])
            ->where('avaliacao_id', $request->avaliacao_id)
            ->where('funcionario_id', $request->funcionario_id)
            ->with('Avaliador:id,nome')
            ->OrigemAvaliador()
            ->get();
        return $avaliadores;
    }

    public function associar(Request $request)
    {
        $this->authorize('cadastro_avaliacao_vincular_avaliadores');
        $dados = $request->input();
        try {
            \DB::beginTransaction();
            if (count($dados['funcionarios']) == 1) {
                $autoAvaliacao = AvaliacaoFeedback::where('avaliacao_id', $dados['avaliacao_id'])
                    ->where('funcionario_id', $dados['funcionarios'][0])
                    ->where('origem_feedback', AvaliacaoFeedback::ORIGEM_FUNCIONARIO)
                    ->first();

                if ($autoAvaliacao) {
                    if (isset($request['avaliadoresDelete'])) {
                        foreach ($request['avaliadoresDelete'] as $id) {
                            AvaliacaoFeedback::find($id)->delete();
                        }
                    }
                    foreach ($request['avaliadores'] as $avaliador) {
                        if (isset($avaliador['novo'])) {
                            $avaliadorFeedback = new AvaliacaoFeedback();
                            $avaliadorFeedback->avaliacao_id = $request->avaliacao_id;
                            $avaliadorFeedback->funcionario_id = $dados['funcionarios'][0];
                            $avaliadorFeedback->avaliador_id = $avaliador['avaliador']['id'];
                            $autoAvaliacao->principal = $avaliador['principal'];
                            $avaliadorFeedback->origem_feedback = AvaliacaoFeedback::ORIGEM_AVALIADOR;
                            $avaliadorFeedback->status = AvaliacaoFeedback::STATUS_AGUARDANDO;
                            $avaliadorFeedback->save();
                        }
                    }
                } else {
                    $autoAvaliacao = new AvaliacaoFeedback();
                    $autoAvaliacao->avaliacao_id = $request->avaliacao_id;
                    $autoAvaliacao->funcionario_id = $dados['funcionarios'][0];
                    $autoAvaliacao->avaliador_id = $dados['funcionarios'][0];
                    $autoAvaliacao->principal = false;
                    $autoAvaliacao->origem_feedback = AvaliacaoFeedback::ORIGEM_FUNCIONARIO;
                    $autoAvaliacao->status = AvaliacaoFeedback::STATUS_AGUARDANDO;
                    $autoAvaliacao->save();
                    foreach ($request['avaliadores'] as $avaliador) {
                        if (isset($avaliador['novo'])) {
                            AvaliacaoFeedback::create(
                                [
                                    'avaliacao_id' => $request->avaliacao_id,
                                    'funcionario_id' => $dados['funcionarios'][0],
                                    'principal' => $avaliador['principal'],
                                    'avaliador_id' => $avaliador['avaliador']['id'],
                                    'origem_feedback' => AvaliacaoFeedback::ORIGEM_AVALIADOR,
                                    'status' => AvaliacaoFeedback::STATUS_AGUARDANDO
                                ]
                            );
                        }
                    }
                }

                \DB::commit();
                return response()->json(['Unica seleção'], 200);

            } else {
                foreach ($dados['funcionarios'] as $funcionario_id) {
                    AvaliacaoFeedback::where('avaliacao_id', $dados['avaliacao_id'])
                        ->where('funcionario_id', $funcionario_id)
                        ->where('origem_feedback', AvaliacaoFeedback::ORIGEM_AVALIADOR)->delete();

                    $autoAvaliacao = AvaliacaoFeedback::where('avaliacao_id', $dados['avaliacao_id'])
                        ->where('funcionario_id', $funcionario_id)
                        ->where('origem_feedback', AvaliacaoFeedback::ORIGEM_FUNCIONARIO);

                    if ($autoAvaliacao->count() == 0) {
                        $autoAvaliacao = new AvaliacaoFeedback();
                        $autoAvaliacao->avaliacao_id = $request->avaliacao_id;
                        $autoAvaliacao->avaliador_id = $funcionario_id;
                        $autoAvaliacao->funcionario_id = $funcionario_id;
                        $autoAvaliacao->principal = false;
                        $autoAvaliacao->origem_feedback = AvaliacaoFeedback::ORIGEM_FUNCIONARIO;
                        $autoAvaliacao->status = AvaliacaoFeedback::STATUS_AGUARDANDO;
                        $autoAvaliacao->save();
                        foreach ($request['avaliadores'] as $avaliador) {
                            $avaliadorFeedback = new AvaliacaoFeedback();
                            $avaliadorFeedback->avaliacao_id = $request->avaliacao_id;
                            $avaliadorFeedback->funcionario_id = $funcionario_id;
                            $avaliadorFeedback->principal = $avaliador['principal'];
                            $avaliadorFeedback->avaliador_id = $avaliador['avaliador']['id'];
                            $avaliadorFeedback->origem_feedback = AvaliacaoFeedback::ORIGEM_AVALIADOR;
                            $avaliadorFeedback->status = AvaliacaoFeedback::STATUS_AGUARDANDO;
                            $avaliadorFeedback->save();
                        }
                    } else {
                        foreach ($request['avaliadores'] as $avaliador) {
                            AvaliacaoFeedback::create(
                                [
                                    'avaliacao_id' => $request->avaliacao_id,
                                    'funcionario_id' => $funcionario_id,
                                    'avaliador_id' => $avaliador['avaliador']['id'],
                                    'principal' => $avaliador['principal'],
                                    'origem_feedback' => AvaliacaoFeedback::ORIGEM_AVALIADOR,
                                    'status' => AvaliacaoFeedback::STATUS_AGUARDANDO
                                ]
                            );
                        }
                    }
                }

                \DB::commit();
                return response()->json(['Multi seleção'], 200);
            }
        } catch
        (\Exception $e) {
            \DB::rollBack();
            \Log::error($e->getMessage());
            return response()->json([
                'msg' => 'Erro ao associar o avaliador',
                'erros' => $e->getMessage()
            ], 400);
        }
    }

    public function atualizarFuncionarios(Request $request)
    {
        $this->authorize('cadastro_avaliacao_vincular_avaliadores');

        $resultado = User::select(['id', 'nome', 'login', 'tipo', 'ativo'])
            ->TiposGerenciais()
            ->whereEmpresaId(auth()->user()->empresa_id)
            ->whereAtivo(true)
            ->with('Avaliadores', function ($query) use ($request) {
                $query->where('avaliacao_id', $request->avaliacao_id)
                    ->with('Avaliador:id,nome');
            })->orderBy('nome');

        $porPagina = $request->get('porPagina');
        $busca = false;
        if ($request->filled('campoBusca')) {
            $busca = $request->get('campoBusca');
            $resultado->where('nome', 'like', '%' . $busca . '%');
        }

        $resultado = $resultado->paginate($porPagina);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => $resultado->items(),
        ]);
    }
}
