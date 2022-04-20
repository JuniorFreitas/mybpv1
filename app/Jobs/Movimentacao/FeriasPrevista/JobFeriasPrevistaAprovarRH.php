<?php

namespace App\Jobs\Movimentacao\FeriasPrevista;


use App\Mail\Movimentacao\FeriasPrevista\AprovacaoMail;
use App\Mail\Movimentacao\FeriasPrevista\AprovacaoRhMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobFeriasPrevistaAprovarRH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $mail;
    public $tries = 3;
    public $mailGestor;

    public function __construct($feriasPrevista)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $feriasPrevista->UserCadastrou->nome,
            'email_para' => $feriasPrevista->UserCadastrou->login,
            'status_aprovacao' => $feriasPrevista->resposta_rh,
            'ferias_id' => $feriasPrevista->id,
            'colaborador' => $feriasPrevista->Colaborador->nome,
            'empresa_id' => auth()->user()->empresa_id
        ];

        $this->mailGestor = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $feriasPrevista->QuemAprovou->nome,
            'email_para' => $feriasPrevista->QuemAprovou->login,
            'status_aprovacao' => $feriasPrevista->resposta_rh,
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
        \Mail::send(new AprovacaoRhMail($this->mail));
        \Mail::send(new AprovacaoRhMail($this->mailGestor));
    }
}
