<?php

namespace App\Http\Controllers;

use App\Models\AreaEtiqueta;
use App\Models\Vaga;
use DB;
use Illuminate\Http\Request;

class AreaEtiquetasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('g.cadastros.areas.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('areaetiqueta');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        $dadosValidados = \Validator::make($dados, [
            'label' => 'required'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Área',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();
                AreaEtiqueta::create($dados);
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
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return AreaEtiqueta|AreaEtiqueta[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|\Illuminate\Http\Response
     */
    public function edit($id)
    {
        $area = AreaEtiqueta::find($id);
        return $area;
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
        $this->authorize('areaetiqueta');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;

        $dadosValidados = \Validator::make($dados, [
            'label' => 'required'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar Área',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();
                $area = AreaEtiqueta::find($id);
                $area->update($dados);
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

    public function atualizar(Request $request)
    {
        $this->authorize('areaetiqueta');
        $porPagina = $request->get('porPagina');
        $resultado = AreaEtiqueta::orderBy('id');

        if ($request->filled('campoBusca')) {
            $resultado->where('label', 'like', '%' . $request->campoBusca . '%');
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
        $this->authorize('areaetiqueta');

        $area = AreaEtiqueta::find($request->id);
        $area->ativo = !$area->ativo;
        $area->save();
        $area->refresh();
        return response()->json(['ativo' => $area->ativo], 201);
    }
}
