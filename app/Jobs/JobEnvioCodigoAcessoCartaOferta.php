<?php

namespace App\Jobs;

use App\Mail\EnvioCodigoAcessoCartaOfertaMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class JobEnvioCodigoAcessoCartaOferta implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $mail;
    public int $tries = 3;

    public function __construct(array $dados)
    {
        $this->mail = [
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'empresa_id' => $dados['empresa_id'],
            'codigo' => $dados['codigo'],
            'minutos' => $dados['minutos'] ?? 60,
            // Nome do que está sendo acessado (o mesmo e-mail serve às telas públicas com código).
            'recurso' => $dados['recurso'] ?? 'Carta Oferta',
        ];
    }

    public function handle(): void
    {
        Mail::send(new EnvioCodigoAcessoCartaOfertaMail($this->mail));
    }
}
