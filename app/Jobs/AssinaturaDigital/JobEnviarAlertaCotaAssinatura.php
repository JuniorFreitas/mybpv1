<?php

namespace App\Jobs\AssinaturaDigital;

use App\Mail\AssinaturaDigital\AlertaCotaAssinaturaMail;
use App\Models\Cliente;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class JobEnviarAlertaCotaAssinatura implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected int $empresaId;
    protected int $percentual;
    protected array $resumo;
    protected array $emails;

    public function __construct(int $empresaId, int $percentual, array $resumo, array $emails)
    {
        $this->empresaId = $empresaId;
        $this->percentual = $percentual;
        $this->resumo = $resumo;
        $this->emails = $emails;
    }

    public function handle(): void
    {
        if (empty($this->emails)) {
            return;
        }

        $empresa = Cliente::withoutGlobalScopes()->find($this->empresaId);
        $nomeEmpresa = $empresa ? ($empresa->razao_social ?: $empresa->nome_fantasia ?: $empresa->apelido) : 'Empresa';

        foreach ($this->emails as $email) {
            if (empty($email)) {
                continue;
            }
            try {
                Mail::to($email)->send(new AlertaCotaAssinaturaMail(
                    $nomeEmpresa,
                    $this->percentual,
                    $this->resumo,
                    $this->empresaId
                ));
            } catch (\Throwable $e) {
                Log::warning('JobEnviarAlertaCotaAssinatura: falha ao enviar e-mail', [
                    'empresa_id' => $this->empresaId,
                    'percentual' => $this->percentual,
                    'email' => $email,
                    'erro' => $e->getMessage(),
                ]);
            }
        }
    }
}

