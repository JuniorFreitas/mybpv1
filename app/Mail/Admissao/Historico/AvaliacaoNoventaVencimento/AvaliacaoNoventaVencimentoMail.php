<?php

namespace App\Mail\Admissao\Historico\AvaliacaoNoventaVencimento;

use Illuminate\Mail\Mailable;

class AvaliacaoNoventaVencimentoMail extends Mailable
{
    /**
     * Dados para o template do e-mail
     *
     * @var array
     */
    public $dados = [];

    /**
     * Create a new message instance.
     *
     * @param array $dados
     * @return void
     */
    public function __construct(array $dados)
    {
        $this->dados = $dados;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Lembrete de vencimento - Avaliação de Experiência')
            ->to($this->dados['usuario']->login, $this->dados['usuario']->nome)
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->view('email.admissao.historico.avaliacaoNoventaVencimento')
            ->with([
                'dados' => $this->dados,
                'subject' => 'Lembrete de vencimento - Avaliação de Experiência'
            ]);
    }
}
