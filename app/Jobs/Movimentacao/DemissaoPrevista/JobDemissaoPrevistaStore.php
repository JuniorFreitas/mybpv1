<?php

namespace App\Jobs\Movimentacao\DemissaoPrevista;

use App\Mail\Movimentacao\DemissaoPrevista\CriadaMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobDemissaoPrevistaStore implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $mail;
    public $tries = 3;

    public function __construct($demissaoPrevista)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'email_de' => auth()->user()->login,
            'empresa' => auth()->user()->Empresa->nome_fantasia,
            'nome_para' => $demissaoPrevista->GestorAprovacao->nome,
            'email_para' => $demissaoPrevista->GestorAprovacao->login,
            'demissao_id' => $demissaoPrevista->id,
            'colaborador' => $demissaoPrevista->Colaborador->nome,
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
