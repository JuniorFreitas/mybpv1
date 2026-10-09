<?php

namespace App\Mail\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CodigoMfaLoginMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nome;
    public $codigo;
    public $minutosExpiracao;
    public $nomeEmpresa;
    public $dados;

    public function __construct(
        string $nome,
        string $codigo,
        int $minutosExpiracao = 10,
        string $nomeEmpresa = '',
        ?int $empresaId = null
    ) {
        $this->nome = $nome;
        $this->codigo = $codigo;
        $this->minutosExpiracao = $minutosExpiracao;
        $this->nomeEmpresa = $nomeEmpresa;
        $this->dados = $empresaId !== null ? ['empresa_id' => $empresaId] : [];
        $this->from(config('mail.from.address', 'naoresponda@mybp.com.br'), config('mail.from.name', 'MyBP'));
        $this->subject = 'Código de verificação de login — MyBP';
    }

    public function build()
    {
        return $this->view('email.auth.codigo-mfa-login')
            ->with('dados', $this->dados);
    }
}
