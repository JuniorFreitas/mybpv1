<?php

namespace App\Jobs\AdmissoesPrevista;

use App\Mail\AdmissoesPrevista\NotificacaoAprovacaoMail;
use App\Models\AdmissoesPrevista;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class JobNotificacaoAprovacao implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $admissao;
    protected $email;

    public $timeout = 300;
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AdmissoesPrevista $admissao, string $email)
    {
        $this->admissao = $admissao;
        $this->email = $email;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info("=== JOB NOTIFICAÇÃO USUÁRIO - INICIADO ===");
        \Log::info("Admissão ID: {$this->admissao->id}");
        \Log::info("Email destino: {$this->email}");

        if (empty($this->email)) {
            \Log::warning("Email vazio - abortando");
            return;
        }

        Mail::to($this->email)->send(
            new NotificacaoAprovacaoMail($this->admissao, 'aprovacao')
        );

        \Log::info("Email enviado com sucesso");
        \Log::info("=== JOB NOTIFICAÇÃO USUÁRIO - CONCLUÍDO ===");
    }
}
