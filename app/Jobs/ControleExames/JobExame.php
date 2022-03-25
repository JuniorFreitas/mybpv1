<?php

namespace App\Jobs\ControleExames;

use App\Mail\ControleExames\FichaClinicaMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobExame implements ShouldQueue
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
            'clinica' => $dados['clinica'],
            'email' => trim(mb_strtolower($dados['email'])),
            'assunto' => "Encaminhamento de Exame {$dados['tipoExame']} colaborador {$dados['colaborador']}",
            'colaborador' => $dados['colaborador'],
            'idade' => $dados['idade'],
            'tipoExame' => $dados['tipoExame'],
            'link' => $dados['link']
        ];

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Mail::send(new FichaClinicaMail($this->mail));
    }
}
