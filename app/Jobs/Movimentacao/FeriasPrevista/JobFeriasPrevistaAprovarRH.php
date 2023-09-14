<?php

namespace App\Jobs\Movimentacao\FeriasPrevista;


use App\Mail\Movimentacao\FeriasPrevista\AprovacaoRhMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JobFeriasPrevistaAprovarRH implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $mail;
    public $tries = 3;
    public $mailGestor;

    public function __construct($dados)
    {
        $this->mail = $dados['dados_quem_cadastrou'];

        $this->mailGestor = $dados['dados_gestor'];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Mail::send(new AprovacaoRhMail($this->mail));
        \Mail::send(new AprovacaoRhMail($this->mailGestor));
        \Artisan::call('mybp:ferias');
    }
}
