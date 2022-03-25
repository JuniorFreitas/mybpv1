<?php

namespace App\Jobs\Movimentacao\AdmissaoPrevista;


use App\Mail\Movimentacao\AdmissaoPrevista\AprovacaoRhMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobAdmissaoPrevistaAprovarRH implements ShouldQueue
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

    public function __construct($admissaoPrevista)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $admissaoPrevista->UserCadastrou->nome,
            'email_para' => $admissaoPrevista->UserCadastrou->login,
            'status_aprovacao' => $admissaoPrevista->resposta_rh,
            'admissao_id' => $admissaoPrevista->id,
            'cargo' => $admissaoPrevista->Cargo->nome
        ];

        $this->mailGestor = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $admissaoPrevista->QuemAprovou->nome,
            'email_para' => $admissaoPrevista->QuemAprovou->login,
            'status_aprovacao' => $admissaoPrevista->resposta_rh,
            'admissao_id' => $admissaoPrevista->id,
            'cargo' => $admissaoPrevista->Cargo->nome
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
