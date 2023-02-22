<?php

namespace App\Jobs\Recrutamento;

use App\Mail\ProvaMail;
use App\Mail\ProximaEtapaMail;
use App\Models\Sistema;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobProva implements ShouldQueue
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
        if ($this->mail['email'] != Sistema::EMAILPADRAO) {
            \Mail::send(new ProvaMail([
                'nome' => $this->mail['nome'],
                'email' => $this->mail['email'],
                'vaga' => $this->mail['vaga'],
                'vaga_id' => $this->mail['vaga_id'],
                'provas' => $this->mail['provas'],
            ]));
        }
    }
}
