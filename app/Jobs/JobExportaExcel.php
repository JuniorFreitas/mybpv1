<?php

namespace App\Jobs;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Exports\ModeloRowsExport;
use App\Models\Exportacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

class JobExportaExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
//    public $delay;
    public $queue;
    public $headdings = [];
    public $rows = [];
    public $nome_arquivo;
    public $local;
    public $usuario;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($usuario, $local, $headdings, $rows, $nome_arquivo)
    {
        $this->headdings = $headdings;
        $this->rows = $rows;
        $this->nome_arquivo = $nome_arquivo;
        $this->local = $local;
        $this->usuario = $usuario;
        $this->delay = now()->addSeconds(rand(5, 10));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Exportacao::create([
            'user_id' => $this->usuario,
            'arquivo' => $this->nome_arquivo,
            'local' => $this->local,
            'removido' => false,
        ]);

        \Excel::store(new ModeloRowsExport($this->headdings, $this->rows), $this->nome_arquivo, 'disco-exportacao');

        Event::dispatch(new NotificacaoEvent([
            'user_id' => $this->usuario,
            'local' => $this->local,
        ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));
    }
}
