<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\VagaProjeto;
use Illuminate\Http\Request;

class ProjetoController extends Controller
{

    public function index(Request $request)
    {
        $Empresa = Cliente::select(['id', 'razao_social', 'apelido'])
            ->withoutGlobalScopes()
            ->whereApelido($request->segment(2))
            ->first();

        if (!$Empresa) {
            return response()->json([
                'msg' => 'Empresa não encontrada',
                'success' => false
            ], 404);
        }

        $Projetos = VagaProjeto::withoutGlobalScopes()
            ->where('empresa_id', $Empresa->id)
            ->with(
                [
                    'Projeto' => function ($query) {
                        $query->select(['id', 'nome', 'qnt_total', 'qnt_total_restante', 'preenchidas'])->withoutGlobalScopes();
                    },
                    'VagaAberta' => function ($query) {
                        $query->select(['id', 'vaga_id', 'titulo', 'municipio_id'])
                            ->with([
                                'Cargo' => function ($query) {
                                    $query->withoutGlobalScopes()
                                        ->select(['id', 'nome']);
                                },
                                'Municipio:id,nome,uf'
                            ])
                            ->whereAtivoSistema(true)
                            ->withoutGlobalScopes();
                    }
                ],
            )
            ->get()
            ->groupBy('projeto_id')->values();

        return response()->json([
            'dados' => $Projetos,
            'success' => true
        ], 200);
    }
}
