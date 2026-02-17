<?php

namespace App\Mail\AdmissoesPrevista;

use App\Models\AdmissoesPrevista;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificacaoAprovacaoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $admissao;
    public $tipo;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(AdmissoesPrevista $admissao, string $tipo)
    {
        $this->admissao = $admissao;
        $this->tipo = $tipo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $cargo = $this->admissao->Cargo ? $this->admissao->Cargo->nome : '';
        $dataAdmissao = $this->admissao->data_admissao;

        $assunto = match ($this->tipo) {
            'criacao' => "Nova Admissão Prevista Criada - {$cargo}",
            'pendente_aprovacao_extra' => "Admissão Prevista Pendente - Aprovação Extra - {$cargo}",
            'pendente_aprovacao_rh' => "Admissão Prevista Pendente - Aprovação RH - {$cargo}",
            'reprovado_gestor' => "Admissão Prevista REPROVADA pelo Gestor - {$cargo}",
            'reprovado_aprovacao_extra' => "Admissão Prevista REPROVADA pela Aprovação Extra - {$cargo}",
            'reprovado_rh' => "Admissão Prevista REPROVADA pelo RH - {$cargo}",
            'cancelado' => "Admissão Prevista CANCELADA - {$cargo}",
            'aprovado_final' => "Admissão Prevista APROVADA - {$cargo} - {$dataAdmissao}",
            // Mantém compatibilidade com tipos antigos
            'aprovacao_extra' => "Admissão Prevista Pendente - Aprovação Extra - {$cargo}",
            'aprovacao_rh' => "Admissão Prevista Pendente - Aprovação RH - {$cargo}",
            'aprovacao' => "Admissão Prevista Aprovada - {$cargo} - {$dataAdmissao}",
            default => "Notificação de Admissão Prevista - {$cargo}",
        };

        return $this->subject($assunto)
            ->view('email.admisoesprevista.notificacao_aprovacao');
    }
}
