<?php

namespace App\Jobs\Movimentacao\MudaCargoPrevista;

use App\Mail\Movimentacao\MudaIntermitenteFixoPrevista\CriadaMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobMudaCargoPrevistaStore implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $mail;
    public $tries = 3;

    public function __construct($mudaCargoPrevista)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'email_de' => auth()->user()->login,
            'nome_para' => $mudaCargoPrevista->GestorAprovacao->nome,
            'email_para' => $mudaCargoPrevista->GestorAprovacao->login,
            'id' => $mudaCargoPrevista->id,
            'cargo_anterior' => $mudaCargoPrevista->CargoAnterior->nome,
            'cargo_novo' => $mudaCargoPrevista->NovoCargo->nome,
            'colaborador' => $mudaCargoPrevista->Colaborador->nome
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
