<?php

namespace App\Jobs\ControleExames;

use App\Mail\ControleExames\FichaClinicaMail;
use App\Mail\ControleExames\FichaColaboradorMail;
use App\Models\Sistema;
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
        $this->mail = $dados;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Mail::send(new FichaClinicaMail($this->mail['dtEmailClinica']));
        if ($this->mail['dtEmailColaborador']['email'] != Sistema::EMAILPADRAO){
            \Mail::send(new FichaColaboradorMail($this->mail['dtEmailColaborador']));
        }
    }
}
