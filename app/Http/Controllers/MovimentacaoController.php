<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class MovimentacaoController extends Controller
{
    public function index()
    {
        return view('g.planejamento.movimentacao.index');
    }

    public function atualizar(Request $request)
    {
//        $resultado = RequisicaoVaga::with(
//            'Cliente',
//            'CentroCusto',
//            'Cargo',
//            'Area');

        $resultado = $resultado->orderByDesc('created_at')->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
            ]
        ]);
    }
}
