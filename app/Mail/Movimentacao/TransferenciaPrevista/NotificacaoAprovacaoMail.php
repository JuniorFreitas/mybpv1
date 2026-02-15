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
        $colaborador = $this->dados['colaborador'] ?? '';

        $nomeAprovacaoExtra = $this->dados['nome_aprovacao_extra'] ?? 'Aprovação Extra';
        $assuntos = [
            'criacao' => "Notificação — Transferência ({$colaborador}) — sua aprovação como gestor",
            'pendente_aprovacao_extra' => "Notificação — Transferência ({$colaborador}) — aguardando aprovação de {$nomeAprovacaoExtra}",
            'pendente_aprovacao_rh' => "Notificação — Transferência ({$colaborador}) — aguardando aprovação do RH",
            'reprovado_gestor' => "Notificação — Transferência ({$colaborador}) — reprovada pelo gestor",
            'reprovado_aprovacao_extra' => "Notificação — Transferência ({$colaborador}) — reprovada por {$nomeAprovacaoExtra}",
            'reprovado_rh' => "Notificação — Transferência ({$colaborador}) — reprovada pelo RH",
            'cancelado' => "Notificação — Transferência ({$colaborador}) — cancelada",
            'aprovado_final' => "Notificação — Transferência ({$colaborador}) — aprovada em todas as etapas",
        ];

        return $assuntos[$tipo] ?? "Notificação — Transferência {$colaborador}";
    }
}
