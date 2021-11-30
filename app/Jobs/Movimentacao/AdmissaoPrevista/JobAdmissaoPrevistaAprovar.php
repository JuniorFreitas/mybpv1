<?php

namespace App\Jobs\Movimentacao\AdmissaoPrevista;



use App\Mail\Movimentacao\AdmissaoPrevista\AprovacaoMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobAdmissaoPrevistaAprovar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $mail;

    public function __construct($admissaoPrevista)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $admissaoPrevista->UserCadastrou->nome,
            'email_para' => $admissaoPrevista->UserCadastrou->login,
            'status_aprovacao' => $admissaoPrevista->status_aprovacao,
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
        \Mail::send(new AprovacaoMail($this->mail));
    }
}
