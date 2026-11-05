<?php

namespace App\Domain\SaudeOcupacional\Seguranca;

use App\Models\User;

/**
 * Registra quem viu ou exportou dado sensível de saúde (LGPD art. 11).
 *
 * Grava só referências (tipo e id do registro), nunca o conteúdo (CID, laudo, atestado).
 */
class AuditoriaAcessoSaude
{
    public const LOG_NAME = 'saude_ocupacional_acesso';

    public const ACAO_VISUALIZAR = 'visualizar';

    public const ACAO_EXPORTAR = 'exportar';

    public const ACAO_BAIXAR_ANEXO = 'baixar_anexo';

    /**
     * @param  string  $recurso  ex.: 'afastamento', 'ficha_saude'
     */
    public function registrar(User $user, string $acao, string $recurso, ?int $recursoId, string $descricao): void
    {
        activity(self::LOG_NAME)
            ->causedBy($user)
            ->withProperties([
                'empresa_id' => (int) $user->empresa_id,
                'acao' => $acao,
                'recurso' => $recurso,
                'recurso_id' => $recursoId,
                'ip' => request()?->ip(),
            ])
            ->event($acao)
            // activity_log do MyBP exige a coluna custom `descricao` (NOT NULL).
            ->tap(function ($activity) use ($descricao) {
                $activity->descricao = $descricao;
            })
            ->log($descricao);
    }
}
