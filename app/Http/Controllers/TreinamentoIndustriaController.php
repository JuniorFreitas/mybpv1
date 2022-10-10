<?php

namespace App\Http\Controllers;

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
    public function store(Request $request)
    {
        $this->authorize('cadastro_treinamento_sgi_insert');
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados,
            [
                'label' => 'required',
                'descricao' => 'required',
                'ativo' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Cadastrar Treinamento Indústria',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                Vencimento::create($dados);

                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE TREINAMENTO INDUSTRIA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
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
        return Vencimento::whereId($id)->first();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Vencimento $vencimento
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->authorize('cadastro_treinamento_sgi_update');
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados,
            [
                'label' => 'required',
                'descricao' => 'required',
                'ativo' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Editar Treinamento Indústria',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                $vencimento = Vencimento::whereId($id)->first();

                $vencimento->update($dados);

                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error UPDATE TREINAMENTO INDUSTRIA:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
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
        $resultado = Vencimento::whereNotNull('label');

        if ($request->filled('campoBusca')) {
            $resultado->where('label', 'like', '%' . $request->campoBusca . '%');
        }

        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == 'true';
            $resultado->whereAtivo($status);
        }

        $resultado = $resultado->paginate($request->pages);

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
