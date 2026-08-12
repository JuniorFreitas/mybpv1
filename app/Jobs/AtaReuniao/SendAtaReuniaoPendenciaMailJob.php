<?php

namespace App\Jobs\AtaReuniao;

use App\Mail\AtaReuniao\AtaReuniaoPendenciaD2Mail;
use App\Models\AtaReuniaoNotificacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAtaReuniaoPendenciaMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $notificacaoId)
    {
    }

    public function handle(): void
    {
        $notificacao = AtaReuniaoNotificacao::find($this->notificacaoId);

        if (!$notificacao || $notificacao->status === 'enviado') {
            return;
        }

        try {
            Mail::send(new AtaReuniaoPendenciaD2Mail([
                ...($notificacao->payload ?? []),
                'subject' => $notificacao->assunto,
                'email' => $notificacao->destinatario_email,
                'nome' => $notificacao->destinatario_nome,
            ]));

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
