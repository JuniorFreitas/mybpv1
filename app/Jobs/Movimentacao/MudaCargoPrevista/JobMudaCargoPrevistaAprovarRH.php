<?php

namespace App\Jobs\Movimentacao\MudaCargoPrevista;


use App\Mail\Movimentacao\FeriasPrevista\AprovacaoMail;
use App\Mail\Movimentacao\FeriasPrevista\AprovacaoRhMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobMudaCargoPrevistaAprovarRH implements ShouldQueue
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

    public function __construct($mudaCargoPrevista)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $mudaCargoPrevista->UserCadastrou->nome,
            'email_para' => $mudaCargoPrevista->UserCadastrou->login,
            'status_aprovacao' => $mudaCargoPrevista->resposta_rh,
            'ferias_id' => $mudaCargoPrevista->id,
            'colaborador' => $mudaCargoPrevista->Colaborador->nome,
            'empresa_id' => auth()->user()->empresa_id
        ];

        $this->mailGestor = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $mudaCargoPrevista->QuemAprovou->nome,
            'email_para' => $mudaCargoPrevista->QuemAprovou->login,
            'status_aprovacao' => $mudaCargoPrevista->resposta_rh,
            'ferias_id' => $mudaCargoPrevista->id,
            'colaborador' => $mudaCargoPrevista->Colaborador->nome,
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
