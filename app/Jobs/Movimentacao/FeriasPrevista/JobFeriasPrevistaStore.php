<?php

namespace App\Jobs\Movimentacao\FeriasPrevista;

use App\Mail\Movimentacao\FeriasPrevista\CriadaMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobFeriasPrevistaStore implements ShouldQueue
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

    public function __construct($feriasPrevista)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'email_de' => auth()->user()->login,
            'nome_para' => $feriasPrevista->Gestor->nome,
            'email_para' => $feriasPrevista->Gestor->login,
            'ferias_id' => $feriasPrevista->id,
            'colaborador' => $feriasPrevista->Admissao->Feedback->Curriculo->nome,
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
