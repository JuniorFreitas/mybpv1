<?php

namespace App\Http\Controllers;

use App\Models\Pcmso;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PcmsoController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('cadastro_empresa_pcmso_insert');
        $dados = $request->input();

        $regra = Rule::unique('pcmsos')->where(function ($query) use ($dados) {
            return $query->whereEmpresaId(auth()->user()->empresa_id)
                ->whereLabel($dados['label']);
        });

        $dadosValidados = \Validator::make($dados, [
            'label' => ['required', $regra],
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar PCMSO',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            Pcmso::create($dados);
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
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Pcmso|Pcmso[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|\Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pcmso = Pcmso::find($id);
        return $pcmso;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->authorize('cadastro_empresa_pcmso_update');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true';
        $pcmso = Pcmso::find($id);

        $regra = Rule::unique('pcmsos')->where(function ($query) use ($dados) {
            return $query->whereEmpresaId(auth()->user()->empresa_id)
                ->whereLabel($dados['label']);
        })->ignore($pcmso->id);

        $dadosValidados = \Validator::make($dados, [
            'label' => ['required', $regra],
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar PCMSO',
                'erros' => $dadosValidados->errors()
            ], 400);

        }
        try {
            DB::beginTransaction();
            $pcmso->update($dados);
            DB::commit();
            return response()->json([], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'msg' => $e->getMessage(),
            ], 400);
        }
    }

    public function atualizar(Request $request)
    {
        $this->authorize('cadastro_empresa_pcmso');
        $porPagina = $request->get('porPagina');
        $resultado = Pcmso::orderBy('id');

        if ($request->filled('campoBusca')) {
            $resultado->where('label', 'like', '%' . $request->campoBusca . '%');
        }

        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == 'true';
            $resultado->whereAtivo($status);
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
        $this->authorize('cadastro_empresa_pcmso_update');

        $pcmso = Pcmso::find($request->id);
        $pcmso->ativo = !$pcmso->ativo;
        $pcmso->save();
        $pcmso->refresh();
        return response()->json(['ativo' => $pcmso->ativo], 201);
    }
}
