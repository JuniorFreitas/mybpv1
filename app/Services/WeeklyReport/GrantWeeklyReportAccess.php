<?php

namespace App\Services\WeeklyReport;

use App\Models\Habilidade;
use App\Models\Papel;
use App\Models\User;

/**
 * Garante a habilidade weekly_report no papel (grupo) do usuário convidado.
 * No MyBP as permissões vêm de papeis_habilidades.
 */
class GrantWeeklyReportAccess
{
    public const HABILIDADE = 'weekly_report';

    /**
     * @return bool true se o usuário passou a ter (ou já tinha) a habilidade
     */
    public function handle(User $user): bool
    {
        $grupoId = (int) ($user->grupo_id ?? 0);
        if ($grupoId <= 0) {
            return false;
        }

        $habilidadeId = Habilidade::query()
            ->where('nome', self::HABILIDADE)
            ->value('id');

        if (!$habilidadeId) {
            return false;
        }

        // Sem ScopeEmpresa: o papel do convidado pode ser master (empresa_id null)
        $papel = Papel::withoutGlobalScopes()->find($grupoId);
        if (!$papel) {
            return false;
        }

        $papelEmpresa = (int) ($papel->empresa_id ?? 0);
        $userEmpresa = (int) ($user->empresa_id ?? 0);
        if ($papelEmpresa > 0 && $userEmpresa > 0 && $papelEmpresa !== $userEmpresa) {
            return false;
        }

        $papel->habilidades()->syncWithoutDetaching([(int) $habilidadeId]);
        $user->limparCacheHabilidades();

        return true;
    }
}
