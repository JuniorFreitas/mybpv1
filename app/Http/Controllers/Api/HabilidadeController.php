<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Habilidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (!auth()->user()->tokenCan('habilidade_create')) {
            return response()->json([
                'msg' => 'Acesso negado',
                'error' => true
            ], 403);
        }

        $validator = Validator::make($request->input(), [
            'nome' => 'required|unique:habilidades|max:255',
            'descricao' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        return response()->json(Habilidade::create($request->input()), 201);
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
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Habilidade $habilidade
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, Habilidade $habilidade)
    {
        if (!auth()->user()->tokenCan('habilidade_update')) {
            return response()->json(['msg' => 'Acesso negado', 'error' => true], 403);
        }

        $validator = Validator::make($request->input(), [
            'nome' => "required|unique:habilidades,nome,{$habilidade->id}|max:255",
            'descricao' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages(), 400);
        }

        return response()->json($habilidade->update($request->input()));
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
