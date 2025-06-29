<?php

namespace App\Http\Controllers;

use App\Models\AvaliacaoTipo;
use App\Models\AvaliacaoTopico;
use App\Models\Simulado;
use DB;
use Illuminate\Http\Request;

class AvaliacaoTopicoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.cadastros.avaliacoes.avaliacaotopico.index');
    }

    public function indexPj()
    {
        return view('g.cadastros.avaliacoes-pj.avaliacaotopico.index');
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
        $this->authorize('cadastro_avaliacao_topico_insert');
        $dados = $request->input();
        $topico = [
            'topico' => $dados['topico'],
            'avaliacao_tipo_id' => $dados['avaliacao_tipo_id'],
            'topico_explicacao' => $dados['topico_explicacao'],
            'ativo' => $dados['ativo'],
            'tipo_pj' => $dados['tipo_pj'],
        ];

        if (!isset($dados['subtopicos'])) {
            return response()->json([
                'msg' => 'ERRO: É necessário inserir subtópicos',
            ], 400);
        }

        $dadosValidados = \Validator::make($dados, [
            'topico' => 'required',
            'avaliacao_tipo_id' => 'required',
            'subtopico.*.topico' => 'required',
            'ativo' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Tópicos',
                'erros' => $dadosValidados->errors()
            ], 400);

        }
        try {
            DB::beginTransaction();
            $subtopicos = collect($dados['subtopicos']);

            if ($subtopicos->duplicates('topico')->count() > 0) {
                return response()->json([
                    'msg' => "Verifique os subtópicos com nomes duplicados!",
                ], 400);
            }

            if ($subtopicos->duplicates('topico_explicacao')->count() > 0) {
                return response()->json([
                    'msg' => "Verifique os subtópicos com descrições duplicados!",
                ], 400);
            }

            if ($subtopicos->count() == 0) {
                return response()->json([
                    'msg' => 'ERRO: O Tópico ' . $topico['topico'] . ' deve ter pelo menos 1(um) subtópico',
                ], 400);
            }

            //Cria um registro Topico e salva o id
            $cadTopico = AvaliacaoTopico::create($topico);

            if (isset($dados['subtopicosDelete'])) {
                foreach ($dados['subtopicosDelete'] as $lin) {
                    AvaliacaoTopico::find($lin)->delete();
                }
            }

            foreach ($subtopicos as $subtopico) {
                $subtopico['avaliacao_tipo_id'] = $dados['avaliacao_tipo_id'];
                $subtopico['topico_pai_id'] = $cadTopico->id;

                AvaliacaoTopico::create($subtopico);
            }

            DB::commit();
            return response()->json([], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'msg' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\AvaliacaoTopico $avaliacaoTopico
     * @return \Illuminate\Http\Response
     */
    public function show(AvaliacaoTopico $avaliacaoTopico)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\AvaliacaoTopico $avaliacaotopico
     * @return Simulado|\Illuminate\Http\Response
     */
    public function edit(AvaliacaoTopico $avaliacaotopico)
    {
        $this->authorize('cadastro_avaliacao_topico_update');
        return $avaliacaotopico->load('Subtopicos');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\AvaliacaoTopico $avaliacaoTopico
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, AvaliacaoTopico $avaliacaoTopico)
    {
        $this->authorize('cadastro_avaliacao_topico_update');
        $dados = $request->input();

        $topico = [
            'id' => $dados['id'],
            'topico' => $dados['topico'],
            'avaliacao_tipo_id' => $dados['avaliacao_tipo_id'],
            'topico_explicacao' => $dados['topico_explicacao'],
            'ativo' => $dados['ativo'],
            'tipo_pj' => $dados['tipo_pj'],
        ];

        if (!isset($dados['subtopicos'])) {
            return response()->json([
                'msg' => 'ERRO: É necessário inserir subtópicos',
            ], 400);
        }

        $dadosValidados = \Validator::make($dados, [
            'topico' => 'required',
            'avaliacao_tipo_id' => 'required',
            'subtopico.*.topico' => 'required',
            'ativo' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar tópicos de avaliações',
                'erros' => $dadosValidados->errors()
            ], 400);

        }
        try {
            DB::beginTransaction();
            $subtopicos = collect($dados['subtopicos']);

            if ($subtopicos->duplicates('topico')->count() > 0) {
                return response()->json([
                    'msg' => "Verifique os subtópicos com nomes duplicados!",
                ], 400);
            }

            if ($subtopicos->count() == 0) {
                return response()->json([
                    'msg' => 'ERRO: O Tópico ' . $topico['topico'] . ' deve ter pelo menos 1(um) subtópico',
                ], 400);
            }

            $updateTopico = AvaliacaoTopico::find($dados['id'])->update($topico);

            if (isset($dados['subtopicosDelete'])) {
                foreach ($dados['subtopicosDelete'] as $lin) {
                    AvaliacaoTopico::find($lin)->delete();
                }
            }

            foreach ($subtopicos as $subtopico) {
                $subtopico['avaliacao_tipo_id'] = $dados['avaliacao_tipo_id'];
                $subtopico['topico_pai_id'] = $dados['id'];

                if (isset($subtopico['id'])) {
                    AvaliacaoTopico::find($subtopico['id'])->update($subtopico);
                } else {
                    AvaliacaoTopico::create($subtopico);
                }
            }

            DB::commit();
            return response()->json([], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'msg' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\AvaliacaoTopico $avaliacaoTopico
     * @return \Illuminate\Http\Response
     */
    public function destroy(AvaliacaoTopico $avaliacaoTopico)
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function filtro(Request $request)
    {
        $resultado = AvaliacaoTopico::TopicosPais()->withCount('Subtopicos as qnt_subtopicos')->with('AvaliacaoTipo')->orderBy('topico', $request->ordem ?: 'Asc');
        if ($request->filled('campoBusca')) {
            $resultado->where("topico", "like", "%$request->campoBusca%")
                ->orWhere('id', $request->campoBusca);
        }
        if ($request->filled('tipo_pj')) {
            $resultado->where('tipo_pj', $request->tipo_pj);
        }
        return $resultado;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function atualizar(Request $request)
    {
        $this->authorize('cadastro_avaliacao_topico');
        $resultado = $this->filtro($request)->paginate($request->porPag ?: 20);
        $avaliacoes_tipos = AvaliacaoTipo::whereAtivo(true)->where('tipo_pj', $request->tipo_pj)->get();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'avaliacoes_tipos' => $avaliacoes_tipos,
            ]
        ]);
    }

    /**
     * Ativa e desativa.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ativaDesativa(Request $request)
    {
        $avaliacaoTopico = AvaliacaoTopico::find($request->id);
        $avaliacaoTopico->ativo = !$avaliacaoTopico->ativo;
        $avaliacaoTopico->save();
        $avaliacaoTopico->refresh();

        AvaliacaoTopico::whereTopicoPaiId($request->id)->update(['ativo' => $avaliacaoTopico->ativo]);


        return response()->json(['ativo' => $avaliacaoTopico->ativo], 201);
    }
}
