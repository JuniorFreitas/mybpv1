<?php

namespace App\Jobs\Avaliacoes;

use App\Mail\Avaliacoes\AvaliacaoPendenciaMail;
use App\Models\AvaliacaoNotificacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAvaliacaoPendenciaMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $notificacaoId;

    public function __construct(int $notificacaoId)
    {
        $this->notificacaoId = $notificacaoId;
    }

    public function handle(): void
    {
        $notificacao = AvaliacaoNotificacao::withoutGlobalScopes()->find($this->notificacaoId);

        if (!$notificacao) {
            return;
        }

        try {
            \Mail::send(new AvaliacaoPendenciaMail($notificacao->payload ?? []));

            $notificacao->update([
                'status' => 'enviado',
                'enviado_em' => now(),
                'erro' => null,
            ]);
        } catch (\Throwable $e) {
            $notificacao->update([
                'status' => 'erro',
                'erro' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
