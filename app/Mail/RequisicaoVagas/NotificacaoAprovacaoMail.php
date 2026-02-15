<?php

namespace App\Mail\RequisicaoVagas;

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
            ->view('emails.requisicao_vaga.notificacao-aprovacao')
            ->with('dados', $this->dados);
    }

    private function gerarAssunto(): string
    {
        $tipo = $this->dados['tipo'];
        $cargo = $this->dados['cargo'] ?? '';
        $nomeAprovacaoExtra = $this->dados['nome_aprovacao_extra'] ?? 'Aprovação Extra';

        $assuntos = [
            'criacao' => "Notificação de Requisição de Vaga ({$cargo}) — sua aprovação como gestor",
            'pendente_aprovacao_extra' => "Notificação de Requisição de Vaga ({$cargo}) — aguardando aprovação de {$nomeAprovacaoExtra}",
            'pendente_aprovacao_rh' => "Notificação de Requisição de Vaga ({$cargo}) — aguardando aprovação do RH",
            'reprovado_gestor' => "Notificação de Requisição de Vaga ({$cargo}) — reprovada pelo gestor",
            'reprovado_aprovacao_extra' => "Notificação de Requisição de Vaga ({$cargo}) — reprovada por {$nomeAprovacaoExtra}",
            'cancelado' => "Notificação de Requisição de Vaga ({$cargo}) — cancelada",
            'aprovado_final' => "Notificação de Requisição de Vaga ({$cargo}) — aprovada em todas as etapas",
        ];

        return $assuntos[$tipo] ?? "Notificação de Requisição de Vaga - {$cargo}";
    }
}
