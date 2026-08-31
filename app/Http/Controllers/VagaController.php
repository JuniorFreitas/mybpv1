<?php

namespace App\Http\Controllers;

use App\Models\Vaga;
use App\Models\Vencimento;
use App\Tenant\Scopes\ScopeEmpresaGrupo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VagaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('cadastro_vagas');
        return view('g.cadastros.vagas.index');
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
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('cadastro_vagas_insert');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;
        $dados['cbo_id'] = empty($dados['cbo_id']) ? null : (int) $dados['cbo_id'];
        unset($dados['vencimento_ids']);

        $empresaIdsDoGrupo = ScopeEmpresaGrupo::empresaIdsDoGrupo(auth()->user()->empresaAtivaId());

        $dadosValidados = \Validator::make($dados, [
            'nome' => [
                'required',
                Rule::unique('vagas')->where(function ($query) use ($request, $empresaIdsDoGrupo) {
                    return $query->whereNome($request->nome)->whereIn('empresa_id', $empresaIdsDoGrupo);
                }),
            ],
            'ativo' => 'required|boolean',
            'cbo_id' => 'nullable|integer|exists:cbos,id',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Vaga',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            $vaga = Vaga::create($dados);
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
     * @param \App\Models\Vaga $vaga
     * @return \Illuminate\Http\Response
     */
    public function show(Vaga $vaga)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Vaga $vaga
     * @return \Illuminate\Http\Response
     */
    public function edit(Vaga $vaga)
    {
        $vaga->load(
            'Vencimentos:id,label,segmento_treinamento_id,vinculo_todos_cargos',
            'Vencimentos.SegmentoTreinamento:id,nome',
            'Cbo:id,codigo,titulo,codigo_familia',
            'Cbo.familia:codigo,titulo,descricao_sumaria'
        );
        $vaga->vencimento_ids = $vaga->Vencimentos->pluck('id')->values();
        $vaga->autocomplete_label_cbo = $vaga->Cbo ? sprintf(
            '%s - %s - %s',
            $vaga->Cbo->codigo,
            $vaga->Cbo->titulo,
            $vaga->Cbo->familia?->titulo ?? 'Família não informada'
        ) : '';
        $vaga->cbo_codigo = $vaga->Cbo?->codigo;
        $vaga->codigo_familia = $vaga->Cbo?->codigo_familia ?? $vaga->Cbo?->familia?->codigo;
        $vaga->cbo_titulo = $vaga->Cbo?->titulo;
        $vaga->cbo_familia = $vaga->Cbo?->familia?->titulo;
        $vaga->cbo_descricao_sumaria = $vaga->Cbo?->familia?->descricao_sumaria;
        $vaga->treinamentos_globais_count = Vencimento::query()
            ->whereAtivo(true)
            ->where('vinculo_todos_cargos', true)
            ->count();

        return $vaga;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Vaga $vaga
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, Vaga $vaga)
    {
        $this->authorize('cadastro_vagas_update');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;
        $dados['cbo_id'] = empty($dados['cbo_id']) ? null : (int) $dados['cbo_id'];
        unset($dados['vencimento_ids']);

        $empresaIdsDoGrupo = ScopeEmpresaGrupo::empresaIdsDoGrupo(auth()->user()->empresaAtivaId());

        $dadosValidados = \Validator::make($dados, [
            'nome' => [
                'required',
                Rule::unique('vagas')->ignore($vaga->id)->where(function ($query) use ($request, $empresaIdsDoGrupo) {
                    return $query->whereNome($request->nome)->whereIn('empresa_id', $empresaIdsDoGrupo);
                }),
            ],
            'ativo' => 'required|boolean',
            'cbo_id' => 'nullable|integer|exists:cbos,id',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar Vaga',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            $vaga->update($dados);
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
     * @param \App\Models\Vaga $vaga
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vaga $vaga)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $this->authorize('cadastro_vagas');

        $resultado = Vaga::with(
            'Cbo:id,codigo,titulo,codigo_familia',
            'Cbo.familia:codigo,titulo',
            'Vencimentos:id,label,segmento_treinamento_id,vinculo_todos_cargos',
            'Vencimentos.SegmentoTreinamento:id,nome'
        )->orderBy('nome');
        if ($request->filled('campoBusca')) {
            $termo = $request->campoBusca;
            $resultado->where(function ($query) use ($termo) {
                $query->where('nome', 'like', '%' . $termo . '%')
                    ->orWhere('id', $termo);
            });
        }
        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == 'true';
            $resultado->whereAtivo($status);
        }

        $resultado = $resultado->paginate(50);
        $treinamentosGlobaisCount = Vencimento::query()
            ->whereAtivo(true)
            ->where('vinculo_todos_cargos', true)
            ->count();

        $dados = collect($resultado->items())->map(function (Vaga $vaga) use ($treinamentosGlobaisCount) {
            $vaga->cbo_label = $vaga->Cbo ? sprintf(
                '%s - %s - %s',
                $vaga->Cbo->codigo,
                $vaga->Cbo->titulo,
                $vaga->Cbo->familia?->titulo ?? 'Família não informada'
            ) : null;
            $vaga->cbo_codigo = $vaga->Cbo?->codigo;
            $vaga->cbo_codigo_familia = $vaga->Cbo?->codigo_familia ?? $vaga->Cbo?->familia?->codigo;
            $vaga->cbo_titulo = $vaga->Cbo?->titulo;
            $vaga->cbo_familia = $vaga->Cbo?->familia?->titulo;
            $vaga->cbo_vinculado = (bool) $vaga->Cbo;

            $treinamentosVinculados = $vaga->Vencimentos->values();
            $vaga->treinamentos_vinculados_count = $treinamentosVinculados->count();
            $vaga->treinamentos_globais_count = $treinamentosGlobaisCount;
            $vaga->treinamentos_total_count = $vaga->treinamentos_vinculados_count + $treinamentosGlobaisCount;

            unset($vaga->Vencimentos);

            return $vaga;
        })->values();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => $dados
        ]);
    }

    public function ativaDesativa(Vaga $vaga)
    {
        $this->authorize('cadastro_vagas_update');
        $vaga->ativo = !$vaga->ativo;
        $vaga->save();
        $vaga->refresh();
        return response()->json(['ativo' => $vaga->ativo], 201);
    }

    private function normalizeIds($ids): array
    {
        if (!is_array($ids)) {
            return [];
        }

        return collect($ids)
            ->filter(function ($id) {
                return is_numeric($id);
            })
            ->map(function ($id) {
                return (int) $id;
            })
            ->unique()
            ->values()
            ->all();
    }
}
