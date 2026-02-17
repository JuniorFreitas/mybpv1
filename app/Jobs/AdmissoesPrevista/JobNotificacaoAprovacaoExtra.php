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

class JobNotificacaoAprovacaoExtra implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $admissao;
    protected $emails;

    public $timeout = 300;
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(AdmissoesPrevista $admissao, array $emails)
    {
        $this->admissao = $admissao;
        $this->emails = $emails;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info("=== JOB NOTIFICAÇÃO APROVAÇÃO EXTRA - INICIADO ===");
        \Log::info("Admissão ID: {$this->admissao->id}");
        \Log::info("Emails: " . implode(', ', $this->emails));

        if (empty($this->emails)) {
            \Log::warning("Emails vazios - abortando");
            return;
        }

        // Envia um único email com todos os destinatários em BCC
        $primeiroEmail = $this->emails[0];
        $mailable = new NotificacaoAprovacaoMail($this->admissao, 'aprovacao_extra');

        if (count($this->emails) > 1) {
            $mailable->bcc(array_slice($this->emails, 1));
            \Log::info("BCC adicionados: " . implode(', ', array_slice($this->emails, 1)));
        }

        Mail::to($primeiroEmail)->send($mailable);
        \Log::info("Email enviado com sucesso para: {$primeiroEmail}");
        \Log::info("=== JOB NOTIFICAÇÃO APROVAÇÃO EXTRA - CONCLUÍDO ===");
    }
}
