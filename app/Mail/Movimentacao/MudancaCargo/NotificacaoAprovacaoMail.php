<?php

namespace App\Mail\Movimentacao\MudancaCargo;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificacaoAprovacaoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $dados;

    /**
     * Create a new message instance.
     *
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
        $assunto = $this->gerarAssunto();

        return $this->subject($assunto)
            ->view('emails.movimentacao.mudanca_cargo.notificacao-aprovacao')
            ->with('dados', $this->dados);
    }

    private function gerarAssunto(): string
    {
        $tipo = $this->dados['tipo'];
        $nomeAprovacaoExtra = $this->dados['nome_aprovacao_extra'] ?? 'Aprovação Extra';

        $assuntos = [
            'criacao' => "Notificação — Mudança de cargo — sua aprovação como gestor",
            'pendente_aprovacao_extra' => "Notificação — Mudança de cargo — aguardando aprovação de {$nomeAprovacaoExtra}",
            'pendente_aprovacao_rh' => "Notificação — Mudança de cargo — aguardando aprovação do RH",
            'reprovado_gestor' => "Notificação — Mudança de cargo — reprovada pelo gestor",
            'reprovado_aprovacao_extra' => "Notificação — Mudança de cargo — reprovada por {$nomeAprovacaoExtra}",
            'reprovado_rh' => "Notificação — Mudança de cargo — reprovada pelo RH",
            'cancelado' => "Notificação — Mudança de cargo — cancelada",
            'aprovado_final' => "Notificação — Mudança de cargo — aprovada em todas as etapas",
        ];

        return $assuntos[$tipo] ?? "Notificação — Mudança de cargo";
    }
}
