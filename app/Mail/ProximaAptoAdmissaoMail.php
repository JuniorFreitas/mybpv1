<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProximaAptoAdmissaoMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $dados = [];

    public function __construct(array $dados)
    {
        $this->dados = $dados;
        $this->to($this->dados['email'], $this->dados['nome']);
        $this->from('naoresponda@mybp.com.br', 'BPSE-BUSINESS PARTNERS SERVIÇOS EMPRESARIAIS');
        $this->subject = "Parabéns!";
        $this->assunto = $this->subject;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.recrutamento.aptoAdmissao');
    }
}
