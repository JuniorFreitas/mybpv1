<?php

namespace App\Jobs;

use App\Models\Exportacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use MasterTag\DataHora;

class JobDeletaExportacaoExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    // Usado pelo schedule:call e pelo comando artisan
    public function __invoke()
    {
        $this->handle();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $limite = new DataHora();
            $limite->subtrairDia(1);
            $createdAte = $limite->dataInsert() . ' 23:59:59';

            $query = Exportacao::query()
                ->where('removido', false)
                ->where('created_at', '<=', $createdAte);

            $total = (clone $query)->count();
            if ($total === 0) {
                Log::info('JobDeletaExportacaoExcel: nenhuma exportação para remover');

                return;
            }

            // 1) Some da lista imediatamente (Meus Downloads filtra removido=false)
            $atualizados = (clone $query)->update([
                'removido' => true,
                'updated_at' => now(),
            ]);

            // 2) Apaga arquivos em disco/S3 (já marcados). Sem exists() — no S3 é caro.
            $discoStorage = Storage::disk('disco-exportacao');
            Exportacao::query()
                ->where('removido', true)
                ->where('created_at', '<=', $createdAte)
                ->whereNotNull('arquivo')
                ->orderBy('id')
                ->chunkById(200, function ($exportacoes) use ($discoStorage) {
                    foreach ($exportacoes as $exportacao) {
                        if (!$exportacao->arquivo) {
                            continue;
                        }
                        try {
                            $discoStorage->delete($exportacao->arquivo);
                        } catch (\Throwable $e) {
                            Log::warning('JobDeletaExportacaoExcel: falha ao apagar arquivo', [
                                'exportacao_id' => $exportacao->id,
                                'arquivo' => $exportacao->arquivo,
                                'erro' => $e->getMessage(),
                            ]);
                        }
                    }
                });

            Log::info("JobDeletaExportacaoExcel: removidas {$atualizados} exportações (elegíveis={$total})");
        } catch (\Exception $e) {
            Log::error($e->getFile() . ' - ' . $e->getMessage() . ' - ' . $e->getCode() . ' Deleta Exportacao');
        }
    }
}
