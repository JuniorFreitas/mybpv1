<?php

namespace App\Jobs;

use App\Mail\Admissao\Apontamento\Intermitente\ConvocacaoIntermitenteMail;
use App\Models\Sistema;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JobConvocacaoIntermitentes implements ShouldQueue
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
            'convocados' => $dados,
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $convocados = $this->mail['convocados'];
        foreach ($convocados as $convocado) {
            if($convocado['email'] != Sistema::EMAILPADRAO){
                \Mail::send(new ConvocacaoIntermitenteMail($convocado));
            }
        }
    }
}
