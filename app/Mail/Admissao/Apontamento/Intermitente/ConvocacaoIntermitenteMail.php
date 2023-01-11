<?php

namespace App\Mail\Admissao\Apontamento\Intermitente;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ConvocacaoIntermitenteMail extends Mailable
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
        $this->to($this->dados['email'], $this->dados['colaborador']);
        $this->from('naoresponda@mybp.com.br', $empresa);
        $this->subject = $empresa." - Convocação Intermitente";
        $this->assunto = $this->subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.admissao.apontamento.convocacaointermitente');
    }
}
