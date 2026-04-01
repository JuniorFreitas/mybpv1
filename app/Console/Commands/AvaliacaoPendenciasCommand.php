<?php

namespace App\Console\Commands;

use App\Models\Avaliacao;
use App\Services\Avaliacoes\AvaliacaoNotificacaoService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AvaliacaoPendenciasCommand extends Command
{
    protected $signature = 'mybp:avaliacao-pendencias {--data-base=}';

    protected $description = 'Envia notificações de pendências e prazos das avaliações';

    public function handle(AvaliacaoNotificacaoService $service): int
    {
        $dataBase = $this->resolverDataBase();

        $empresaIds = Avaliacao::withoutGlobalScopes()
            ->where('status', Avaliacao::STATUS_ABERTA)
            ->whereAtivo(true)
            ->distinct()
            ->pluck('empresa_id')
            ->filter()
            ->values();

        $totais = [3 => 0, 2 => 0, 1 => 0, 0 => 0];

        foreach ($empresaIds as $empresaId) {
            $totais[3] += $service->notificarPendentesPorPrazo(3, (int)$empresaId, $dataBase);
            $totais[2] += $service->notificarPendentesPorPrazo(2, (int)$empresaId, $dataBase);
            $totais[1] += $service->notificarPendentesPorPrazo(1, (int)$empresaId, $dataBase);
            $totais[0] += $service->notificarPendentesPorPrazo(0, (int)$empresaId, $dataBase);
        }

        foreach ($totais as $dias => $quantidade) {
            $rotulo = $dias === 0 ? 'no dia do vencimento' : "D-{$dias}";
            $this->info("Notificações {$rotulo}: {$quantidade}");
        }

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
