<?php

namespace App\Mail\AssinaturaDigital;

use App\Models\Arquivo;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DocumentoAssinadoConcluidoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $nome;
    public $nomeDocumento;
    public $nomeEmpresa;
    public $dados;

    /** @var Arquivo|null documento PDF para anexar */
    protected $arquivo;

    public function __construct(
        string $nome,
        string $nomeDocumento,
        string $nomeEmpresa = '',
        ?int $empresaId = null,
        ?Arquivo $arquivo = null
    ) {
        $this->nome = $nome;
        $this->nomeDocumento = $nomeDocumento;
        $this->nomeEmpresa = $nomeEmpresa;
        $this->dados = $empresaId !== null ? ['empresa_id' => $empresaId] : [];
        $this->arquivo = $arquivo;
        $this->from(config('mail.from.address', 'naoresponda@mybp.com.br'), config('mail.from.name', 'MyBP'));
        $this->subject = 'Documento assinado: ' . $nomeDocumento;
    }

    public function build()
    {
        $mail = $this->view('email.assinatura-digital.documento-assinado-concluido')
            ->with('dados', $this->dados);

        if ($this->arquivo && $this->arquivo->disco && $this->arquivo->file) {
            try {
                $conteudo = Storage::disk($this->arquivo->disco)->get($this->arquivo->file);
                if ($conteudo !== null) {
                    $nomeAnexo = $this->arquivo->nome ?: 'documento-assinado.pdf';
                    if (strtolower(pathinfo($nomeAnexo, PATHINFO_EXTENSION)) !== 'pdf') {
                        $nomeAnexo .= '.pdf';
                    }
                    $mail->attachData($conteudo, $nomeAnexo, ['mime' => 'application/pdf']);
                }
            } catch (\Throwable $e) {
                \Log::warning('DocumentoAssinadoConcluidoMail: falha ao anexar PDF', [
                    'arquivo_id' => $this->arquivo->id,
                    'erro' => $e->getMessage(),
                ]);
            }
        }

        return $mail;
    }
}
