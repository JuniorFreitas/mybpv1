<?php

namespace App\Jobs;

use App\Mail\Admissao\Processo\VencimentoAsoMail;
use App\Models\TipoRecebeEmail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use MasterTag\DataHora;

class JobMailVencimentoAso implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $mail;
    public $tries = 3;

    public function __construct($dados)
    {
        $this->mail = $dados;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $usuario = $this->mail['usuario'] ?? null;
        $usuarioId = (int) ($usuario->id ?? 0);
        if ($usuarioId === 0 || !User::elegivelParaNotificacaoEmail($usuarioId, TipoRecebeEmail::VENCIMENTO_ASO)) {
            \Log::info('E-mail de Vencimento ASO não enviado: usuário sem check, inativo ou bloqueado', [
                'usuario_id' => $usuarioId,
            ]);

            return;
        }

        \Mail::send(new VencimentoAsoMail($this->mail));
    }
}
