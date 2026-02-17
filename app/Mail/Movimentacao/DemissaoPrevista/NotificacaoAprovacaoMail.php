<?php

namespace App\Mail\Movimentacao\DemissaoPrevista;

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
    public $dados;

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
            ->view('emails.movimentacao.demissao_prevista.notificacao-aprovacao')
            ->with('dados', $this->dados);
    }

    private function gerarAssunto(): string
    {
        $tipo = $this->dados['tipo'];
        $colaborador = $this->dados['colaborador'] ?? '';
        $nomeAprovacaoExtra = $this->dados['nome_aprovacao_extra'] ?? 'Aprovação Extra';

        $assuntos = [
            'criacao' => "Notificação — Demissão ({$colaborador}) — sua aprovação como gestor",
            'pendente_aprovacao_extra' => "Notificação — Demissão ({$colaborador}) — aguardando aprovação de {$nomeAprovacaoExtra}",
            'pendente_aprovacao_rh' => "Notificação — Demissão ({$colaborador}) — aguardando aprovação do RH",
            'reprovado_gestor' => "Notificação — Demissão ({$colaborador}) — reprovada pelo gestor",
            'reprovado_aprovacao_extra' => "Notificação — Demissão ({$colaborador}) — reprovada por {$nomeAprovacaoExtra}",
            'reprovado_rh' => "Notificação — Demissão ({$colaborador}) — reprovada pelo RH",
            'cancelado' => "Notificação — Demissão ({$colaborador}) — cancelada",
            'aprovado_final' => "Notificação — Demissão ({$colaborador}) — aprovada em todas as etapas",
        ];

        return $assuntos[$tipo] ?? "Notificação — Demissão {$colaborador}";
    }
}
