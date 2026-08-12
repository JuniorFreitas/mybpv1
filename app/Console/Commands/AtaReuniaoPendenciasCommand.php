<?php

namespace App\Console\Commands;

use App\Services\AtaReuniao\AtaReuniaoPendenciaNotificacaoService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AtaReuniaoPendenciasCommand extends Command
{
    protected $signature = 'mybp:ata-pendencias {--data-base=}';

    protected $description = 'Processa pendencias de atas, notificacoes D-2 e atrasos';

    public function handle(AtaReuniaoPendenciaNotificacaoService $service): int
    {
        $dataBase = $this->resolverDataBase();

        $empresaIds = DB::table('ata_reuniao_acaos as a')
            ->join('ata_reuniaos as ata', 'ata.id', '=', 'a.ata_reuniao_id')
            ->whereNull('ata.deleted_at')
            ->whereNull('a.deleted_at')
            ->whereNotNull('ata.empresa_id')
            ->distinct()
            ->pluck('ata.empresa_id')
            ->filter()
            ->values();

        $totalAntecedencia = 0;
        $totalVencimento = 0;
        $totalEscalonamento = 0;
        $totalAtrasadas = 0;

        foreach ($empresaIds as $empresaId) {
            $totalAtrasadas += $service->marcarAtrasadas((int) $empresaId, $dataBase);
            $totais = $service->notificarConfiguradas((int) $empresaId, $dataBase);
            $totalAntecedencia += $totais['antecedencia'];
            $totalVencimento += $totais['vencimento'];
            $totalEscalonamento += $totais['atrasos'];
        }

        $this->info("Pendencias atrasadas atualizadas: {$totalAtrasadas}");
        $this->info("Notificacoes de antecedencia criadas: {$totalAntecedencia}");
        $this->info("Notificacoes de vencimento criadas: {$totalVencimento}");
        $this->info("Notificacoes de atraso criadas: {$totalEscalonamento}");

        return self::SUCCESS;
    }

    private function resolverDataBase(): Carbon
    {
        $dataBase = $this->option('data-base');

        if (!$dataBase) {
            return now()->startOfDay();
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dataBase)) {
            return Carbon::createFromFormat('d/m/Y', $dataBase)->startOfDay();
        }

        return Carbon::parse($dataBase)->startOfDay();
    }
}
