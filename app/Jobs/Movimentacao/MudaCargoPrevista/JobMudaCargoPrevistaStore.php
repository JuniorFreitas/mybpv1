<?php

namespace App\Jobs\Movimentacao\MudaCargoPrevista;

use App\Mail\Movimentacao\MudaCargoPrevista\CriadaMail;
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
    public $timeout = 300;

    public function __construct($mudaCargoPrevista)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'email_de' => auth()->user()->login,
            'nome_para' => $mudaCargoPrevista->Gestor->nome,
            'email_para' => $mudaCargoPrevista->Gestor->login,
            'id' => $mudaCargoPrevista->id,
            'cargo_anterior' => $mudaCargoPrevista->VagaAbertaAnterior->Vaga->nome,
            'cargo_novo' => is_null($mudaCargoPrevista->nova_vaga_aberta_id) ? "Não mudou de cargo" : $mudaCargoPrevista->VagaAbertaNova->Vaga->nome,
            'colaborador' => $mudaCargoPrevista->Colaborador->nome,
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
