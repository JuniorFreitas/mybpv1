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
        $this->delay = now()->addSeconds(rand(5, 10));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new ZapNotificacao())->enviar([
            'enviado_id' => $this->dados['enviado_id'],
            'telefone' => $this->dados['telefone'],
            'mensagem' => $this->dados['mensagem'],
        ]);
    }
}
