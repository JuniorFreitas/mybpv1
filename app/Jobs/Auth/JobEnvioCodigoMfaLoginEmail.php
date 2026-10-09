<?php

namespace App\Jobs\Auth;

use App\Mail\Auth\CodigoMfaLoginMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class JobEnvioCodigoMfaLoginEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected int $userId;
    protected string $codigo;
    protected int $ttlMinutos;
    protected string $empresaNome;
    protected ?int $empresaId;

    public function __construct(
        int $userId,
        string $codigo,
        int $ttlMinutos,
        string $empresaNome = '',
        ?int $empresaId = null
    ) {
        $this->userId = $userId;
        $this->codigo = $codigo;
        $this->ttlMinutos = $ttlMinutos;
        $this->empresaNome = $empresaNome;
        $this->empresaId = $empresaId;
    }

    public function handle(): void
    {
        $user = User::withoutGlobalScopes()->find($this->userId);
        if (!$user || empty($user->login) || !filter_var($user->login, FILTER_VALIDATE_EMAIL)) {
            Log::warning('JobEnvioCodigoMfaLoginEmail: usuário inválido ou sem e-mail', [
                'user_id' => $this->userId,
            ]);

            return;
        }

        Mail::to($user->login)->send(new CodigoMfaLoginMail(
            $user->nome ?: $user->login,
            $this->codigo,
            $this->ttlMinutos,
            $this->empresaNome,
            $this->empresaId
        ));
    }
}
