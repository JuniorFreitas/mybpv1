<?php

namespace App\Jobs\Movimentacao\MudaIntermitenteFixoPrevista;

use App\Mail\Movimentacao\MudaIntermitenteFixoPrevista\AprovacaoRhMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobMudaIntermitenteFixoPrevistaAprovarRH implements ShouldQueue
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

    public function __construct($mudaIntermitentePrevista)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $mudaIntermitentePrevista->UserCadastrou->nome,
            'email_para' => $mudaIntermitentePrevista->UserCadastrou->login,
            'status_aprovacao' => $mudaIntermitentePrevista->resposta_rh,
            'ferias_id' => $mudaIntermitentePrevista->id,
            'colaborador' => $mudaIntermitentePrevista->Colaborador->nome
        ];

        $this->mailGestor = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $mudaIntermitentePrevista->QuemAprovou->nome,
            'email_para' => $mudaIntermitentePrevista->QuemAprovou->login,
            'status_aprovacao' => $mudaIntermitentePrevista->resposta_rh,
            'ferias_id' => $mudaIntermitentePrevista->id,
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
        \Mail::send(new AprovacaoRhMail($this->mail));
        \Mail::send(new AprovacaoRhMail($this->mailGestor));
    }
}
