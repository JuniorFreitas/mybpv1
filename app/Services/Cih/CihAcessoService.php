<?php

namespace App\Services\Cih;

use App\Authorization\HabilidadeResolver;
use App\Models\CentroCusto;
use App\Models\Cih;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class CihAcessoService
{
    /**
     * Papel Montisol com visão ampliada por CCs da matriz (não por vínculo pessoal).
     * Workaround de 2024: "CIH SO PRA MONTISOL ajustar depois para todo mundo".
     */
    public const PAPEL_MONTISOL_CIH_MATRIZ = 113;

    /** CNPJ matriz Montisol (12.557.849/0001-40), só dígitos. */
    public const CNPJ_MATRIZ_MONTISOL = '12557849000140';

    public function __construct(
        private readonly HabilidadeResolver $habilidadeResolver,
    ) {
    }

    public function podeVerTodas(User $user): bool
    {
        return $this->habilidadeResolver->canAny($user, [
            'admissao_cih_privilegio_adm',
            'admissao_cih_ver_todas',
        ]);
    }

    public function podeAprovarComoGestor(User $user, Cih $cih): bool
    {
        if ($this->habilidadeResolver->can($user, 'admissao_cih_privilegio_adm')) {
            return true;
        }

        return (int) $cih->gestor_id === (int) $user->id;
    }

    /**
     * Resolve a query base de listagem/export conforme o escopo do usuário.
     * Prioridade: ver todas → CCs matriz Montisol (papel legado) → vinculados.
     */
    public function queryBaseComEscopo(User $user, array $relationships): Builder
    {
        if ($this->podeVerTodas($user)) {
            return Cih::with($relationships);
        }

        if ($this->usaEscopoCentrosCustoMatrizMontisol($user)) {
            return $this->queryCentrosCustoMatrizMontisol($user, $relationships);
        }

        return Cih::vinculados()->with($relationships);
    }

    public function usaEscopoCentrosCustoMatrizMontisol(User $user): bool
    {
        return (int) $user->grupo_id === self::PAPEL_MONTISOL_CIH_MATRIZ;
    }

    private function queryCentrosCustoMatrizMontisol(User $user, array $relationships): Builder
    {
        $centrosPorCnpj = (new CentroCusto())->listaCentroCustoPorCnpj($user->empresa_id);
        $centrosMatriz = collect($centrosPorCnpj['centros_custos'][self::CNPJ_MATRIZ_MONTISOL] ?? [])
            ->where('ativo', '=', true);

        return Cih::with($relationships)
            ->whereHas('CentroDeCusto', function (Builder $query) use ($centrosMatriz) {
                $query->whereIn('id', $centrosMatriz->pluck('id')->toArray());
            });
    }
}
