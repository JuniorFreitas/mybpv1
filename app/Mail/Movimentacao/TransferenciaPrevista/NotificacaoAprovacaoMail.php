<?php

namespace App\Mail\Movimentacao\TransferenciaPrevista;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificacaoAprovacaoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $dados;

    public function __construct(array $dados)
    {
        $this->dados = $dados;
    }

    public function build()
    {
        $assunto = $this->gerarAssunto();

        return $this->subject($assunto)
            ->view('emails.movimentacao.transferencia_prevista.notificacao-aprovacao')
            ->with('dados', $this->dados);
    }

    private function gerarAssunto(): string
    {
        $tipo = $this->dados['tipo'];

        $nomeAprovacaoExtra = $this->dados['nome_aprovacao_extra'] ?? 'Aprovação Extra';
        $assuntos = [
            'criacao' => "Notificação — Transferência — sua aprovação como gestor",
            'criacao_gestor_unico' => "Notificação — Transferência — sua aprovação como gestor aprovação",
            'reprovado_gestor_unico' => "Notificação — Transferência — reprovada pelo gestor aprovação",
            'pendente_aprovacao_extra' => "Notificação — Transferência — aguardando aprovação de {$nomeAprovacaoExtra}",
            'pendente_aprovacao_rh' => "Notificação — Transferência — aguardando aprovação do RH",
            'reprovado_gestor' => "Notificação — Transferência — reprovada pelo gestor",
            'reprovado_aprovacao_extra' => "Notificação — Transferência — reprovada por {$nomeAprovacaoExtra}",
            'reprovado_rh' => "Notificação — Transferência — reprovada pelo RH",
            'cancelado' => "Notificação — Transferência — cancelada",
            'aprovado_final' => "Notificação — Transferência — aprovada em todas as etapas",
        ];

        return $assuntos[$tipo] ?? "Notificação — Transferência";
    }
}
