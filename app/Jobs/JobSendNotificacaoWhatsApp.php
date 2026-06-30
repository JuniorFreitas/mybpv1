<?php

namespace App\Jobs;

use App\Classes\ZapNotificacao;
use App\Models\NotificacaoWhatsapp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JobSendNotificacaoWhatsApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $dados;
    public $upload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($dados)
    {
        $this->dados = $dados;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $zap = new ZapNotificacao();

        if (!$zap->deveEnviarWhatsapp($this->dados)) {
            return;
        }

        $dados = $zap->normalizarDadosEnvio($this->dados);
        $send = $zap->send($dados);
        if (!isset($this->dados['sistema'])) {
            if ($send['status']) {
                $notificacao = new NotificacaoWhatsapp();
                $notificacao->enviado_id = $this->dados['enviado_id'];
                $notificacao->user_id = auth()->id() ?? 1;
                $notificacao->messageid = 0;
                $notificacao->telefone = $dados['telefone'];
                $notificacao->mensagem = $dados['mensagem'];
                $notificacao->save();
            }
        }

        \Log::info(print_r($send, true));
//        var_dump($send);
//        return $send;
    }
}
