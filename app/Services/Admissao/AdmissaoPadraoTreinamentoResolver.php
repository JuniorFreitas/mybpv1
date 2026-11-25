<?php

namespace App\Services\Admissao;

use App\Models\PadraoTreinamento;

/**
 * Resolve e valida o padrão de treinamento informado, garantindo que pertença à empresa do usuário.
 * Extraído de AdmissaoController::resolverPadraoTreinamentoIdEmpresa (TD-002 fatia 2).
 */
class AdmissaoPadraoTreinamentoResolver
{
    /**
     * @param  mixed  $padraoId  Valor bruto recebido do request (pode vir como string, int ou vazio).
     * @param  int|null  $empresaId  Empresa a validar; quando omitido usa a empresa do usuário autenticado.
     */
    public function resolver($padraoId, ?int $empresaId = null): ?int
    {
        if ($padraoId === null || $padraoId === '' || $padraoId === 0 || $padraoId === '0') {
            return null;
        }

        $padraoId = (int) $padraoId;
        if ($padraoId <= 0) {
            return null;
        }

        $empresaId ??= (int) auth()->user()->empresa_id;

        $existe = PadraoTreinamento::query()
            ->where('id', $padraoId)
            ->where('empresa_id', $empresaId)
            ->exists();

        return $existe ? $padraoId : null;
    }
}
