<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreHabilidadeApiRequest;
use App\Http\Requests\Api\UpdateHabilidadeApiRequest;
use App\Models\Habilidade;
use Illuminate\Http\Request;

class HabilidadeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function filtro(Request $request)
    {
        $resultado = Habilidade::orderBy('nome', $request->ordem ?: 'Asc');
        if ($request->filled('campoBusca')) {
            $resultado->where("nome", "like", "%$request->campoBusca%")
                ->orWhere('id', $request->campoBusca);
        }
        return $resultado;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->porPag ?: 20);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreHabilidadeApiRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreHabilidadeApiRequest $request)
    {
        return response()->json(Habilidade::create($request->validated()), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Habilidade $habilidade
     * @return Habilidade|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function show(Habilidade $habilidade)
    {
        return response()->json($habilidade);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateHabilidadeApiRequest $request
     * @param \App\Models\Habilidade $habilidade
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(UpdateHabilidadeApiRequest $request, Habilidade $habilidade)
    {
        return response()->json($habilidade->update($request->validated()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Habilidade $habilidade
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function destroy(Habilidade $habilidade)
    {
        if (!auth()->user()->tokenCan('habilidade_delete')) {
            return response()->json(['msg' => 'Acesso negado', 'error' => true], 403);
        }

        $habilidade->delete();
        return response()->json([]);
    }
}
