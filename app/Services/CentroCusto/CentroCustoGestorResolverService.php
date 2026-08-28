<?php

namespace App\Services\CentroCusto;

use App\Models\CentroCusto;
use App\Models\CentroCustoGestor;
use App\Models\User;
use DomainException;
use Illuminate\Support\Carbon;

class CentroCustoGestorResolverService
{
    public function getGestorPrincipal(int $centroCustoId): ?User
    {
        $gestor = CentroCustoGestor::query()
            ->where('centro_custo_id', $centroCustoId)
            ->where('tipo', CentroCustoGestor::TIPO_GESTOR_PRINCIPAL)
            ->where('ativo', true)
            ->where(function ($query) {
                $query->whereNull('fim_vigencia')
                    ->orWhere('fim_vigencia', '>=', Carbon::today()->toDateString());
            })
            ->with('Usuario')
            ->first();

        if ($gestor?->Usuario && $this->usuarioAtivo($gestor->Usuario)) {
            return $gestor->Usuario;
        }

        $centro = CentroCusto::query()
            ->select(['id', 'gestor_id'])
            ->with(['Gestor' => fn ($q) => $q->select(['id', 'nome', 'login', 'ativo', 'gestor_superior_id'])])
            ->find($centroCustoId);

        $fallback = $centro?->Gestor;

        return $fallback && $this->usuarioAtivo($fallback) ? $fallback : null;
    }

    public function getGestorSubstituto(int $centroCustoId): ?User
    {
        $gestor = CentroCustoGestor::query()
            ->where('centro_custo_id', $centroCustoId)
            ->where('tipo', CentroCustoGestor::TIPO_GESTOR_SUBSTITUTO)
            ->where('ativo', true)
            ->where(function ($query) {
                $query->whereNull('fim_vigencia')
                    ->orWhere('fim_vigencia', '>=', Carbon::today()->toDateString());
            })
            ->with('Usuario')
            ->first();

        $usuario = $gestor?->Usuario;

        return $usuario && $this->usuarioAtivo($usuario) ? $usuario : null;
    }

    /**
     * @throws DomainException
     */
    public function resolverAprovador(int $centroCustoId, int $solicitanteId): User
    {
        $principal = $this->getGestorPrincipal($centroCustoId);

        if (!$principal) {
            throw new DomainException(
                'Não foi possível iniciar a transferência. O centro de custo não possui um gestor responsável configurado. Entre em contato com o administrador do sistema.'
            );
        }

        $candidatos = array_filter([
            $principal,
            $this->getGestorSubstituto($centroCustoId),
            $principal->GestorSuperior,
        ]);

        foreach ($candidatos as $candidato) {
            if ($candidato && $this->usuarioAtivo($candidato) && (int) $candidato->id !== $solicitanteId) {
                return $candidato;
            }
        }

        throw new DomainException(
            'Não foi possível iniciar a transferência. Não há aprovador válido disponível para o centro de custo selecionado (evitar autoaprovação). Entre em contato com o administrador do sistema.'
        );
    }

    public function usuarioAtivo(?User $user): bool
    {
        return $user !== null && (bool) $user->ativo;
    }
}
