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
    public function __construct($dados, $upload)
    {
        $this->dados = $dados;
        $this->upload = $upload;
        $this->delay = now()->addSeconds(rand(5, 10));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $send = (new ZapNotificacao())->send($this->dados, $this->upload);

        if ($send['status']) {
            $notificacao = new NotificacaoWhatsapp();
            $notificacao->enviado_id = $this->dados['enviado_id'];
            $notificacao->user_id = auth()->id() ?? 1;
            $notificacao->messageid = 0;
            $notificacao->telefone = $this->dados['telefone'];
            $notificacao->mensagem = $this->dados['mensagem'];
            $notificacao->save();
        }

        return $send;
    }
}
