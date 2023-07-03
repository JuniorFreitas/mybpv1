<?php

namespace App\Jobs\Entrevista;

use App\Mail\Entrevista\EnvioDocumentosMail;
use App\Mail\Entrevista\EnvioFeedbackDocumentosMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class JobEnvioFeedbackDocumento implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $mail;
    public $tries = 3;

    public function __construct($dados)
    {
        $this->mail = [
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'empresa_id' => $dados['empresa_id'],
            'observacao' => $dados['observacao'],
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::send(new EnvioFeedbackDocumentosMail($this->mail));
    }
}
