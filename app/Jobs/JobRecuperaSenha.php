<?php

namespace App\Jobs;

use App\Mail\RecuperaSenhaMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use MasterTag\DataHora;

class JobRecuperaSenha implements ShouldQueue
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
            'nome' => $dados['nome'],
            'email' => $dados['email'],
            'token' => $dados['token'],
            'empresa_id' => $dados['empresa_id'],
            'expiracao' => (new DataHora($dados['expiracao']))->dataCompleta() . ' às ' . (new DataHora($dados['expiracao']))->horaCompleta()
        ];

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Mail::send(new RecuperaSenhaMail($this->mail));
    }
}
