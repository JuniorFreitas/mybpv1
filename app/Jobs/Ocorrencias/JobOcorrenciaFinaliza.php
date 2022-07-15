<?php

namespace App\Jobs\Ocorrencias;

use App\Mail\Ocorrencias\FinalizouMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobOcorrenciaFinaliza implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $mail;

    public $tries = 3;

    public function __construct($ocorrencia,$userPara)
    {
        $this->mail = [
            'nome_para' => $userPara->nome,
            'email_para' => $userPara->login,
            'assunto_ocorrencia' => $ocorrencia->assunto,
            'ocorrencia_id' => $ocorrencia->id,
            'finalizado_por' => User::find($ocorrencia->quem_finalizou)->nome,
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

        \Mail::send(new FinalizouMail($this->mail));
    }
}
