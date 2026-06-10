<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

/**
 * Recrutamento (currículo) na integração SPA v2: mesma regra do legado em /api/busca-*,
 * com empresa resolvida pelo apelido na URL (X-API-TOKEN obrigatório).
 */
class IntegracaoSpaCurriculoController extends Controller
{
    public function __construct(
        private readonly VagaAbertaController $vagaAberta,
    ) {
    }

    public function buscaCurriculo(Request $request, string $apelido)
    {
        $empresaId = $this->empresaIdFromApelido($apelido);
        if ($empresaId === null) {
            return response()->json([
                'msg' => 'Empresa não encontrada',
                'success' => false,
            ], 404);
        }

        $request->merge(['empresa_id' => $empresaId]);

        return $this->vagaAberta->buscaCurriculo($request);
    }

    public function buscaCpf(Request $request, string $apelido)
    {
        $empresaId = $this->empresaIdFromApelido($apelido);
        if ($empresaId === null) {
            return response()->json([
                'msg' => 'Empresa não encontrada',
                'success' => false,
            ], 404);
        }

        $request->merge(['empresa_id' => $empresaId]);

        return $this->vagaAberta->buscaCpf($request);
    }

    public function cadastraCurriculo(Request $request, string $apelido)
    {
        $empresaId = $this->empresaIdFromApelido($apelido);
        if ($empresaId === null) {
            return response()->json([
                'msg' => 'Empresa não encontrada',
                'success' => false,
            ], 404);
        }

        $request->merge(['empresa_id' => $empresaId]);

        return $this->vagaAberta->store($request);
    }

    private function empresaIdFromApelido(string $apelido): ?int
    {
        $id = Cliente::withoutGlobalScopes()
            ->where('apelido', $apelido)
            ->where('ativo', true)
            ->value('id');

        return $id !== null ? (int) $id : null;
    }
}
