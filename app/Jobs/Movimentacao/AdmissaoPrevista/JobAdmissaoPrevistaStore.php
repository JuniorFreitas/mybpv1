<?php

namespace App\Jobs\Movimentacao\AdmissaoPrevista;

use App\Mail\Movimentacao\AdmissaoPrevista\CriadaMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobAdmissaoPrevistaStore implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $mail;

    public $tries = 3;

    public function __construct($admissaoPrevista)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'email_de' => auth()->user()->login,
            'nome_para' => $admissaoPrevista->GestorAprovacao->nome,
            'email_para' => $admissaoPrevista->GestorAprovacao->login,
            'admissao_id' => $admissaoPrevista->id,
            'nome_pessoa' => $admissaoPrevista->nome_pessoa ?? ($admissaoPrevista->Colaborador ? $admissaoPrevista->Colaborador->nome : ''),
            'cargo' => $admissaoPrevista->Cargo->nome,
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
