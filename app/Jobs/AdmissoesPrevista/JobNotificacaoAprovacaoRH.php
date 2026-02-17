<?php

namespace App\Jobs\AdmissoesPrevista;

use App\Mail\AdmissoesPrevista\NotificacaoAprovacaoMail;
use App\Models\AdmissoesPrevista;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class JobNotificacaoAprovacaoRH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $admissao;

    public $timeout = 300;
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AdmissoesPrevista $admissao)
    {
        $this->admissao = $admissao;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info("=== JOB NOTIFICAÇÃO RH - INICIADO ===");
        \Log::info("Admissão ID: {$this->admissao->id}");
        \Log::info("Empresa ID: {$this->admissao->empresa_id}");

        // Busca usuários do RH
        $usuariosRH = User::where('empresa_id', $this->admissao->empresa_id)
            ->where(function ($query) {
                $query->where('privilegio_gestao_rh', true)
                    ->orWhere('privilegio_aprovar_por_rh', true);
            })
            ->whereNotNull('email')
            ->pluck('email')
            ->toArray();

        \Log::info("Usuários RH encontrados: " . implode(', ', $usuariosRH));

        if (empty($usuariosRH)) {
            \Log::warning("Nenhum usuário RH com email encontrado - abortando");
            return;
        }

        // Envia um único email com todos os destinatários em BCC
        $primeiroEmail = $usuariosRH[0];
        $mailable = new NotificacaoAprovacaoMail($this->admissao, 'aprovacao_rh');

        if (count($usuariosRH) > 1) {
            $mailable->bcc(array_slice($usuariosRH, 1));
            \Log::info("BCC adicionados: " . implode(', ', array_slice($usuariosRH, 1)));
        }

        Mail::to($primeiroEmail)->send($mailable);
        \Log::info("Email enviado com sucesso para: {$primeiroEmail}");
        \Log::info("=== JOB NOTIFICAÇÃO RH - CONCLUÍDO ===");
    }
}
