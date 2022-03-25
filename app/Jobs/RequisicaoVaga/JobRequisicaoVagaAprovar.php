<?php

namespace App\Jobs\RequisicaoVaga;

use App\Mail\RequisicaoVagas\AprovacaoMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobRequisicaoVagaAprovar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $mail;
    public $tries = 3;

    public function __construct($requisicaoVaga)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $requisicaoVaga->User->nome,
            'email_para' => $requisicaoVaga->User->login,
            'status_aprovacao' => $requisicaoVaga->status_aprovacao,
            'requisicao_id' => $requisicaoVaga->id,
            'cargo' => $requisicaoVaga->Cargo->nome
        ];

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Mail::send(new AprovacaoMail($this->mail));
    }
}
