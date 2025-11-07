<?php

namespace App\Jobs\Admissao\Historico\AvaliacaoNoventaVencimento;

use App\Mail\Admissao\Historico\AvaliacaoNoventaVencimento\AvaliacaoNoventaVencimentoMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAvaliacaoNoventaVencimentoMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Número de tentativas do Job
     */
    public $tries = 3;

    /** @var User */
    public $usuario;

    /** @var array */
    public $vencimentos;

    /** @var int */
    public $empresaId;

    /**
     * Create a new job instance.
     */
    public function __construct(User $usuario, array $vencimentos, int $empresaId)
    {
        $this->usuario = $usuario;
        $this->vencimentos = $vencimentos;
        $this->empresaId = $empresaId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        \Mail::send(new AvaliacaoNoventaVencimentoMail([
            'usuario' => $this->usuario,
            'vencimentos' => $this->vencimentos,
            'empresa_id' => $this->empresaId,
        ]));
    }
}
