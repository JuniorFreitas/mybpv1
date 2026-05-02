<?php

namespace App\Jobs\Movimentacao\TransferenciaPrevista;

use App\Mail\Movimentacao\TransferenciaPrevista\AprovacaoMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobTransferenciaPrevistaAprovar implements ShouldQueue
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

    public function __construct($transferenciaPrevista)
    {
        $this->mail = [
            'nome_de' => auth()->user()->nome,
            'nome_para' => $transferenciaPrevista->UserCadastrou->nome,
            'email_para' => $transferenciaPrevista->UserCadastrou->login,
            'status_aprovacao' => $transferenciaPrevista->status_aprovacao,
            'id' => $transferenciaPrevista->id,
            'centro_custo_origem' => $transferenciaPrevista->CentroCustoOrigem?->label ?? 'Não informado',
            'centro_custo_destino' => $transferenciaPrevista->CentroCustoDestino->label,
            'colaborador' => $transferenciaPrevista->Colaborador->nome,
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
