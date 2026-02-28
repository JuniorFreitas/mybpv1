<?php

namespace App\Jobs\AssinaturaDigital;

use App\Mail\AssinaturaDigital\DocumentoParaAssinaturaMail;
use App\Models\Cliente;
use App\Models\DocumentoAssinaturaSignatario;
use App\Models\DocumentoParaAssinatura;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class JobEnvioEmailAssinatura implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected int $documentoParaAssinaturaId;

    public function __construct(int $documentoParaAssinaturaId)
    {
        $this->documentoParaAssinaturaId = $documentoParaAssinaturaId;
        Log::info('JobEnvioEmailAssinatura: disparado', ['documento_id' => $documentoParaAssinaturaId]);
    }

    public function handle(): void
    {
        Log::info('JobEnvioEmailAssinatura: handle iniciado', ['documento_id' => $this->documentoParaAssinaturaId]);

        $doc = DocumentoParaAssinatura::withoutGlobalScopes()
            ->with('signatarios')
            ->find($this->documentoParaAssinaturaId);

        if (!$doc) {
            Log::warning('JobEnvioEmailAssinatura: documento não encontrado', ['documento_id' => $this->documentoParaAssinaturaId]);
            return;
        }
        if ($doc->status === DocumentoParaAssinatura::STATUS_CANCELADO) {
            Log::info('JobEnvioEmailAssinatura: documento cancelado, ignorando envio', ['documento_id' => $doc->id]);
            return;
        }

        $empresa = Cliente::withoutGlobalScopes()->find($doc->empresa_id);
        $apelido = $empresa && $empresa->apelido ? $empresa->apelido : 'empresa';
        $baseUrl = rtrim(config('app.url'), '/') . '/' . $apelido . '/assinatura/';
        $nomeDocumento = DocumentoParaAssinatura::labelTipoDocumento($doc->tipo_documento);
        $nomeEmpresa = $empresa ? ($empresa->razao_social ?? $empresa->nome_fantasia ?? '') : '';

        $totalSignatarios = $doc->signatarios->count();
        $pendentes = $doc->signatarios->filter(fn ($s) => $s->status === DocumentoAssinaturaSignatario::STATUS_PENDENTE && !empty($s->email));
        Log::info('JobEnvioEmailAssinatura: documento carregado', [
            'documento_id' => $doc->id,
            'tipo' => $doc->tipo_documento,
            'apelido' => $apelido,
            'total_signatarios' => $totalSignatarios,
            'pendentes_com_email' => $pendentes->count(),
        ]);

        foreach ($doc->signatarios as $signatario) {
            if ($signatario->status !== DocumentoAssinaturaSignatario::STATUS_PENDENTE || !$signatario->email) {
                Log::debug('JobEnvioEmailAssinatura: signatário ignorado', [
                    'signatario_id' => $signatario->id,
                    'email' => $signatario->email,
                    'status' => $signatario->status,
                ]);
                continue;
            }
            $linkAssinatura = $baseUrl . $signatario->token;
            try {
                Log::info('JobEnvioEmailAssinatura: enviando e-mail', [
                    'para' => $signatario->email,
                    'nome' => $signatario->nome,
                ]);
                Mail::to($signatario->email)->send(new DocumentoParaAssinaturaMail(
                    $signatario->nome,
                    $linkAssinatura,
                    $nomeDocumento,
                    $nomeEmpresa,
                    $doc->empresa_id
                ));
                Log::info('JobEnvioEmailAssinatura: e-mail enviado com sucesso', ['para' => $signatario->email]);
            } catch (\Throwable $e) {
                Log::error('JobEnvioEmailAssinatura: falha ao enviar e-mail', [
                    'para' => $signatario->email,
                    'erro' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            }
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('JobEnvioEmailAssinatura: job falhou', [
            'documento_id' => $this->documentoParaAssinaturaId,
            'erro' => $exception->getMessage(),
        ]);
    }
}
