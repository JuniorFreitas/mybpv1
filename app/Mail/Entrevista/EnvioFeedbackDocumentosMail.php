<?php

namespace App\Mail\Entrevista;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EnvioFeedbackDocumentosMail extends Mailable
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
        $nome_fantasia = \DB::table('clientes')->select(['nome_fantasia'])->find($this->dados['empresa_id']);

        $this->to($this->dados['email'], $this->dados['nome']);
        $this->from('naoresponda@mybp.com.br', $nome_fantasia);
        $this->subject = "Feedback de Documentos PRÉ-ADMISSÃO";
        $this->assunto = $this->subject;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.recrutamento.envioFeedbackDocumentos');
    }
}
