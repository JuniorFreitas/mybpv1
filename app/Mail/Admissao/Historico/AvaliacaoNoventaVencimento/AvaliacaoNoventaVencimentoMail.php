<?php

namespace App\Mail\Admissao\Historico\AvaliacaoNoventaVencimento;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AvaliacaoNoventaVencimentoMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $dados = [];
    public $subject;

    public function __construct($dados)
    {

        $this->dados = $dados;
        $this->subject = 'Lembrete de vencimento avaliação 90 dias';

        $this->to($this->dados['usuario']->login, $this->dados['usuario']->nome);
        $this->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.admissao.historico.avaliacaoNoventaVencimento');
    }
}
