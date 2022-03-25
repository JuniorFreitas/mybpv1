<?php

namespace App\Mail\Weekly_report;

use App\Events\WeeklyReport\TarefaEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UpdateMembrosMail extends Mailable
{
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
    public function __construct($dados)
    {
        $this->de = $dados['de'];
        $this->para = $dados['para'];
        $this->acao = $dados['acao'];
        $this->tarefa = $dados['modelTarefa'];

        $this->to($this->para->login, $this->para->nome);
        $this->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
        $this->replyTo($this->para->login,$this->para->nome);
        if($this->acao == TarefaEvent::ACAO_ADD){
            $this->subject = 'Nova tarefa para você';
            $this->assunto = 'Nova tarefa para você';
        }
        if($this->acao == TarefaEvent::ACAO_DELETE){
            $this->subject = 'Você foi removido de uma tarefa';
            $this->assunto = 'Você foi removido de uma tarefa';
        }


    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('email.weekly-report.updateMembros');
    }
}
