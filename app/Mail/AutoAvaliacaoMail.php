<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AutoAvaliacaoMail extends Mailable
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
        $this->from('naoresponda@mybp.com.br', 'MyBP - SISTEMA INTEGRADO DE SOLUÇÕES EM GESTÃO');
        $this->subject = "Auto Avaliação Concluída - ".$this->dados['funcionario'];
        $this->assunto = $this->subject;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.avaliacoes.autoavaliacao');
    }
}
