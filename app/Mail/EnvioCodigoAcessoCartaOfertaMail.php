<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnvioCodigoAcessoCartaOfertaMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $dados = [];

    public function __construct(array $dados)
    {
        $this->dados = $dados;
        $empresa = \App\Models\Cliente::withoutGlobalScopes()->find($this->dados['empresa_id']);
        $this->to($this->dados['email'], $this->dados['nome']);
        $this->from('naoresponda@mybp.com.br', $empresa->nome_fantasia ?? 'MyBPIN');
        $this->subject = 'Código de acesso — Carta Oferta';
        $this->assunto = $this->subject;
    }

    public function build()
    {
        return $this->view('email.envioCodigoAcessoCartaOferta');
    }
}
