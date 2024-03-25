<?php

namespace App\Http\Controllers;

use App\Models\AvaliacaoAvaliadoresTipos;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AvaliadorTipoController extends Controller
{
    public function index(Request $request)
    {
        return view('g.cadastros.avaliacoes.avaliadortipo.index');
    }

    public function store(Request $request)
    {
        $this->authorize('cadastro_avaliador_tipo_insert');
        $dados = $request->input();
        $label = $dados['label'];

        $arrayValidacao = [
            'label' => [
                'required',
                'min:3'
            ]
        ];
        $dadosValidados = \Validator::make($dados, $arrayValidacao);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar tipo de avaliador',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            AvaliacaoAvaliadoresTipos::create($dados);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE TIPO DE Avaliador:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->label;
            \Log::debug($msg);
            return response()->json(['msg' => $e->getMessage()], 400);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function edit(AvaliacaoAvaliadoresTipos $avaliadortipo)
    {
        $this->authorize('cadastro_avaliador_tipo_update');
        return $avaliadortipo;
    }

    public function show(AvaliacaoAvaliadoresTipos $avaliadortipo)
    {
        $this->authorize('cadastro_avaliador_tipo_show');
        return $avaliadortipo;
    }

    public function update(Request $request, AvaliacaoAvaliadoresTipos $avaliadortipo)
    {
        $this->authorize('cadastro_avaliador_tipo_update');

        $dados = $request->input();
        $arrayValidacao = [
            'label' => [
                'required',
                'min:3'
            ]
        ];
        $dadosValidados = \Validator::make($dados, $arrayValidacao);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao editar tipo de avaliador',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            $avaliadortipo->update($dados);
            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error UPDATE TIPO DE Avaliador:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->label;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function atualizar(Request $request)
    {
        $this->authorize('cadastro_avaliador_tipo');
        $porPagina = $request->get('porPagina');
        $resultado = AvaliacaoAvaliadoresTipos::orderBy('id');

        if ($request->filled('campoBusca')) {
            $resultado->where('label', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('descricao', 'like', '%' . $request->campoBusca . '%');
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
        $this->authorize('cadastro_avaliador_tipo_active');

        $avaliacaoTipo = AvaliacaoAvaliadoresTipos::find($request->id);
        $avaliacaoTipo->ativo = !$avaliacaoTipo->ativo;
        $avaliacaoTipo->save();
        $avaliacaoTipo->refresh();
        return response()->json(['ativo' => $avaliacaoTipo->ativo], 201);
    }

}
