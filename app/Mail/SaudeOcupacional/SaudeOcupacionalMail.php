<?php

namespace App\Mail\SaudeOcupacional;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * E-mails da Saúde Ocupacional no layout oficial (layouts.mail.layout):
 * marcos de vencimento de ASO e resumo semanal.
 */
class SaudeOcupacionalMail extends Mailable
{
    use Queueable, SerializesModels;

    public const VIEWS = [
        'marcos' => 'email.saude-ocupacional.alerta-marcos',
        'resumo' => 'email.saude-ocupacional.resumo-semanal',
    ];

    public $subject;

    /**
     * @param  array<string, mixed>  $dados
     */
    public function __construct(public string $tipo, public User $usuario, public array $dados)
    {
        $this->subject = $tipo === 'resumo'
            ? 'Saúde Ocupacional — resumo da semana'
            : 'Saúde Ocupacional — ASOs chegando ao vencimento';

        $this->to($usuario->login, $usuario->nome);
        $this->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
    }

    public function build()
    {
        return $this->view(self::VIEWS[$this->tipo] ?? self::VIEWS['marcos']);
    }
}
