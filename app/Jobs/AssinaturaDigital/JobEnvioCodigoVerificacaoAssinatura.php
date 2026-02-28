<?php

namespace App\Jobs\AssinaturaDigital;

use App\Mail\AssinaturaDigital\CodigoVerificacaoAssinaturaMail;
use App\Models\DocumentoAssinaturaSignatario;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class JobEnvioCodigoVerificacaoAssinatura implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected int $signatarioId;
    protected string $codigo;
    protected int $ttlMinutos;
    protected string $empresaNome;
    protected ?int $empresaId;

    public function __construct(int $signatarioId, string $codigo, int $ttlMinutos, string $empresaNome = '', ?int $empresaId = null)
    {
        $this->signatarioId = $signatarioId;
        $this->codigo = $codigo;
        $this->ttlMinutos = $ttlMinutos;
        $this->empresaNome = $empresaNome;
        $this->empresaId = $empresaId;
    }

    public function handle(): void
    {
        $signatario = DocumentoAssinaturaSignatario::withoutGlobalScopes()->find($this->signatarioId);
        if (!$signatario || empty($signatario->email)) {
            Log::warning('JobEnvioCodigoVerificacaoAssinatura: signatário inválido', ['signatario_id' => $this->signatarioId]);
            return;
        }

        Mail::to($signatario->email)->send(new CodigoVerificacaoAssinaturaMail(
            $signatario->nome ?: $signatario->email,
            $this->codigo,
            $this->ttlMinutos,
            $this->empresaNome,
            $this->empresaId
        ));
    }
}

