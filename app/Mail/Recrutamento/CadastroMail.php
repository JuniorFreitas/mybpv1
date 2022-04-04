<?php

namespace App\Mail\Movimentacao\AdmissaoPrevista;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CadastroMail extends Mailable
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
        $this->to($this->dados['email'], $this->dados['nome_para']);
        $this->from('naoresponda@mybp.com.br', $this->dados['nome']);
        $this->subject = "Confirmação de Cadastro";
        $this->assunto = $this->subject;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.recrutamento.cadastro');
    }
}
