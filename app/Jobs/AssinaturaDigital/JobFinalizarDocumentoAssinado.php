<?php

namespace App\Jobs\AssinaturaDigital;

use App\Services\AssinaturaDigital\AssinaturaDigitalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JobFinalizarDocumentoAssinado implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected int $documentoId;

    public function __construct(int $documentoId)
    {
        $this->documentoId = $documentoId;
    }

    public function handle(AssinaturaDigitalService $service): void
    {
        $service->processarPosConclusao($this->documentoId);
    }
}

