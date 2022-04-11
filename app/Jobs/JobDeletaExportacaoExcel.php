<?php

namespace App\Jobs;

use App\Exports\ModeloRowsExport;
use App\Models\Exportacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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

    //Comentar esse metodo se for usar com Job
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
        $hoje = new DataHora();
        $exportacoes = Exportacao::whereRemovido(false)
            ->where('created_at', '>=', $hoje->dataInsert() . ' 23:59:59')
            ->get();

        foreach ($exportacoes as $exportacao) {
            $exportacao->removido = true;
            $exportacao->save();

            $disco = 'disco-exportacao';
            $discoStorage = Storage::disk($disco);
            if ($discoStorage->exists($exportacao->arquivo)) {
                $discoStorage->delete($exportacao->arquivo);
            }
        }

    }
}
