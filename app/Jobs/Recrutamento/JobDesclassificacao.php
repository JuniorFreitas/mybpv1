<?php

namespace App\Jobs\Recrutamento;

use App\Mail\DesclassificacaoMail;
use App\Models\Sistema;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobDesclassificacao implements ShouldQueue
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
            \Mail::send(new DesclassificacaoMail([
                'nome' => $this->mail['nome'],
                'email' => trim($this->mail['email'])
            ]));
        }
    }
}
