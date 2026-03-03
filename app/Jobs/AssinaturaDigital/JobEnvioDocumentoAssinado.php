<?php

namespace App\Jobs\AssinaturaDigital;

use App\Mail\AssinaturaDigital\DocumentoAssinadoConcluidoMail;
use App\Models\Cliente;
use App\Models\DocumentoParaAssinatura;
use App\Models\Notificacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class JobEnvioDocumentoAssinado implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected int $documentoParaAssinaturaId;

    public const TIPO_NOTIFICACAO = 'documento_assinatura_concluido';

    public function __construct(int $documentoParaAssinaturaId)
    {
        $this->documentoParaAssinaturaId = $documentoParaAssinaturaId;
        Log::channel('stack')->info('JobEnvioDocumentoAssinado: disparado', ['documento_id' => $documentoParaAssinaturaId]);
    }

    public function handle(): void
    {
        Log::channel('stack')->info('JobEnvioDocumentoAssinado: handle iniciado', ['documento_id' => $this->documentoParaAssinaturaId]);

        $doc = DocumentoParaAssinatura::withoutGlobalScopes()
            ->with(['signatarios', 'arquivo', 'arquivoAssinado', 'solicitante'])
            ->find($this->documentoParaAssinaturaId);

        if (!$doc) {
            Log::channel('stack')->warning('JobEnvioDocumentoAssinado: documento não encontrado', ['documento_id' => $this->documentoParaAssinaturaId]);
            return;
        }
        if ($doc->status !== DocumentoParaAssinatura::STATUS_CONCLUIDO) {
            Log::channel('stack')->info('JobEnvioDocumentoAssinado: documento não está concluído, ignorando', ['documento_id' => $doc->id, 'status' => $doc->status]);
            return;
        }

        $empresa = Cliente::withoutGlobalScopes()->find($doc->empresa_id);
        $nomeDocumento = DocumentoParaAssinatura::labelTipoDocumento($doc->tipo_documento);
        $nomeEmpresa = $empresa ? ($empresa->razao_social ?? $empresa->nome_fantasia ?? '') : '';
        $arquivo = ($doc->arquivoAssinado && $doc->arquivoAssinado->disco)
            ? $doc->arquivoAssinado
            : ($doc->arquivo && $doc->arquivo->disco ? $doc->arquivo : null);

        foreach ($doc->signatarios as $signatario) {
            if (empty($signatario->email)) {
                continue;
            }
            try {
                Mail::to($signatario->email)->send(new DocumentoAssinadoConcluidoMail(
                    $signatario->nome ?: $signatario->email,
                    $nomeDocumento,
                    $nomeEmpresa,
                    $doc->empresa_id,
                    $arquivo
                ));
                Log::channel('stack')->info('JobEnvioDocumentoAssinado: e-mail enviado', ['para' => $signatario->email]);
            } catch (\Throwable $e) {
                Log::channel('stack')->error('JobEnvioDocumentoAssinado: falha ao enviar e-mail', [
                    'para' => $signatario->email,
                    'erro' => $e->getMessage(),
                ]);
            }
        }

        if ($doc->solicitante_id && $doc->solicitante && !empty($doc->solicitante->email)) {
            try {
                Mail::to($doc->solicitante->email)->send(new DocumentoAssinadoConcluidoMail(
                    $doc->solicitante->nome ?? 'Solicitante',
                    $nomeDocumento,
                    $nomeEmpresa,
                    $doc->empresa_id,
                    $arquivo
                ));
            } catch (\Throwable $e) {
                Log::channel('stack')->warning('JobEnvioDocumentoAssinado: falha e-mail solicitante', ['erro' => $e->getMessage()]);
            }

            Notificacao::create([
                'tipo' => self::TIPO_NOTIFICACAO,
                'user_id' => $doc->solicitante_id,
                'payload' => [
                    'documento_id' => $doc->id,
                    'tipo_documento' => $doc->tipo_documento,
                    'nome_documento' => $nomeDocumento,
                    'mensagem' => "O documento \"{$nomeDocumento}\" foi assinado por todos os signatários.",
                ],
                'visto' => false,
            ]);
            Log::channel('stack')->info('JobEnvioDocumentoAssinado: notificação criada', ['solicitante_id' => $doc->solicitante_id]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('stack')->error('JobEnvioDocumentoAssinado: job falhou', [
            'documento_id' => $this->documentoParaAssinaturaId,
            'erro' => $exception->getMessage(),
        ]);
    }
}
