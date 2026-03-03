<?php

namespace App\Mail\AssinaturaDigital;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentoParaAssinaturaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nome;
    public $linkAssinatura;
    public $nomeDocumento;
    public $nomeEmpresa;

    /** @var array dados para o layout do e-mail (ex.: ['empresa_id' => 123]) */
    public $dados;

    public function __construct(string $nome, string $linkAssinatura, string $nomeDocumento, string $nomeEmpresa = '', ?int $empresaId = null)
    {
        $this->nome = $nome;
        $this->linkAssinatura = $linkAssinatura;
        $this->nomeDocumento = $nomeDocumento;
        $this->nomeEmpresa = $nomeEmpresa;
        $this->dados = $empresaId !== null ? ['empresa_id' => $empresaId] : [];
        $this->from(config('mail.from.address', 'naoresponda@mybp.com.br'), config('mail.from.name', 'MyBP'));
        $this->subject = 'Documento para assinatura digital: ' . $nomeDocumento;
    }

    public function build()
    {
        return $this->view('email.assinatura-digital.documento-para-assinatura')
            ->with('dados', $this->dados);
    }
}
