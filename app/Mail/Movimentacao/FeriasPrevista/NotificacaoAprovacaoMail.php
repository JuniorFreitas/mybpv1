<?php

namespace App\Mail\Movimentacao\FeriasPrevista;

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
            ->view('emails.movimentacao.ferias_prevista.notificacao-aprovacao')
            ->with('dados', $this->dados);
    }

    private function gerarAssunto(): string
    {
        $tipo = $this->dados['tipo'];
        $colaborador = $this->dados['colaborador'] ?? '';
        $nomeAprovacaoExtra = $this->dados['nome_aprovacao_extra'] ?? 'Aprovação Extra';

        $assuntos = [
            'criacao' => "Notificação — Férias ({$colaborador}) — sua aprovação como gestor",
            'pendente_aprovacao_extra' => "Notificação — Férias ({$colaborador}) — aguardando aprovação de {$nomeAprovacaoExtra}",
            'pendente_aprovacao_rh' => "Notificação — Férias ({$colaborador}) — aguardando aprovação do RH",
            'reprovado_gestor' => "Notificação — Férias ({$colaborador}) — reprovadas pelo gestor",
            'reprovado_aprovacao_extra' => "Notificação — Férias ({$colaborador}) — reprovadas por {$nomeAprovacaoExtra}",
            'reprovado_rh' => "Notificação — Férias ({$colaborador}) — reprovadas pelo RH",
            'cancelado' => "Notificação — Férias ({$colaborador}) — canceladas",
            'aprovado_final' => "Notificação — Férias ({$colaborador}) — aprovadas em todas as etapas",
        ];

        return $assuntos[$tipo] ?? "Notificação — Férias {$colaborador}";
    }
}
