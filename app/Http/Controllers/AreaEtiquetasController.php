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
            return $query->whereEmpresaId(auth()->user()->empresaAtivaId())
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
        $area = AreaEtiqueta::find($id)->load('Gestor');
        $area->autocomplete_label_gestor_modal = $area->Gestor ? $area->Gestor->nome : '';
        $area->autocomplete_label_gestor_modal_anterior = $area->Gestor ? $area->Gestor->nome : '';
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
            return $query->whereEmpresaId(auth()->user()->empresaAtivaId())
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
        $resultado = AreaEtiqueta::with('Gestor:id,nome,empresa_id', 'CentroCusto:id,empresa_id,label')->orderBy('id');

        if ($request->filled('campoBusca')) {
            $termo = trim((string) $request->campoBusca);
            $resultado->where(function ($query) use ($termo) {
                $query->where('label', 'like', '%' . $termo . '%');
                if (is_numeric($termo)) {
                    $query->orWhere('id', (int) $termo);
                }
            });
        }

        if ($request->filled('campoStatus')) {
            $status = filter_var($request->campoStatus, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($status !== null) {
                $resultado->whereAtivo($status);
            }
        }

        $resultado = $resultado->paginate($porPagina);
        $items = collect($resultado->items());
        $supervisores = DB::table('cliente_area_etiquetas')
            ->whereIn('area_etiqueta_id', $items->pluck('id'))
            ->pluck('numero_supervisor', 'area_etiqueta_id');

        $items = $items->map(function (AreaEtiqueta $area) use ($supervisores) {
            $area->gestor_nome = $area->Gestor?->nome;
            $area->centro_custo_label = $area->CentroCusto?->label;
            $area->numero_supervisor = $supervisores[$area->id] ?? null;

            return $area;
        });

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => $items->values()->all(),
                'empresa_id' => auth()->user()->empresaAtivaId(),
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
