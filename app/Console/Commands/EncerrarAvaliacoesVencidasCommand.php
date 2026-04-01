<?php

namespace App\Console\Commands;

use App\Models\Avaliacao;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class EncerrarAvaliacoesVencidasCommand extends Command
{
    protected $signature = 'mybp:encerrar-avaliacoes-vencidas';

    protected $description = 'Encerra avaliações abertas cujo vencimento já passou';

    public function handle(): int
    {
        $empresaIds = collect(DB::select(
            'select distinct empresa_id from avaliacoes where ativo = 1 and status = ? and data_fim_prazo is not null and date(data_fim_prazo) < curdate()',
            [Avaliacao::STATUS_ABERTA]
        ))->pluck('empresa_id')->filter()->values();

        if ($empresaIds->isEmpty()) {
            $this->info('Nenhuma avaliação vencida encontrada para encerramento.');
            return self::SUCCESS;
        }

        $totalAtualizado = 0;

        foreach ($empresaIds as $empresaId) {
            $atualizados = DB::update(
                'update avaliacoes set status = ? where empresa_id = ? and ativo = 1 and status = ? and data_fim_prazo is not null and date(data_fim_prazo) < curdate()',
                [Avaliacao::STATUS_ENCERRADA, $empresaId, Avaliacao::STATUS_ABERTA]
            );

            if ($atualizados > 0) {
                Cache::forget("lista_av_grp_ano_{$empresaId}");
                $this->info("Empresa {$empresaId}: {$atualizados} avaliação(ões) encerrada(s).");
                $totalAtualizado += $atualizados;
            }
        }

        $this->info("Total de avaliações encerradas: {$totalAtualizado}.");

        return self::SUCCESS;
    }
}
