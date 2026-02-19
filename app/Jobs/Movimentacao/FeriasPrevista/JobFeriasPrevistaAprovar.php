<?php

namespace App\Jobs\Movimentacao\FeriasPrevista;


use App\Mail\Movimentacao\FeriasPrevista\AprovacaoMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobFeriasPrevistaAprovar implements ShouldQueue
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
            'nome_para' => $feriasPrevista->UserCadastrou->nome,
            'email_para' => $feriasPrevista->UserCadastrou->login,
            'status_aprovacao' => $feriasPrevista->status_aprovacao,
            'ferias_id' => $feriasPrevista->id,
            'colaborador' => $feriasPrevista->Colaborador->nome,
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
