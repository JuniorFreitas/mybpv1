<?php

namespace App\Jobs\Recrutamento;

use App\Mail\ProximaEtapaMail;
use App\Models\Sistema;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobProximaEtapa implements ShouldQueue
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
            \Mail::send(new ProximaEtapaMail([
                'nome' => $this->mail['nome'],
                'email' => $this->mail['email'],
                'empresa' => $this->mail['empresa'],
                'logo' => $this->mail['logo'],
                'vaga_selecionada' => $this->mail['vaga_selecionada'],
                'local_entrevista' => $this->mail['local_entrevista'],
                'data_entrevista' => $this->mail['data_entrevista'],
            ]));
        }
    }
}
