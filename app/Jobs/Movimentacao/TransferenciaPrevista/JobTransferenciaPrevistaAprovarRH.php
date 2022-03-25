<?php

namespace App\Jobs\Movimentacao\TransferenciaPrevista;


use App\Mail\Movimentacao\TransferenciaPrevista\AprovacaoMail;
use App\Mail\Movimentacao\TransferenciaPrevista\AprovacaoRhMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobTransferenciaPrevistaAprovarRH implements ShouldQueue
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

    public function __construct($transferenciaPrevista)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $transferenciaPrevista->UserCadastrou->nome,
            'email_para' => $transferenciaPrevista->UserCadastrou->login,
            'status_aprovacao' => $transferenciaPrevista->resposta_rh,
            'ferias_id' => $transferenciaPrevista->id,
            'colaborador' => $transferenciaPrevista->Colaborador->nome
        ];

        $this->mailGestor = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $transferenciaPrevista->QuemAprovou->nome,
            'email_para' => $transferenciaPrevista->QuemAprovou->login,
            'status_aprovacao' => $transferenciaPrevista->resposta_rh,
            'ferias_id' => $transferenciaPrevista->id,
            'colaborador' => $transferenciaPrevista->Colaborador->nome
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
