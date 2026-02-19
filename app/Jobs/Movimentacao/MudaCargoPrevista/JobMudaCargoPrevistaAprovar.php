<?php

namespace App\Jobs\Movimentacao\MudaCargoPrevista;

use App\Mail\Movimentacao\MudaCargoPrevista\AprovacaoMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobMudaCargoPrevistaAprovar implements ShouldQueue
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
            'nome_para' => $mudaCargoPrevista->UserCadastrou->nome,
            'email_para' => $mudaCargoPrevista->UserCadastrou->login,
            'status_aprovacao' => $mudaCargoPrevista->status_aprovacao,
            'id' => $mudaCargoPrevista->id,
            'cargo_anterior' => $mudaCargoPrevista->CargoAnterior->nome,
            'cargo_novo' => $mudaCargoPrevista->NovoCargo->nome,
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
        \Mail::send(new AprovacaoMail($this->mail));
    }
}
