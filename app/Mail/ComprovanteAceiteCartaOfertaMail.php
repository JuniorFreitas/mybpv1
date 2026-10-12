<?php

namespace App\Mail;

use App\Models\Arquivo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ComprovanteAceiteCartaOfertaMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $dados = [];

    public function __construct(array $dados)
    {
        $this->dados = $dados;
        $empresa = \App\Models\Cliente::withoutGlobalScopes()->find($this->dados['empresa_id']);
        $this->to($this->dados['email'], $this->dados['nome']);
        $this->from('naoresponda@mybp.com.br', $empresa->nome_fantasia ?? 'MyBPIN');
        $this->subject = 'Carta Oferta — comprovante de ' . ($this->dados['aceito'] ? 'aceite' : 'recusa');
        $this->assunto = $this->subject;
    }

    public function build()
    {
        $mail = $this->view('email.comprovanteAceiteCartaOferta');

        $arquivo = !empty($this->dados['comprovante_arquivo_id'])
            ? Arquivo::find($this->dados['comprovante_arquivo_id'])
            : null;
        if ($arquivo && Storage::disk($arquivo->disco)->exists($arquivo->file)) {
            $mail->attachData(
                Storage::disk($arquivo->disco)->get($arquivo->file),
                'carta-oferta-comprovante.pdf',
                ['mime' => 'application/pdf']
            );
        }

        return $mail;
    }
}
