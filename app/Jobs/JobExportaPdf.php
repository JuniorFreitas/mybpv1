<?php

namespace App\Jobs;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Models\Exportacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use PDF;


class JobExportaPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $queue;
    public $nome_arquivo;
    public $view;
    public $local;
    public $usuario;
    public $model;
    public $timeout = 600;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($usuario, $local, $model, $nome_arquivo, $view)
    {
        $this->model = $model;
        $this->nome_arquivo = $nome_arquivo;
        $this->view = $view;
        $this->local = $local;
        $this->usuario = $usuario;
        $this->delay = now()->addSeconds(rand(1, 1));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pdf = PDF::loadView($this->view, [ 'dados' => $this->model, 'usuario' => $this->usuario])
                    ->setPaper('A4', 'landscape');

        \Storage::disk('disco-exportacao')->put($this->nome_arquivo, $pdf->output());

        Exportacao::create([
            'user_id' => $this->usuario['id'],
            'arquivo' => $this->nome_arquivo,
            'local' => $this->local,
            'removido' => false,
        ]);

        Event::dispatch(new NotificacaoEvent([
            'user_id' => $this->usuario['id'],
            'local' => $this->local,
        ], NotificacaoEvent::EXPORTACAO_PDF, NotificacaoEvent::TIPO_PADRAO));
    }
}
