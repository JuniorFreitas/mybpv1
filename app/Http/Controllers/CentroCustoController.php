<?php

namespace App\Http\Controllers;

use App\Models\CentroCusto;
use App\Models\CentroCustoFilial;
use App\Models\ClienteFilial;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CentroCustoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.cadastros.centrocusto.index');
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
        $this->authorize('cadastro_centrocusto_insert');
        $dados = $request->input();
        $dados['ativo'] = $dados['ativo'] == 'true';

        $regra = Rule::unique('centro_custos')->where(function ($query) use ($dados) {
            return $query->whereEmpresaId(auth()->user()->empresa_id)
                ->whereLabel($dados['label']);
        });

        $dadosValidados = \Validator::make($dados, [
            'label' => ['required', $regra]
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar Centro de Custos',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();
                $centro = CentroCusto::create($dados);

                $filial = new ClienteFilial();
                if ($filial->temFilial()) {
                    foreach ($dados['filiais'] as $filial) {
                        $filial['empresa_id'] = auth()->user()->empresa_id;
                        $filial['centro_custo_id'] = $centro->id;
                        $filial['ativo'] = $filial['selecionado'];

                        if ($filial['selecionado']) {
                            CentroCustoFilial::create($filial);
                        }
                    }
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
     * @return CentroCusto|CentroCusto[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|\Illuminate\Http\Response
     */
    public function edit($id)
    {
        $centro = CentroCusto::find($id)->load('Gestor', 'Filiais:id,centro_custo_id,cliente_filial_id,empresa_id,ativo');

        $centro->autocomplete_label_gestor_modal = $centro->Gestor ? $centro->Gestor->nome : '';
        $centro->autocomplete_label_gestor_modal_anterior = $centro->Gestor ? $centro->Gestor->nome : '';
        return $centro;
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
        $this->authorize('cadastro_centrocusto_update');
        $dados = $request->input();
        $centro = CentroCusto::find($id);
        $dados['ativo'] = $dados['ativo'] == 'true';

        $regra = Rule::unique('centro_custos')->where(function ($query) use ($dados) {
            return $query->whereEmpresaId(auth()->user()->empresa_id)
                ->whereLabel($dados['label']);
        })->ignore($centro->id);

        $dadosValidados = \Validator::make($dados, [
            'label' => ['required', $regra]
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar Centro de Custos',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();
                $centro->update($dados);
                $filial = new ClienteFilial();
                if ($filial->temFilial()) {
                    foreach ($dados['filiais'] as $filial) {
                        $filial['empresa_id'] = auth()->user()->empresa_id;
                        $filial['centro_custo_id'] = $centro->id;
                        $filial['ativo'] = $filial['selecionado'];

                        $centroCustoFilial = CentroCustoFilial::where('empresa_id', $filial['empresa_id'])->where('centro_custo_id', $filial['centro_custo_id'])->where('cliente_filial_id', $filial['id']);

                        if ($centroCustoFilial->count() == 0 && $filial['selecionado']) {
                            CentroCustoFilial::create($filial);
                        }
                        if ($centroCustoFilial->count() > 0) {
                            $centroCustoFilial->update(['ativo' => $filial['selecionado']]);
                        }

                    }
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
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }


    public function atualizar(Request $request)
    {
        $this->authorize('cadastro_centrocusto');
        $porPagina = $request->get('porPagina');
        $resultado = CentroCusto::with('Empresa', 'Gestor')
            ->withCount('Filiais')
            ->orderBy('id');

        if ($request->filled('campoBusca')) {
            $resultado->where('label', 'like', '%' . $request->campoBusca . '%');
        }

        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == 'true';
            $resultado->whereAtivo($status);
        }

        $resultado = $resultado->paginate($porPagina);

        $filial = new ClienteFilial();
        if ($filial->temFilial()) {
            $listaFilial = $filial->getListaFilialAtiva();
        }

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => $resultado->items(),
                'listaFilial' => $listaFilial ?? null
            ]
        ], 200);
    }

    public function ativaDesativa(Request $request)
    {
        $this->authorize('cadastro_centrocusto');

        $centro = CentroCusto::find($request->id);
        $centro->ativo = !$centro->ativo;
        $centro->save();
        $centro->refresh();
        return response()->json(['ativo' => $centro->ativo], 201);
    }

    public function getFiliais(Request $request)
    {
        $resposta = CentroCustoFilial::where('ativo', $request->ativo ?? true)
            ->where('empresa_id', auth()->user()->empresa_id ?? $request->empresa_id)
            ->with('Filial')
            ->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'razao_social' => $item->Filial->dados->razao_social,
                ];
            });

        return response()->json($resposta, 200);
    }

    public function getFiliaisCentroDeCusto(Request $request)
    {

        $resposta = CentroCustoFilial::where('ativo',true)->where('centro_custo_id', $request->centro_custo_id)
            ->where('empresa_id', auth()->user()->empresa_id ?? $request->empresa_id)
            ->with('Filial')
            ->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'razao_social' => $item->Filial->dados->razao_social,
                ];
            });

        return response()->json($resposta, 200);
    }
}
