<?php

namespace App\Http\Controllers;

use App\Models\VagasAbertas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VagasAbertasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('vagas_abertas');
        return view('g.cadastros.vagas_abertas.index');
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('vagas_abertas_insert');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        $dadosValidados = \Validator::make($dados, [
            'vaga_id' => 'required',
            'municipio_id' => 'required'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Vaga',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();
                VagasAbertas::create($dados);
                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'msg' => $e->getMessage(),
                ], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\VagasAbertas $vagas_aberta
     * @return \Illuminate\Http\Response
     */
    public function show(VagasAbertas $vagas_aberta)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\VagasAbertas $vagas_aberta
     * @return \Illuminate\Http\Response
     */
    public function edit(VagasAbertas $vagas_aberta)
    {
        return $vagas_aberta->load('Municipio', 'Vaga');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\VagasAbertas $vagas_aberta
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, VagasAbertas $vagas_aberta)
    {
        $this->authorize('vagas_abertas_update');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        $dadosValidados = \Validator::make($dados, [
            'vaga_id' => 'required',
            'municipio_id' => 'required'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar Vaga',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();
                $vagas_aberta->update($dados);
                DB::commit();
                return response()->json([], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'msg' => $e->getMessage(),
                ], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\VagasAbertas $vagas_aberta
     * @return \Illuminate\Http\Response
     */
    public function destroy(VagasAbertas $vagas_aberta)
    {
        //
    }

    public function atualizar(Request $request)
    {
        $this->authorize('vagas_abertas');
        $resultado = VagasAbertas::with('Vaga', 'Municipio');
        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Vaga', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->campoBusca . '%');
            })->orWhere('id', $request->campoBusca);
        }
        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == 'true' ? true : false;
            $resultado->whereAtivo($status);
        }

        $resultado = $resultado->orderByDesc('updated_at')->paginate(50);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => $resultado->items()
        ]);
    }

    public function ativaDesativa(VagasAbertas $vagas_aberta)
    {
        $this->authorize('vagas_abertas_update');
        $vagas_aberta->ativo = !$vagas_aberta->ativo;
        $vagas_aberta->save();
        $vagas_aberta->refresh();
        return response()->json(['ativo' => $vagas_aberta->ativo], 201);
    }
}
