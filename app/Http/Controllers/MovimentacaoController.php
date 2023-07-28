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

    public function listarAbas(Request $request){
        $permissoes = [
            'demissao' => auth()->user()->can('planejamento_movimentacao_exibir_aba_demissao'),
            'ferias' => auth()->user()->can('planejamento_movimentacao_exibir_aba_ferias'),
            'admissao' => auth()->user()->can('planejamento_movimentacao_exibir_aba_admissao'),
            'valorextra' => auth()->user()->can('planejamento_movimentacao_exibir_aba_lideranca_de_pessoal_valor_extra'),
            'mudacargo' => auth()->user()->can('planejamento_movimentacao_exibir_aba_mudanca_cargo'),
            'intermitente' => auth()->user()->can('planejamento_movimentacao_exibir_aba_mudanca_de_intermitente_para_fixo'),
            'transferencia' => auth()->user()->can('planejamento_movimentacao_exibir_aba_transferencia')
        ];

        $aba_ativa = array_search(true, $permissoes);

        return response()->json([
            'dados' => [
                'permissoes_abas' => $permissoes,
                'aba_ativa' => $aba_ativa,
            ]
        ]);
    }
    public function atualizar(Request $request)
    {
//        $resultado = RequisicaoVaga::with(
//            'Cliente',
//            'CentroCusto',
//            'Cargo',
//            'Area');

//        $resultado = $resultado->orderByDesc('created_at')->paginate($request->pages);
//
//        return response()->json([
//            'atual' => $resultado->currentPage(),
//            'ultima' => $resultado->lastPage(),
//            'total' => $resultado->total(),
//            'dados' => [
//                'itens' => $resultado->items(),
//            ]
//        ]);
    }
}
