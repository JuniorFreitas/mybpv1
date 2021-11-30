<?php

namespace App\Mail\RequisicaoVagas;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AprovacaoMail extends Mailable
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
        $this->from('naoresponda@mybp.com.br', 'BPSE-BUSINESS PARTNERS SERVIÇOS EMPRESARIAIS');
        $this->subject = "ATUALIZAÇÃO NA REQUISIÇÃO DE VAGA - CARGO: ". $this->dados['cargo'];
        $this->assunto = $this->subject;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.requisicao.andamento');
    }
}
