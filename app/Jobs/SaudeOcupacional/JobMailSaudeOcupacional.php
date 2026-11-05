<?php

namespace App\Jobs\SaudeOcupacional;

use App\Mail\SaudeOcupacional\SaudeOcupacionalMail;
use App\Models\TipoRecebeEmail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Envia (em fila) um e-mail da Saúde Ocupacional, conferindo antes se o usuário ainda quer receber.
 */
class JobMailSaudeOcupacional implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const TIPO_MARCOS = 'marcos';

    public const TIPO_RESUMO = 'resumo';

    public $tries = 3;

    /**
     * @param  array<string, mixed>  $dados
     */
    public function __construct(public string $tipo, public int $usuarioId, public array $dados)
    {
    }

    public function handle(): void
    {
        if (!User::elegivelParaNotificacaoEmail($this->usuarioId, TipoRecebeEmail::VENCIMENTO_ASO)) {
            Log::info('E-mail Saúde Ocupacional não enviado: usuário sem preferência, inativo ou bloqueado', ['usuario_id' => $this->usuarioId]);

            return;
        }

        $usuario = User::withoutGlobalScopes()->find($this->usuarioId);
        if (!$usuario) {
            return;
        }

        Mail::send(new SaudeOcupacionalMail($this->tipo, $usuario, $this->dados));
        Log::info('E-mail Saúde Ocupacional enviado', ['tipo' => $this->tipo, 'usuario_id' => $this->usuarioId]);
    }
}
