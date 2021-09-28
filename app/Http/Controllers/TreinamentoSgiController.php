<?php

namespace App\Http\Controllers;

use App\Models\TreinamentoSgi;
use App\Models\User;
use DB;
use Illuminate\Http\Request;

class TreinamentoSgiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.cadastros.treinamentosgi.index');
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
                'nome' => 'required',
                'titulo_certificado' => 'required',
                'carga_horaria' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Cadastrar Treinamento SGI',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                TreinamentoSgi::create($dados);

                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE TREINAMENTO SGI:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return TreinamentoSgi|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Http\Response|object
     */
    public function edit($id)
    {
        return TreinamentoSgi::whereId($id)->first();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->authorize('cadastro_treinamento_sgi_update');
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados,
            [
                'nome' => 'required',
                'titulo_certificado' => 'required',
                'carga_horaria' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Cadastrar Treinamento SGI',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                $sgi = TreinamentoSgi::whereId($id)->first();

                $sgi->update($dados);

                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE TREINAMENTO SGI:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }


    public function atualizar(Request $request)
    {
        $resultado = TreinamentoSgi::whereNotNull('nome');

        if ($request->filled('campoBusca')) {
            $resultado->where('label', 'like', '%' . $request->campoBusca . '%');
        }

        $resultado = $resultado->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' =>
                [
                    'items' => $resultado->items()
                ]
        ]);
    }
}
