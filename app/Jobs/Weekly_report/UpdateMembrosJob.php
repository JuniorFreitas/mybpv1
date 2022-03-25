<?php

namespace App\Jobs\Weekly_report;

use App\Mail\Weekly_report\UpdateMembrosMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class UpdateMembrosJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $dados;

    public function __construct($dados) {
        $this->dados = $dados;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        Mail::send(new UpdateMembrosMail([
            'de' => $this->dados['de'],
            'para' => $this->dados['para'],
            'acao' => $this->dados['acao'],
            'modelTarefa' => $this->dados['modelTarefa']
        ]));
    }
}
