<?php

namespace App\Console\Commands;

use App\Services\Cbo\CboImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportarCboCommand extends Command
{
    protected $signature = 'cbo:importar {--force : Força nova baixa dos CSVs (ignora cache de 24h)}';

    protected $description = 'Importa a base oficial da CBO a partir dos arquivos oficiais do governo';

    public function handle(CboImportService $importService): int
    {
        $force = (bool) $this->option('force');

        Log::info('CBO: início da importação', ['force' => $force]);
        $this->info('Iniciando importação da CBO...');

        try {
            $result = $importService->run($force);
        } catch (\Throwable $e) {
            Log::error('CBO: importação falhou', [
                'mensagem' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        Log::info('CBO: importação concluída', [
            'familias_processadas' => $result->familiasProcessadas,
            'familias_ignoradas' => $result->familiasIgnoradas,
            'ocupacoes_processadas' => $result->ocupacoesProcessadas,
            'ocupacoes_ignoradas' => $result->ocupacoesIgnoradas,
            'perfis_atualizados' => $result->perfisAtualizados,
            'perfis_ignorados' => $result->perfisIgnorados,
        ]);

        $this->table(
            ['Etapa', 'Processados', 'Ignorados'],
            [
                ['Famílias ocupacionais', (string) $result->familiasProcessadas, (string) $result->familiasIgnoradas],
                ['Ocupações', (string) $result->ocupacoesProcessadas, (string) $result->ocupacoesIgnoradas],
                ['Descrição sumária (perfil)', (string) $result->perfisAtualizados, (string) $result->perfisIgnorados],
            ]
        );
        $this->info('Importação da CBO finalizada com sucesso.');

        return self::SUCCESS;
    }
}
