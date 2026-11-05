<?php

namespace App\Jobs\SaudeOcupacional;

use App\Domain\SaudeOcupacional\Documentos\ArquivamentoAutomaticoSst;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Gera o PDF do PCMSO publicado e guarda em Documentos SST (fora da requisição: PDF é pesado).
 */
class JobArquivarPcmsoSst implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;

    public $backoff = 60;

    public function __construct(
        public int $empresaId,
        public int $pcmsoVersaoId,
        public ?int $usuarioId
    ) {
    }

    public function handle(ArquivamentoAutomaticoSst $arquivamento): void
    {
        $documento = $arquivamento->arquivarPcmso($this->empresaId, $this->pcmsoVersaoId, $this->usuarioId);

        Log::info('JobArquivarPcmsoSst: concluído', [
            'empresa_id' => $this->empresaId,
            'pcmso_versao_id' => $this->pcmsoVersaoId,
            'saude_documento_id' => $documento?->id,
        ]);
    }
}
