<?php

namespace App\Mail\Movimentacao\AdmissaoPrevista;

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
            ->view('emails.movimentacao.admissao_prevista.notificacao-aprovacao')
            ->with('dados', $this->dados);
    }

    private function gerarAssunto(): string
    {
        $tipo = $this->dados['tipo'];
        $cargo = $this->dados['cargo'] ?? '';
        $nomeAprovacaoExtra = $this->dados['nome_aprovacao_extra'] ?? 'Aprovação Extra';

        $assuntos = [
            'criacao' => "Notificação — Admissão ({$cargo}) — sua aprovação como gestor",
            'pendente_aprovacao_extra' => "Notificação — Admissão ({$cargo}) — aguardando aprovação de {$nomeAprovacaoExtra}",
            'pendente_aprovacao_rh' => "Notificação — Admissão ({$cargo}) — aguardando aprovação do RH",
            'reprovado_gestor' => "Notificação — Admissão ({$cargo}) — reprovada pelo gestor",
            'reprovado_aprovacao_extra' => "Notificação — Admissão ({$cargo}) — reprovada por {$nomeAprovacaoExtra}",
            'reprovado_rh' => "Notificação — Admissão ({$cargo}) — reprovada pelo RH",
            'cancelado' => "Notificação — Admissão ({$cargo}) — cancelada",
            'aprovado_final' => "Notificação — Admissão ({$cargo}) — aprovada em todas as etapas",
        ];

        return $assuntos[$tipo] ?? "Notificação — Admissão {$cargo}";
    }
}
