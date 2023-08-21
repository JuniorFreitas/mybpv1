<?php

namespace App\Mail\Movimentacao\MudaCargoPrevista;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CriadaMail extends Mailable
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
        $this->to($this->dados['email_para'], $this->dados['nome_para']);
        $this->from('naoresponda@mybp.com.br', 'MyBP - SISTEMA INTEGRADO DE SOLUÇÕES EM GESTÃO');
        $this->subject = "MUDANÇA DE CARGO - COLABORADOR ".$this->dados['colaborador']." - CÓD: ".$this->dados['id'];
        $this->assunto = $this->subject;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.movimentacao.mudacargoprevista.criada');
    }
}
