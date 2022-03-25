<?php

namespace App\Mail\Movimentacao\MudaCargoPrevista;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AprovacaoRhMail extends Mailable
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
        $this->subject = "CONFIRMAÇÃO RH PARA MUDANÇA DE CARGO PREVISTA  - COLABORADOR {$this->dados['colaborador']}  CÓD - ". $this->dados['ferias_id'];
        $this->assunto = $this->subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.movimentacao.mudacargoprevista.aprovar_rh');
    }
}
