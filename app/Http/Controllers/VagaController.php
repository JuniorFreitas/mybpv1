<?php

namespace App\Http\Controllers;

use App\Models\Vaga;
use App\Models\Vencimento;
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
        $vencimentoIds = $this->normalizeIds($dados['vencimento_ids'] ?? []);
        unset($dados['vencimento_ids']);
        $vencimentoIds = $this->filtrarVencimentosSemVinculoTodosCargos($vencimentoIds);

        $dadosValidados = \Validator::make($dados, [
            'nome' => [
                'required',
                Rule::unique('vagas')->where(function ($query) use ($request) {
                    return $query->whereNome($request->nome)->whereEmpresaId(auth()->user()->empresa_id);
                }),
            ],
            'ativo' => 'required|boolean',
        ]);

        $vencimentosValidados = \Validator::make(
            ['vencimento_ids' => $vencimentoIds],
            [
                'vencimento_ids' => 'nullable|array',
                'vencimento_ids.*' => 'integer',
                'vencimento_ids.*' => Rule::exists('vencimentos', 'id')->where(function ($query) {
                    $query->whereAtivo(true);
                }),
            ]
        );

        if ($vencimentosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao cadastrar Vaga',
                'erros' => $vencimentosValidados->errors(),
            ], 400);
        }

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Vaga',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            $vaga = Vaga::create($dados);
            $vaga->Vencimentos()->sync($vencimentoIds);
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
        $vaga->load('Vencimentos:id,label,segmento_treinamento_id', 'Vencimentos.SegmentoTreinamento:id,nome');
        $vaga->vencimento_ids = $vaga->Vencimentos->pluck('id')->values();
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
        $vencimentoIds = $this->normalizeIds($dados['vencimento_ids'] ?? []);
        unset($dados['vencimento_ids']);
        $vencimentoIds = $this->filtrarVencimentosSemVinculoTodosCargos($vencimentoIds);

        $dadosValidados = \Validator::make($dados, [
            'nome' => [
                'required',
                Rule::unique('vagas')->ignore($vaga->id)->where(function ($query) use ($request) {
                    return $query->whereNome($request->nome)->whereEmpresaId(auth()->user()->empresa_id);
                }),
            ],
            'ativo' => 'required|boolean',
        ]);

        $vencimentosValidados = \Validator::make(
            ['vencimento_ids' => $vencimentoIds],
            [
                'vencimento_ids' => 'nullable|array',
                'vencimento_ids.*' => 'integer',
                'vencimento_ids.*' => Rule::exists('vencimentos', 'id')->where(function ($query) {
                    $query->whereAtivo(true);
                }),
            ]
        );

        if ($vencimentosValidados->fails()) {
            return response()->json([
                'msg' => 'Erro ao atualizar Vaga',
                'erros' => $vencimentosValidados->errors()
            ], 400);
        }

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar Vaga',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            $vaga->update($dados);
            $vaga->Vencimentos()->sync($vencimentoIds);
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
        $resultado = Vaga::orderBy('nome');
        if ($request->filled('campoBusca')) {
            $resultado->where('nome', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('id', $request->campoBusca);
        }
        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == 'true';
            $resultado->whereAtivo($status);
        }

        $resultado = $resultado->paginate(50);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => $resultado->items()
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

    /**
     * Treinamentos com "vinculo a todos os cargos" nao usam a pivot por cargo.
     */
    private function filtrarVencimentosSemVinculoTodosCargos(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $excluir = Vencimento::whereIn('id', $ids)->where('vinculo_todos_cargos', true)->pluck('id')->all();

        return collect($ids)->diff($excluir)->values()->all();
    }
}
