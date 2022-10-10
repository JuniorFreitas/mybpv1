<?php

namespace App\Jobs\Admissao\Apontamento\Cih;

use App\Mail\Movimentacao\AdmissaoPrevista\AprovacaoMail;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class JobCihAprovarReprovar implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $mail;

    public $tries = 3;

    public function __construct(array $dados)
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
        \Mail::send(new \App\Mail\Admissao\Apontamento\Cih\AprovaReprovaMail($this->mail));
    }
}
