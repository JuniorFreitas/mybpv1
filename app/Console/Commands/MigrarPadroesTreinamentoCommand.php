<?php

namespace App\Console\Commands;

use App\Services\Treinamento\MigrarSegmentosParaPadroesTreinamento;
use Illuminate\Console\Command;

/**
 * Reexecuta a migração Segmento → Padrão (idempotente).
 *
 * Uso:
 *   php artisan mybp:migrar-padroes-treinamento
 *   docker compose exec mybpdp php artisan mybp:migrar-padroes-treinamento
 */
class MigrarPadroesTreinamentoCommand extends Command
{
    protected $signature = 'mybp:migrar-padroes-treinamento';

    protected $description = 'Migra segmentos/admissões/assinaturas/vencimentos para padroes_treinamento (idempotente)';

    public function handle(MigrarSegmentosParaPadroesTreinamento $migrador): int
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('segmentos_treinamento')) {
            $this->warn('Tabela segmentos_treinamento já foi removida. Nada a migrar.');

            return self::SUCCESS;
        }

        $this->info('Migrando segmentos → padrões de treinamento...');
        $stats = $migrador->executar();

        foreach ($stats as $chave => $valor) {
            $this->line(sprintf('  %-14s %d', $chave . ':', $valor));
        }

        $this->info('Concluído.');
        $this->line('No deploy, o drop do legado roda sozinho em `php artisan migrate` (migration 2026_10_18_150000).');

        return self::SUCCESS;
    }
}
