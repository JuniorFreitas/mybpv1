<?php

namespace App\Services\Notificacao;

use App\Models\NotificacaoTipo;

/**
 * API central do motor de notificações.
 * Na 1ª entrega o foco é resolução de destinatários (e-mail RH via grupos).
 */
class NotificacaoEngine
{
    public function __construct(
        private readonly NotificacaoDestinatarioResolver $resolver
    ) {
    }

    /**
     * @param  array{centro_custo_id?: int|null, matriz?: bool|null}  $contexto
     * @return list<string>
     */
    public function emailsParaTipo(string $tipoCodigo, int $empresaId, array $contexto = []): array
    {
        return $this->resolver->emails($empresaId, $tipoCodigo, $contexto);
    }

    /**
     * @param  array{centro_custo_id?: int|null, matriz?: bool|null}  $contexto
     * @return list<string>
     */
    public function emailsAprovacaoRh(int $empresaId, array $contexto = []): array
    {
        return $this->emailsParaTipo(NotificacaoTipo::APROVACAO_RH, $empresaId, $contexto);
    }

    public function invalidarEmpresa(int $empresaId): void
    {
        $this->resolver->invalidarEmpresa($empresaId);
    }
}
