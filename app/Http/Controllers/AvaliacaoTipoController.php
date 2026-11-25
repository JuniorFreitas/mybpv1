<?php

namespace App\Http\Controllers;

use App\Http\Requests\Avaliacao\StoreAvaliacaoTipoRequest;
use App\Http\Requests\Avaliacao\UpdateAvaliacaoTipoRequest;
use App\Models\AvaliacaoTipo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AvaliacaoTipoController extends Controller
{
    public function index(Request $request)
    {
        return view('g.cadastros.avaliacoes.avaliacaotipo.index');
    }

    public function indexPj(Request $request)
    {
        return view('g.cadastros.avaliacoes-pj.avaliacaotipo.index');
    }

    public function store(StoreAvaliacaoTipoRequest $request)
    {
        $this->authorize('cadastro_avaliacao_tipo_insert');
        $dados = $request->input();

        try {
            DB::beginTransaction();

            AvaliacaoTipo::create($dados);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE TIPO DE AVALIAÇÃO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function edit(AvaliacaoTipo $avaliacaotipo)
    {
        $this->authorize('cadastro_avaliacao_tipo_update');
        return $avaliacaotipo;
    }

    public function show(AvaliacaoTipo $avaliacaotipo)
    {
        $this->authorize('cadastro_avaliacao_tipo_show');
        return $avaliacaotipo;
    }

    public function update(UpdateAvaliacaoTipoRequest $request, AvaliacaoTipo $avaliacaotipo)
    {
        $this->authorize('cadastro_avaliacao_tipo_update');

        $dados = $request->input();

        try {
            DB::beginTransaction();

            $avaliacaotipo->update($dados);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error UPDATE TIPO DE AVALIAÇÃO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function atualizar(Request $request)
    {
        $this->authorize('cadastro_avaliacao_tipo');
        $porPagina = $request->get('porPagina');
        $resultado = AvaliacaoTipo::orderBy('id');

        if ($request->filled('campoBusca')) {
            $resultado->where('nome', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('descricao', 'like', '%' . $request->campoBusca . '%');
        }

        if ($request->filled('tipo_pj')) {
            $resultado->where('tipo_pj', $request->tipo_pj);
        }

        $resultado = $resultado->paginate($porPagina);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => $resultado->items(),
            ]
        ], 200);
    }

    public function ativaDesativa(Request $request)
    {
        $this->authorize('cadastro_avaliacao_tipo_active');

        $avaliacaoTipo = AvaliacaoTipo::find($request->id);
        $avaliacaoTipo->ativo = !$avaliacaoTipo->ativo;
        $avaliacaoTipo->save();
        $avaliacaoTipo->refresh();
        return response()->json(['ativo' => $avaliacaoTipo->ativo], 201);
    }

}
