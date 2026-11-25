<?php

namespace App\Http\Controllers;

use App\Http\Requests\Treinamentos\StorePcmsoRequest;
use App\Http\Requests\Treinamentos\UpdatePcmsoRequest;
use App\Models\Pcmso;
use DB;
use Illuminate\Http\Request;

class PcmsoController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(StorePcmsoRequest $request)
    {
        $this->authorize('cadastro_empresa_pcmso_insert');
        $dados = $request->validated() + $request->input();

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
    public function update(UpdatePcmsoRequest $request, $id)
    {
        $this->authorize('cadastro_empresa_pcmso_update');
        $dados = $request->validated() + $request->input();
        $pcmso = Pcmso::find($id);

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
