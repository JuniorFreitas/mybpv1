<?php

namespace App\Http\Controllers;

use App\Models\AreaEtiqueta;
use App\Models\Vaga;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $this->authorize('cadastro_areaetiqueta');
        $dados = $request->input();

        $regra = Rule::unique('area_etiquetas')->where(function ($query) use ($dados) {
            return $query->whereEmpresaId(auth()->user()->empresa_id)
                ->whereLabel($dados['label']);
        });

        $dadosValidados = \Validator::make($dados, [
            'label' => ['required', $regra],
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Área',
                'erros' => $dadosValidados->errors()
            ], 400);

        }

        try {
            DB::beginTransaction();
            $areaEtiqueta = AreaEtiqueta::create($dados);
            DB::table('cliente_area_etiquetas')->insert([
                'cliente_id' => $areaEtiqueta->empresa_id,
                'area_etiqueta_id' => $areaEtiqueta->id,
                'numero_supervisor' => $dados['numero_supervisor'],
            ]);
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
     * @return AreaEtiqueta|AreaEtiqueta[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|\Illuminate\Http\Response
     */
    public function edit($id)
    {
        $area = AreaEtiqueta::find($id);
        $area->numero_supervisor = DB::table('cliente_area_etiquetas')->where('area_etiqueta_id', $id)->value('numero_supervisor');
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
        $this->authorize('cadastro_areaetiqueta');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true' ? true : false;
        $area = AreaEtiqueta::find($id);

        $regra = Rule::unique('area_etiquetas')->where(function ($query) use ($dados) {
            return $query->whereEmpresaId(auth()->user()->empresa_id)
                ->whereLabel($dados['label']);
        })->ignore($area->id);

        $dadosValidados = \Validator::make($dados, [
            'label' => ['required', $regra],
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar Área',
                'erros' => $dadosValidados->errors()
            ], 400);

        }
        try {
            DB::beginTransaction();

            $area->update($dados);
            $clienteArea = DB::table('cliente_area_etiquetas')->where('area_etiqueta_id', $id);
            if ($clienteArea->count() > 0) {
                $clienteArea->update([
                    'numero_supervisor' => $dados['numero_supervisor'],
                ]);
            } else {
                DB::table('cliente_area_etiquetas')->insert([
                    'cliente_id' => $area->empresa_id,
                    'area_etiqueta_id' => $area->id,
                    'numero_supervisor' => $dados['numero_supervisor'],
                ]);
            }

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
        $this->authorize('cadastro_areaetiqueta');
        $porPagina = $request->get('porPagina');
        $resultado = AreaEtiqueta::orderBy('id');

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
        $this->authorize('cadastro_areaetiqueta');

        $area = AreaEtiqueta::find($request->id);
        $area->ativo = !$area->ativo;
        $area->save();
        $area->refresh();
        return response()->json(['ativo' => $area->ativo], 201);
    }
}
