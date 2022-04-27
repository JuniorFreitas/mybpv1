<?php

namespace App\Jobs\Entrevista\ResultadoIntegrado;

use App\Mail\Entrevista\ResultadoIntegrado\EncaminhamentoExameMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobEncaminhamentoExame implements ShouldQueue
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
        $this->mail = [
            'colaborador' => $dados['colaborador'],
            'cargo' => $dados['cargo'],
            'clinica' => $dados['clinica'],
            'tipo_pcmso' => $dados['tipo_pcmso'],
            'empresa_id' => $dados['empresa_id']
        ];

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Mail::send(new EncaminhamentoExameMail($this->mail));
    }
}
