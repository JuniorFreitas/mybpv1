<?php

namespace App\Mail\Admissao\Processo;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VencimentoAsoMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $dados = [];
    public $subject;

    public function __construct($dados)
    {

        $this->dados = $dados;
        $this->subject = 'Lembrete de vencimento ASO';

        $this->to($this->dados['usuario']->login, $this->dados['usuario']->nome);
        $this->from(config('mail.from.address'), config('app.name'));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.admissao.processo.vencimentoAso');
    }
}
