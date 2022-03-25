<?php

namespace App\Jobs\Movimentacao\ValorExtraPrevista;


use App\Mail\Movimentacao\ValorExtraPrevista\AprovacaoMail;
use App\Mail\Movimentacao\ValorExtraPrevista\AprovacaoRhMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobValorExtraPrevistaAprovarRH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $mail;
    public $mailGestor;

    public $tries = 3;

    public function __construct($valorExtraPrevista)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $valorExtraPrevista->UserCadastrou->nome,
            'email_para' => $valorExtraPrevista->UserCadastrou->login,
            'status_aprovacao' => $valorExtraPrevista->resposta_rh,
            'ferias_id' => $valorExtraPrevista->id,
            'colaborador' => $valorExtraPrevista->Colaborador->nome
        ];

        $this->mailGestor = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $valorExtraPrevista->QuemAprovou->nome,
            'email_para' => $valorExtraPrevista->QuemAprovou->login,
            'status_aprovacao' => $valorExtraPrevista->resposta_rh,
            'ferias_id' => $valorExtraPrevista->id,
            'colaborador' => $valorExtraPrevista->Colaborador->nome
        ];

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Mail::send(new AprovacaoRhMail($this->mail));
        \Mail::send(new AprovacaoRhMail($this->mailGestor));
    }
}
