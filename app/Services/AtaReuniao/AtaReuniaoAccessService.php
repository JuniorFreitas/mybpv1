<?php

namespace App\Services\AtaReuniao;

use App\Models\AtaReuniao;
use App\Models\AtaReuniaoAcesso;
use App\Models\User;

class AtaReuniaoAccessService
{
    private const STATUS_BLOQUEADOS_EDICAO_COMUM = [
        'aprovada',
        'publicada',
        'encerrada',
        'cancelada',
    ];

    public function canView(User $user, AtaReuniao $ata): bool
    {
        if ((int) $ata->empresa_id !== (int) $user->empresa_id) {
            return false;
        }

        if ($user->can('administracao_atareuniao_privilegio_adm')) {
            return true;
        }

        if ((int) $ata->quem_cadastrou === (int) $user->id) {
            return true;
        }

        if ((int) $ata->organizador_id === (int) $user->id || (int) $ata->redator_id === (int) $user->id) {
            return true;
        }

        return AtaReuniaoAcesso::withoutGlobalScopes()
            ->where('empresa_id', $user->empresa_id)
            ->where('ata_reuniao_id', $ata->id)
            ->where('user_id', $user->id)
            ->whereNull('revogado_em')
            ->where(function ($query) {
                $query->whereNull('expira_em')->orWhere('expira_em', '>', now());
            })
            ->exists()
            || $ata->Participantes()->where('user_id', $user->id)->exists()
            || $ata->Acoes()->where('responsavel_id', $user->id)->exists();
    }

    public function canEdit(User $user, AtaReuniao $ata): bool
    {
        if (!$this->canView($user, $ata)) {
            return false;
        }

        if ($ata->bloqueada_em || in_array((string) $ata->status, self::STATUS_BLOQUEADOS_EDICAO_COMUM, true)) {
            return false;
        }

        if (!$user->can('administracao_atareuniao_update')) {
            return false;
        }

        if ($user->can('administracao_atareuniao_privilegio_adm')) {
            return true;
        }

        if ((int) $ata->quem_cadastrou === (int) $user->id) {
            return true;
        }

        if ((int) $ata->organizador_id === (int) $user->id || (int) $ata->redator_id === (int) $user->id) {
            return true;
        }

        return AtaReuniaoAcesso::withoutGlobalScopes()
            ->where('empresa_id', $user->empresa_id)
            ->where('ata_reuniao_id', $ata->id)
            ->where('user_id', $user->id)
            ->where('papel', AtaReuniaoAcesso::PAPEL_EDITOR)
            ->whereNull('revogado_em')
            ->exists();
    }

    public function concederAcessoMinimoPendencia(AtaReuniao $ata, User $responsavel): void
    {
        AtaReuniaoAcesso::withoutGlobalScopes()->updateOrCreate([
            'empresa_id' => $ata->empresa_id,
            'ata_reuniao_id' => $ata->id,
            'user_id' => $responsavel->id,
            'papel' => AtaReuniaoAcesso::PAPEL_RESPONSAVEL_PENDENCIA,
        ], [
            'origem' => 'pendencia',
            'revogado_em' => null,
        ]);
    }
}
