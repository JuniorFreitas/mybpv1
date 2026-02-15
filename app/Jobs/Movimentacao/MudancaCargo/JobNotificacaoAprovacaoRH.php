<?php

namespace App\Jobs\Movimentacao\MudancaCargo;

use App\Mail\Movimentacao\MudancaCargo\NotificacaoAprovacaoMail;
use App\Models\MudancaCargo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JobNotificacaoAprovacaoRH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected $mudanca_cargo_id;
    protected $emails;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mudanca_cargo_id, array $emails)
    {
        $this->mudanca_cargo_id = $mudanca_cargo_id;
        $this->emails = $emails;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (empty($this->emails)) {
            return;
        }

        // Busca com eager loading para reduzir queries
        $mudanca_cargo = MudancaCargo::withoutGlobalScopes()
            ->with(['Colaborador:id,nome', 'VagaAbertaAnterior.Vaga:id,nome', 'VagaAbertaNova.Vaga:id,nome'])
            ->find($this->mudanca_cargo_id);

        if (!$mudanca_cargo) {
            return;
        }

        $dados = [
            'mudanca_cargo_id' => $mudanca_cargo->id,
            'colaborador' => $mudanca_cargo->Colaborador ? $mudanca_cargo->Colaborador->nome : 'N/A',
            'cargo_anterior' => ($mudanca_cargo->VagaAbertaAnterior && $mudanca_cargo->VagaAbertaAnterior->Vaga) ? $mudanca_cargo->VagaAbertaAnterior->Vaga->nome : 'N/A',
            'cargo_novo' => ($mudanca_cargo->VagaAbertaNova && $mudanca_cargo->VagaAbertaNova->Vaga) ? $mudanca_cargo->VagaAbertaNova->Vaga->nome : 'Não mudou de cargo',
            'empresa_id' => $mudanca_cargo->empresa_id,
            'etapa' => 'RH',
            'link' => route('g.movimentacao.index'),
            'tipo' => 'nova solicitação',
            'status_aprovacao_gestor' => $mudanca_cargo->status_aprovacao_gestor,
            'status_aprovacao_extra' => $mudanca_cargo->status_aprovacao_extra,
            'data_solicitacao' => $mudanca_cargo->created_at ? $mudanca_cargo->created_at->format('d/m/Y H:i') : null,
            'data_aprovacao_gestor' => $mudanca_cargo->data_aprovacao_gestor ? \Carbon\Carbon::parse($mudanca_cargo->data_aprovacao_gestor)->format('d/m/Y H:i') : null,
            'data_aprovacao_extra' => $mudanca_cargo->data_aprovacao_extra ? \Carbon\Carbon::parse($mudanca_cargo->data_aprovacao_extra)->format('d/m/Y H:i') : null,
        ];

        // Usa o primeiro email como destinatário principal e os demais como BCC
        $email_principal = array_shift($this->emails);
        if (!empty($this->emails)) {
            $dados['emails_bcc'] = $this->emails;
        }

        \Mail::to($email_principal)->send(new NotificacaoAprovacaoMail($dados));
    }
}
