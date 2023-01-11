<?php

namespace App\Jobs;

use App\Mail\Admissao\Apontamento\Intermitente\RespostaIntermitenteMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JobRespostaIntermitentes implements ShouldQueue
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
            'dados' => $dados,
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $dados = $this->mail['dados'];
        if($dados['email_gestor'] != "sistema@mybp.com.br"){
            \Mail::send(new RespostaIntermitenteMail($dados));
        }
    }
}
