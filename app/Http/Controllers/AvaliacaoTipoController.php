<?php

namespace App\Http\Controllers;

use App\Models\AvaliacaoTipo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AvaliacaoTipoController extends Controller
{
    public function index(Request $request)
    {
        return view('g.cadastros.avaliacoes.avaliacaotipo.index');

    }

    public function store(Request $request)
    {
//        $this->authorize('administracao_documentos_legais_insert');
        $dados = $request->input();
        $nome = $dados['nome'];
        $descricao = $dados['descricao'];

        $arrayValidacao = [
            'nome' => [
                'required',
                'min:3',
                Rule::unique('avaliacoes_tipos')->where(function ($query) use($nome,$descricao) {
                    return $query->where('nome',$nome)
                                 ->where('descricao',$descricao);
                })
            ]
        ];
        $dadosValidados = \Validator::make($dados, $arrayValidacao);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar tipo de avaliação',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

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
        return $avaliacaotipo;
    }

    public function show(AvaliacaoTipo $avaliacaotipo)
    {
//        $this->authorize('administracao_documentos_legais_insert');
        return $avaliacaotipo;
    }

    public function update(Request $request, AvaliacaoTipo $avaliacaotipo)
    {
//        $this->authorize('administracao_documentos_legais_insert');

//        dd($request->segment(5));
        $dados = $request->input();
        $nome = $dados['nome'];
        $descricao = $dados['descricao'];
        $id_tipo_avaliacao = $request->segment(5);
        $arrayValidacao = [
            'nome' => [
                'required',
                'min:3',
                Rule::unique('avaliacoes_tipos')->where(function ($query) use($nome,$descricao,$id_tipo_avaliacao) {
                    return $query->where('nome', $nome)
                                 ->where('descricao', $descricao)
                                 ->where('id','<>', $id_tipo_avaliacao);
                })
            ]
        ];
        $dadosValidados = \Validator::make($dados, $arrayValidacao);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao editar tipo de avaliação',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

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
//        $this->authorize('administracao_documentos_legais');
        $porPagina = $request->get('porPagina');
        $resultado = AvaliacaoTipo::orderBy('id');

        if ($request->filled('campoBusca')) {
            $resultado->where('nome', 'like', '%' . $request->campoBusca . '%')
                ->orWhere('descricao', 'like', '%' . $request->campoBusca . '%');
        }

        $resultado = $resultado->paginate($porPagina);

        $permissoes = [
//            'insert' => auth()->user()->can('administracao_documentos_legais_tipos_documentos_insert'),
//            'update' => auth()->user()->can('administracao_documentos_legais_tipos_documentos_update'),
//            'delete' => auth()->user()->can('administracao_documentos_legais_tipos_documentos_delete')
        ];

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => $resultado->items(),
                'permissoes' => $permissoes
            ]
        ], 200);
    }

    public function ativaDesativa(Request $request)
    {
//        $this->authorize('administracao_documentos_legais_insert');

        $avaliacaoTipo = AvaliacaoTipo::find($request->id);
        $avaliacaoTipo->ativo = !$avaliacaoTipo->ativo;
        $avaliacaoTipo->save();
        $avaliacaoTipo->refresh();
        return response()->json(['ativo' => $avaliacaoTipo->ativo], 201);
    }

}
