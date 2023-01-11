<?php

namespace App\Mail\Admissao\Apontamento\Intermitente;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RespostaIntermitenteMail extends Mailable
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
        $empresa = $dados['empresa'];
        $this->dados = $dados;
        $this->to($this->dados['email_gestor'], $this->dados['gestor']);
        $this->from('naoresponda@mybp.com.br', $empresa);
        $this->subject = $empresa." - Resposta de Convocação Intermitente";
        $this->assunto = $this->subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.admissao.apontamento.respostaintermitente');
    }
}
