<?php

namespace App\Http\Controllers;

use App\Models\CentroCusto;
use App\Models\CentroCustoFilial;
use App\Models\CentroCustoGestor;
use App\Models\ClienteFilial;
use App\Services\CentroCusto\CentroCustoCnpjSyncService;
use App\Services\CentroCusto\CentroCustoGestorSyncService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CentroCustoController extends Controller
{
    public function __construct(
        private readonly CentroCustoGestorSyncService $gestorSyncService,
        private readonly CentroCustoCnpjSyncService $cnpjSyncService
    ) {
    }

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

        $regras = ['label' => ['required', $regra]];
        if ((new ClienteFilial())->temFilial()) {
            $regras['campoCnpj'] = ['required', 'string'];
        }

        $dadosValidados = \Validator::make($dados, $regras);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar Centro de Custos',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();
                $centro = CentroCusto::create($dados);
                $this->gestorSyncService->sincronizar(
                    $centro,
                    $dados['gestor_id'] ?? null,
                    $dados['gestor_substituto_id'] ?? null
                );
                $this->cnpjSyncService->sincronizar($centro, $dados['campoCnpj'] ?? null);
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
        $centro = CentroCusto::find($id)->load([
            'Gestor',
            'GestorSubstituto.Usuario',
            'Filiais:id,centro_custo_id,cliente_filial_id,empresa_id,ativo',
        ]);

        $centro->autocomplete_label_gestor_modal = $centro->Gestor ? $centro->Gestor->nome : '';
        $centro->autocomplete_label_gestor_modal_anterior = $centro->Gestor ? $centro->Gestor->nome : '';
        $centro->gestor_substituto_id = $centro->GestorSubstituto?->usuario_id ?? '';
        $centro->autocomplete_label_gestor_substituto_modal = $centro->GestorSubstituto?->Usuario?->nome ?? '';
        $centro->autocomplete_label_gestor_substituto_modal_anterior = $centro->autocomplete_label_gestor_substituto_modal;
        $centro->campo_cnpj = $this->cnpjSyncService->resolverCampoCnpj($centro);

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

        $regras = ['label' => ['required', $regra]];
        if ((new ClienteFilial())->temFilial()) {
            $regras['campoCnpj'] = ['required', 'string'];
        }

        $dadosValidados = \Validator::make($dados, $regras);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar Centro de Custos',
                'erros' => $dadosValidados->errors()
            ], 400);

        } else {
            try {
                DB::beginTransaction();
                $centro->update($dados);
                $this->gestorSyncService->sincronizar(
                    $centro,
                    $dados['gestor_id'] ?? null,
                    $dados['gestor_substituto_id'] ?? null
                );
                $this->cnpjSyncService->sincronizar($centro, $dados['campoCnpj'] ?? null);
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
        $resultado = CentroCusto::with([
            'Empresa',
            'Gestor:id,nome,login,ativo',
            'GestorSubstituto.Usuario:id,nome,login,ativo',
            'Filiais' => function ($query) {
                $query->where('ativo', true)
                    ->whereNull('deleted_at')
                    ->with('Filial:id,dados');
            },
        ])
            ->withCount(['Filiais' => function ($query) {
                $query->where('ativo', true)->whereNull('deleted_at');
            }])
            ->orderBy('id');

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

        $listaCcs = null;
        if ($request->filled('campoCnpj')) {
            $listaCcs = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);
            $grupoCentros = $listaCcs['centros_custos'][$request->campoCnpj] ?? collect();

            if ($grupoCentros->isNotEmpty()) {
                $resultado->whereIn('id', $grupoCentros->pluck('id')->all());
            } else {
                $resultado->whereRaw('1 = 0');
            }
        }

        $resultado = $resultado->paginate($porPagina);

        if ($listaCcs === null) {
            $listaCcs = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);
        }

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => $resultado->items(),
                'lista_ccs' => $listaCcs,
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
