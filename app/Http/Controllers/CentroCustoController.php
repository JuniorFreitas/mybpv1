<?php

namespace App\Http\Controllers;

use App\Models\CentroCusto;
use App\Models\Cliente;
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
                CentroCusto::create($dados);
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
        $centro = CentroCusto::find($id)->load('Gestor');
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
        $resultado = CentroCusto::with('Empresa','Gestor')->orderBy('id');

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
        $this->authorize('cadastro_centrocusto');

        $centro = CentroCusto::find($request->id);
        $centro->ativo = !$centro->ativo;
        $centro->save();
        $centro->refresh();
        return response()->json(['ativo' => $centro->ativo], 201);
    }
}
