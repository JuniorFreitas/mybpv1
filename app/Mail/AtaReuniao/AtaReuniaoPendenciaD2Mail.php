<?php

namespace App\Mail\AtaReuniao;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AtaReuniaoPendenciaD2Mail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $dados)
    {
    }

    public function build()
    {
        $mail = $this
            ->subject($this->dados['subject'] ?? 'Pendencia proxima do vencimento')
            ->to($this->dados['email'], $this->dados['nome'] ?? null)
            ->from(config('mail.from.address', 'naoresponda@mybp.com.br'), config('mail.from.name', 'MyBP - SISTEMA INTEGRADO DE SOLUÇÕES EM GESTÃO'))
            ->view('email.atareuniao.pendencia-d2')
            ->with(['dados' => $this->dados]);

        foreach ($this->dados['cc'] ?? [] as $cc) {
            if (!empty($cc['email'])) {
                $mail->cc($cc['email'], $cc['nome'] ?? null);
            }
        }

        return $mail;
    }
}
