<?php

namespace App\Services\CentroCusto;

use App\Models\CentroCusto;
use App\Models\CentroCustoGestor;

class CentroCustoGestorSyncService
{
    public function sincronizar(CentroCusto $centro, ?int $gestorPrincipalId, ?int $gestorSubstitutoId): void
    {
        $this->sincronizarTipo($centro, CentroCustoGestor::TIPO_GESTOR_PRINCIPAL, $gestorPrincipalId);
        $this->sincronizarTipo($centro, CentroCustoGestor::TIPO_GESTOR_SUBSTITUTO, $gestorSubstitutoId);

        if ($gestorPrincipalId && (int) $centro->gestor_id !== (int) $gestorPrincipalId) {
            $centro->update(['gestor_id' => $gestorPrincipalId]);
        }
    }

    private function sincronizarTipo(CentroCusto $centro, string $tipo, ?int $usuarioId): void
    {
        CentroCustoGestor::query()
            ->where('centro_custo_id', $centro->id)
            ->where('tipo', $tipo)
            ->update(['ativo' => false]);

        if (!$usuarioId) {
            return;
        }

        $existente = CentroCustoGestor::query()
            ->where('centro_custo_id', $centro->id)
            ->where('tipo', $tipo)
            ->where('usuario_id', $usuarioId)
            ->first();

        if ($existente) {
            $existente->update(['ativo' => true, 'empresa_id' => $centro->empresa_id]);
            return;
        }

        CentroCustoGestor::create([
            'centro_custo_id' => $centro->id,
            'usuario_id' => $usuarioId,
            'tipo' => $tipo,
            'ativo' => true,
            'empresa_id' => $centro->empresa_id,
        ]);
    }
}
