<?php

namespace App\Console\Commands;

use App\Domain\SaudeOcupacional\Alertas\AlertasPeriodicosService;
use App\Models\ClienteConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Alertas diários de exames periódicos (marcos de vencimento) e resumo semanal (segunda-feira),
 * para empresas com o módulo Saúde Ocupacional ligado. E-mails vão para a fila.
 */
class SaudeAlertasCommand extends Command
{
    protected $signature = 'mybp:saude-alertas {--empresa= : Processa só esta empresa}';

    protected $description = 'Saúde Ocupacional: alertas de vencimento de ASO por marcos e resumo semanal';

    public function handle(AlertasPeriodicosService $alertas): int
    {
        if (!Schema::hasColumn('cliente_configs', 'modulo_saude_ocupacional')) {
            $this->warn('Módulo Saúde Ocupacional não instalado (rode as migrations).');

            return self::SUCCESS;
        }

        $empresas = ClienteConfig::withoutGlobalScopes()
            ->where('modulo_saude_ocupacional', true)
            ->when($this->option('empresa'), fn ($q, $id) => $q->where('cliente_id', (int) $id))
            ->pluck('cliente_id');

        foreach ($empresas as $empresaId) {
            try {
                $r = $alertas->processar((int) $empresaId);
                $this->line(sprintf('Empresa %d: %d marco(s), %d destinatário(s)%s.', $empresaId, $r['marcos'], $r['destinatarios'], $r['resumo'] ? ', resumo semanal' : ''));
            } catch (\Throwable $e) {
                Log::error('Alertas Saúde Ocupacional: falha na empresa', ['empresa_id' => (int) $empresaId, 'erro' => $e->getMessage()]);
                $this->error("Empresa {$empresaId}: falha ({$e->getMessage()}).");
            }
        }

        return self::SUCCESS;
    }
}
