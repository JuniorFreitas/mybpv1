<?php

namespace App\Console\Commands;

use App\Jobs\AssinaturaDigital\JobEnvioEmailAssinatura;
use App\Models\DocumentoParaAssinatura;
use Illuminate\Console\Command;

class AssinaturaEnviarEmailCommand extends Command
{
    protected $signature = 'assinatura:enviar-email {documento_id : ID do DocumentoParaAssinatura} {--sync : Roda o job na hora (sem fila)}';

    protected $description = 'Dispara (ou executa em modo sync) o envio de e-mail de assinatura digital para um documento. Use para debugar.';

    public function handle(): int
    {
        $documentoId = (int) $this->argument('documento_id');
        $sync = $this->option('sync');

        $doc = DocumentoParaAssinatura::withoutGlobalScopes()->with('signatarios')->find($documentoId);
        if (!$doc) {
            $this->error("Documento {$documentoId} não encontrado.");
            return 1;
        }

        $this->info("Documento #{$doc->id} - tipo: {$doc->tipo_documento} - signatários: " . $doc->signatarios->count());

        if ($sync) {
            $this->info('Executando job em modo síncrono (sem fila)...');
            try {
                JobEnvioEmailAssinatura::dispatchSync($documentoId);
                $this->info('Job concluído. Verifique os logs em storage/logs/laravel.log');
                return 0;
            } catch (\Throwable $e) {
                $this->error('Erro: ' . $e->getMessage());
                $this->line($e->getTraceAsString());
                return 1;
            }
        }

        JobEnvioEmailAssinatura::dispatch($documentoId);
        $this->info('Job enfileirado. Se usar fila (redis/database), execute: php artisan queue:work');
        return 0;
    }
}
