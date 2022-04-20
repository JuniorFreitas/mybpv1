<?php

namespace App\Jobs\RequisicaoVaga;

use App\Mail\RequisicaoVagas\CriadaMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobRequisicaoVagaStore implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $mail;
    public $tries = 3;

    public function __construct($requisicao,$userPara)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'email_de' => auth()->user()->login,
            'nome_para' => $userPara->nome,
            'email_para' => $userPara->login,
            'requisicao_id' => $requisicao->id,
            'prioridade' => $requisicao->prioridade,
            'cargo' => $requisicao->OutrasInformacoes->Cargo->nome,
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
