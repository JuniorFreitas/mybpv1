<?php

namespace App\Jobs\Concerns;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

trait EnviaMailComBcc
{
    /**
     * Envia o mailable para o 1º destinatário em To e o restante em BCC.
     * Mailpit (host.docker.internal) preserva To/BCC — não há mais bypass de Mailtrap.
     *
     * @param  list<string>  $destinatarios
     */
    protected function enviarMailComBcc(array $destinatarios, object $mailable, ?string $tipoLog = null): void
    {
        $lista = array_values(array_unique(array_filter(array_map(
            static fn ($email) => is_string($email) ? trim($email) : '',
            $destinatarios
        ))));

        if ($lista === []) {
            return;
        }

        $to = $lista[0];
        $bcc = array_slice($lista, 1);

        if ($tipoLog !== null) {
            Log::info("Preparando email - Tipo: {$tipoLog}, Destinatários: " . implode(', ', $lista));
            Log::info(
                "Enviando email para: {$to} - Tipo: {$tipoLog}"
                . ($bcc !== [] ? '; BCC: ' . implode(', ', $bcc) : '')
            );
        }

        $email = Mail::to($to);
        if ($bcc !== []) {
            $email->bcc($bcc);
        }
        $email->send($mailable);
    }
}
