<?php

namespace App\Mail\Avaliacoes;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AvaliacaoPendenciaMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $dados = [];

    public function __construct(array $dados)
    {
        $this->dados = $dados;
    }

    public function build()
    {
        return $this
            ->subject($this->dados['subject'])
            ->to($this->dados['email'], $this->dados['nome'])
            ->from(config('mail.from.address', 'naoresponda@mybp.com.br'), config('mail.from.name', 'MyBP - SISTEMA INTEGRADO DE SOLUÇÕES EM GESTÃO'))
            ->view('email.avaliacoes.pendencia-fluxo')
            ->with([
                'dados' => $this->dados,
                'subject' => $this->dados['subject'],
            ]);
    }
}
