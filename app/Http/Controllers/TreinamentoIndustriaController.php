<?php

namespace App\Http\Controllers;

use App\Http\Requests\Treinamentos\StoreTreinamentoIndustriaRequest;
use App\Http\Requests\Treinamentos\UpdateTreinamentoIndustriaRequest;
use App\Models\User;
use App\Models\Vencimento;
use DB;
use Illuminate\Http\Request;

class TreinamentoIndustriaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.cadastros.treinamentoindustria.index');
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
    public function store(StoreTreinamentoIndustriaRequest $request)
    {
        $this->authorize('cadastro_treinamento_sgi_insert');
        $dados = $request->validated() + $request->input();
        $cargoIds = $dados['cargo_ids'] ?? [];
        $padraoIds = $dados['padrao_treinamento_ids'] ?? [];
        unset($dados['cargo_ids'], $dados['padrao_treinamento_ids'], $dados['padroes'], $dados['padroes_treinamento']);

        try {
            DB::beginTransaction();

            if(!$dados['exibir_na_carteira']){
                $dados['label_reduzida'] = null;
            }

            $vencimento = Vencimento::create($dados);
            $vencimento->Vagas()->sync($dados['vinculo_todos_cargos'] ? [] : $cargoIds);
            $vencimento->PadroesTreinamento()->sync($padraoIds);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE TREINAMENTO INDUSTRIA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Vencimento $vencimento
     * @return \Illuminate\Http\Response
     */
    public function show(Vencimento $vencimento)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Vencimento $vencimento
     * @return Vencimento|\Illuminate\Http\Response
     */
    public function edit($id)
    {
        $vencimento = Vencimento::with([
            'PadroesTreinamento:id,nome,slug,ativo',
            'Vagas:id,nome',
        ])->whereId($id)->first();
        if (!$vencimento) {
            return $vencimento;
        }

        $vencimento->cargo_ids = $vencimento->Vagas->pluck('id')->values();
        $vencimento->padrao_treinamento_ids = $vencimento->PadroesTreinamento->pluck('id')->values();

        return $vencimento;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Vencimento $vencimento
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateTreinamentoIndustriaRequest $request, $id)
    {
        $this->authorize('cadastro_treinamento_sgi_update');
        $dados = $request->validated() + $request->input();
        $cargoIds = $dados['cargo_ids'] ?? [];
        $padraoIds = $dados['padrao_treinamento_ids'] ?? [];
        unset($dados['cargo_ids'], $dados['padrao_treinamento_ids'], $dados['padroes'], $dados['padroes_treinamento']);

        try {
            DB::beginTransaction();

            $vencimento = Vencimento::whereId($id)->first();

            if(!$dados['exibir_na_carteira']){
                $dados['label_reduzida'] = null;
            }

            $vencimento->update($dados);
            $vencimento->Vagas()->sync($dados['vinculo_todos_cargos'] ? [] : $cargoIds);
            $vencimento->PadroesTreinamento()->sync($padraoIds);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error UPDATE TREINAMENTO INDUSTRIA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Vencimento $vencimento
     * @return \Illuminate\Http\Response
     */
    public function destroy(Vencimento $vencimento)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $porPagina = (int) $request->get('porPagina', $request->get('pages', 20));
        $porPagina = $porPagina > 0 ? $porPagina : 20;

        $resultado = Vencimento::with('PadroesTreinamento:id,nome,slug,ativo')
            ->withCount('Vagas')
            ->whereNotNull('label');

        if ($request->filled('campoBusca')) {
            $termo = trim((string) $request->campoBusca);
            $resultado->where(function ($query) use ($termo) {
                $query->where('label', 'like', '%' . $termo . '%')
                    ->orWhere('descricao', 'like', '%' . $termo . '%');

                if (is_numeric($termo)) {
                    $query->orWhere('id', (int) $termo);
                }
            });
        }

        if ($request->filled('campoStatus')) {
            $status = filter_var($request->campoStatus, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($status !== null) {
                $resultado->whereAtivo($status);
            }
        }

        if ($request->filled('padrao_treinamento_id')) {
            $padraoFiltro = (int) $request->input('padrao_treinamento_id');
            $resultado->whereHas('PadroesTreinamento', function ($query) use ($padraoFiltro) {
                $query->where('padroes_treinamento.id', $padraoFiltro);
            });
        }

        $resultado = $resultado->paginate($porPagina);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' =>
                ['items' => $resultado->items()
                ]
        ]);
    }

    public function ativaDesativa(Request $request)
    {
        $this->authorize('cadastro_treinamento_industria');

        $treinamento = Vencimento::find($request->id);

        $treinamento->ativo = !$treinamento->ativo;
        $treinamento->save();
        $treinamento->refresh();
        return response()->json(['ativo' => $treinamento->ativo], 201);
    }

}
