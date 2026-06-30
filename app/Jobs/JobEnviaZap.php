<?php

namespace App\Jobs;


use App\Classes\ZapNotificacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JobEnviaZap implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $dados;
    public $tries = 3;

    public function __construct($dados)
    {
        $this->dados = $dados;
        $this->delay = now()->addSeconds(ZapNotificacao::calcularDelayFila());
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $zap = new ZapNotificacao();
        $dados = $zap->normalizarDadosEnvio($this->dados);
        $zap->send($dados);
    }
}
