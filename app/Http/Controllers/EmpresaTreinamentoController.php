<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\EmpresaTreinamento;
use App\Models\User;
use DB;
use Illuminate\Http\Request;

class EmpresaTreinamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.cadastros.empresatreinamento.index');
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
        $this->authorize('cadastro_empresa_treinamento_insert');
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados,
            [
                'nome' => 'required',
                'endereco' => 'required',
                'ativo' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Cadastrar Empresa Treinamento',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                EmpresaTreinamento::create($dados);

                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE Empresa Treinamento:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return EmpresaTreinamento|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Http\Response|object
     */
    public function edit($id)
    {
        return EmpresaTreinamento::whereId($id)->first();
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
        $this->authorize('cadastro_empresa_treinamento_update');
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados,
            [
                'nome' => 'required',
                'endereco' => 'required',
                'ativo' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Atualizar Empresa Treinamento',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                $et = EmpresaTreinamento::whereId($id)->first();

                $et->update($dados);

                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error UPDATE Empresa Treinamento:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }


    public function atualizar(Request $request)
    {
        $resultado = EmpresaTreinamento::whereNotNull('nome');


        if ($request->filled('campoBusca')) {
            $resultado->where('nome', 'like', '%' . $request->campoBusca . '%');
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

    public function ativaDesativa(Request $request)
    {
        $this->authorize('cadastro_empresa_treinamento');

        $treinamento = EmpresaTreinamento::find($request->id);

        $treinamento->ativo = !$treinamento->ativo;
        $treinamento->save();
        $treinamento->refresh();
        return response()->json(['ativo' => $treinamento->ativo], 201);
    }
}
