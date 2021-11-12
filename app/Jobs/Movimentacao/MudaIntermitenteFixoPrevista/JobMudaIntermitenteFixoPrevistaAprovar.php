<?php

namespace App\Jobs\Movimentacao\MudaIntermitenteFixoPrevista;

use App\Mail\Movimentacao\MudaIntermitenteFixoPrevista\AprovacaoMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobMudaIntermitenteFixoPrevistaAprovar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $mail;

    public function __construct($mudaIntermitentePrevista)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $mudaIntermitentePrevista->UserCadastrou->nome,
            'email_para' => $mudaIntermitentePrevista->UserCadastrou->login,
            'status_aprovacao' => $mudaIntermitentePrevista->status_aprovacao,
            'id' => $mudaIntermitentePrevista->id,
            'cargo_anterior' => $mudaIntermitentePrevista->CargoAnterior->nome,
            'cargo_novo' => $mudaIntermitentePrevista->NovoCargo->nome,
            'colaborador' => $mudaIntermitentePrevista->Colaborador->nome
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
