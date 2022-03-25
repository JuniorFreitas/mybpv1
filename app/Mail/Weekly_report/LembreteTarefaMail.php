<?php

namespace App\Mail\Weekly_report;

use App\Events\WeeklyReport\TarefaEvent;
use App\Models\Tarefa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LembreteTarefaMail extends Mailable {
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $tries = 3;
    public $de;
    public $para;
    public $acao;
    public $tarefa;
    public $assunto;
    public $mensagem;

    public function __construct($dados) {

        //$this->de = $dados['de'];
        $this->para = $dados['para'];
        $this->tarefa = $dados['modelTarefa'];
        $this->subject='Lembrete de tarefa';

        $this->to($this->para->login, $this->para->nome);
        $this->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
        $this->replyTo($this->para->login, $this->para->nome);


    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build() {
        return $this->view('email.weekly-report.lembreteTarefa');
    }
}
