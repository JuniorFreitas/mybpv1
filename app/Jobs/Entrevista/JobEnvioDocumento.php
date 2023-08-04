<?php

namespace App\Jobs\Entrevista;

use App\Mail\Entrevista\EnvioDocumentosMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class JobEnvioDocumento implements ShouldQueue
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
            'url_documento' => $dados['url_documento'],
            'observacao' => $dados['observacao'],
        ];

        if (isset($dados['anexo'])){
            $this->mail['anexo'] = $dados['anexo'];
        }

        if (isset($dados['url_checklist'])){
            $this->mail['url_checklist'] = $dados['url_checklist'];
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::send(new EnvioDocumentosMail($this->mail));
    }
}
