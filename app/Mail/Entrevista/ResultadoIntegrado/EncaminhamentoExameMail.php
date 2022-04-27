<?php

namespace App\Mail\Entrevista\ResultadoIntegrado;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EncaminhamentoExameMail extends Mailable
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
        $this->to($this->dados['colaborador']->email, $this->dados['colaborador']->nome);
        $this->cc($this->dados['clinica']->dados['email'], $this->dados['clinica']->nome);
        $this->from('naoresponda@mybp.com.br', "MyBP - SISTEMA INTEGRADO DE SOLUÇÕES EM GESTÃO");
        $this->subject = "Encaminhamento de Exame - {$this->dados['colaborador']->nome}";
        $this->assunto = $this->subject;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.entrevista.resultado_integrado.encaminhamento_exame');
    }
}
