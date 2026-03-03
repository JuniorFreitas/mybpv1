<?php

namespace App\Mail\AssinaturaDigital;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AlertaCotaAssinaturaMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $nomeEmpresa;
    public int $percentual;
    public array $resumo;
    public array $dados;

    public function __construct(string $nomeEmpresa, int $percentual, array $resumo, ?int $empresaId = null)
    {
        $this->nomeEmpresa = $nomeEmpresa;
        $this->percentual = $percentual;
        $this->resumo = $resumo;
        $this->dados = $empresaId !== null ? ['empresa_id' => $empresaId] : [];
        $this->from(config('mail.from.address', 'naoresponda@mybp.com.br'), config('mail.from.name', 'MyBP'));
        $this->subject = "Alerta de cota de assinatura digital ({$percentual}%)";
    }

    public function build()
    {
        return $this->view('email.assinatura-digital.alerta-cota-assinatura')
            ->with('dados', $this->dados);
    }
}

