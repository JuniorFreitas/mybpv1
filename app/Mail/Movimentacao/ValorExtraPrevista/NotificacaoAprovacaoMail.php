<?php

namespace App\Mail\Movimentacao\ValorExtraPrevista;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificacaoAprovacaoMail extends Mailable
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
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $assunto = $this->gerarAssunto();

        return $this->subject($assunto)
            ->view('emails.movimentacao.valor_extra_prevista.notificacao-aprovacao')
            ->with('dados', $this->dados);
    }

    private function gerarAssunto(): string
    {
        $tipo = $this->dados['tipo'];
        $nomeAprovacaoExtra = $this->dados['nome_aprovacao_extra'] ?? 'Aprovação Extra';

        $assuntos = [
            'criacao' => "Notificação — Valor extra — sua aprovação como gestor",
            'pendente_aprovacao_extra' => "Notificação — Valor extra — aguardando aprovação de {$nomeAprovacaoExtra}",
            'pendente_aprovacao_rh' => "Notificação — Valor extra — aguardando aprovação do RH",
            'reprovado_gestor' => "Notificação — Valor extra — reprovado pelo gestor",
            'reprovado_aprovacao_extra' => "Notificação — Valor extra — reprovado por {$nomeAprovacaoExtra}",
            'reprovado_rh' => "Notificação — Valor extra — reprovado pelo RH",
            'cancelado' => "Notificação — Valor extra — cancelado",
            'aprovado_final' => "Notificação — Valor extra — aprovado em todas as etapas",
        ];

        return $assuntos[$tipo] ?? "Notificação — Valor extra";
    }
}
