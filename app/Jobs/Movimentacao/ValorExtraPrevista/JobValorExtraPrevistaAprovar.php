<?php

namespace App\Jobs\Movimentacao\ValorExtraPrevista;


use App\Mail\Movimentacao\ValorExtraPrevista\AprovacaoMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobValorExtraPrevistaAprovar implements ShouldQueue
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

    public function __construct($valorExtraPrevista)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $valorExtraPrevista->UserCadastrou->nome,
            'email_para' => $valorExtraPrevista->UserCadastrou->login,
            'status_aprovacao' => $valorExtraPrevista->status_aprovacao,
            'id' => $valorExtraPrevista->id,
            'colaborador' => $valorExtraPrevista->Colaborador->nome,
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
