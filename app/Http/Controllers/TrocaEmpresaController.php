<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Fase 4 — troca de empresa ativa (workspace switcher). Acesso é automático
 * dentro do grupo da empresa de casa do usuário (grupo = limite de
 * multi-tenancy); user_empresas só entra pra concessões entre grupos
 * diferentes. Ver User::empresasAcessiveis()/temAcessoEmpresa().
 */
class TrocaEmpresaController extends Controller
{
    public function listar(): JsonResponse
    {
        $usuario = auth()->user();

        return response()->json([
            'empresas' => $usuario->empresasAcessiveis(),
            'empresa_ativa_id' => $usuario->empresaAtivaId(),
        ]);
    }

    public function trocar(Request $request): JsonResponse
    {
        $request->validate([
            'empresa_id' => 'required|integer',
        ]);

        $usuario = auth()->user();
        $empresaId = (int) $request->input('empresa_id');

        if (! $usuario->temAcessoEmpresa($empresaId)) {
            return response()->json(['message' => 'Você não tem acesso a essa empresa.'], 403);
        }

        $usuario->empresa_ativa_id = $empresaId;
        $usuario->save();

        return response()->json([
            'message' => 'Empresa ativa atualizada.',
            'empresa_ativa_id' => $usuario->empresaAtivaId(),
        ]);
    }
}
