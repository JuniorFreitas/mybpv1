<?php

namespace App\Mail\Movimentacao\MudaIntermitenteFixoPrevista;

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
            ->view('emails.notificacao_aprovacao')
            ->with('dados', $this->dados);
    }

    private function gerarAssunto(): string
    {
        $tipo = $this->dados['tipo'];

        $nomeAprovacaoExtra = $this->dados['nome_aprovacao_extra'] ?? 'Aprovação Extra';
        $assuntos = [
            'criacao' => "Notificação Intermitente/Fixo — sua aprovação como gestor",
            'pendente_aprovacao_extra' => "Notificação Intermitente/Fixo — aguardando aprovação de {$nomeAprovacaoExtra}",
            'pendente_aprovacao_rh' => "Notificação Intermitente/Fixo — aguardando aprovação do RH",
            'reprovado_gestor' => "Notificação Intermitente/Fixo — reprovada pelo gestor",
            'reprovado_aprovacao_extra' => "Notificação Intermitente/Fixo — reprovada por {$nomeAprovacaoExtra}",
            'reprovado_rh' => "Notificação Intermitente/Fixo — reprovada pelo RH",
            'cancelado' => "Notificação Intermitente/Fixo — cancelada",
            'aprovado_final' => "Notificação Intermitente/Fixo — aprovada em todas as etapas",
        ];

        return $assuntos[$tipo] ?? "Notificação Intermitente/Fixo";
    }
}
