<?php

namespace App\Jobs\Movimentacao\MudaIntermitenteFixoPrevista;

use App\Mail\Movimentacao\MudaCargoPrevista\CriadaMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobMudaIntermintenteFixoPrevistaStore implements ShouldQueue
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
            'email_de' => auth()->user()->login,
            'nome_para' => $mudaIntermitentePrevista->GestorAprovacao->nome,
            'email_para' => $mudaIntermitentePrevista->GestorAprovacao->login,
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
        \Mail::send(new CriadaMail($this->mail));
    }
}
