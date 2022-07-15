<?php

namespace App\Jobs\Ocorrencias;

use App\Mail\Ocorrencias\CriadaMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobOcorrenciaStore implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $mail;

    public $tries = 3;

    public function __construct($ocorrencia, $userPara)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'email_de' => auth()->user()->login,
            'nome_para' => $userPara->nome,
            'email_para' => $userPara->login,
            'assunto_ocorrencia' => $ocorrencia->assunto,
            'ocorrencia_id' => $ocorrencia->id,
            'empresa_id' => auth()->user()->empresa_id
        ];

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Mail::send(new CriadaMail($this->mail));
    }
}
