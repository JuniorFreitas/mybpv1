<?php

namespace App\Jobs\Movimentacao\Concerns;

use App\Services\Movimentacao\MovimentacaoWhatsappNotificationService;

trait EnviaWhatsappNotificacaoMovimentacao
{
    protected function enviarWhatsappAposEmail(array $dados, array $destinatarios, string $modulo): void
    {
        if ($destinatarios === [] || empty($dados['empresa_id'])) {
            return;
        }

        app(MovimentacaoWhatsappNotificationService::class)->enviarNotificacaoAprovacao(
            (int) $dados['empresa_id'],
            $destinatarios,
            $modulo,
            (string) ($dados['tipo'] ?? ''),
            (string) ($dados['colaborador'] ?? ''),
            (string) ($dados['url'] ?? route('g.movimentacao.index')),
            (string) ($dados['nome_aprovacao_extra'] ?? 'Aprovação Extra'),
        );
    }
}
