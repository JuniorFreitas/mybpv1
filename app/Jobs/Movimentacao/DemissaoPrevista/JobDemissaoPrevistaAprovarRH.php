<?php

namespace App\Jobs\Movimentacao\DemissaoPrevista;


use App\Mail\Movimentacao\DemissaoPrevista\AprovacaoMail;
use App\Mail\Movimentacao\FeriasPrevista\AprovacaoRhMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobDemissaoPrevistaAprovarRH implements ShouldQueue
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

    public function __construct($demissaoPrevista)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $demissaoPrevista->UserCadastrou->nome,
            'email_para' => $demissaoPrevista->UserCadastrou->login,
            'status_aprovacao' => $demissaoPrevista->status_aprovacao,
            'demissao_id' => $demissaoPrevista->id,
            'colaborador' => $demissaoPrevista->Colaborador->nome
        ];

        $this->mailGestor = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $demissaoPrevista->QuemAprovou->nome,
            'email_para' => $demissaoPrevista->QuemAprovou->login,
            'status_aprovacao' => $demissaoPrevista->resposta_rh,
            'ferias_id' => $demissaoPrevista->id,
            'colaborador' => $demissaoPrevista->Colaborador->nome
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
