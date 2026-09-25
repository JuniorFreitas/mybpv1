<?php

namespace App\Mail\Weekly_report;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LembreteTarefaMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $para;
    public $tarefa;
    public $assunto;

    public function __construct($dados)
    {
        $this->para = $dados['para'];
        $this->tarefa = $dados['modelTarefa'];
        $this->assunto = 'Lembrete de tarefa';
        $this->subject = $this->assunto;

        $this->to($this->para->login, $this->para->nome);
        $this->from(config('mail.from.address'), config('mail.from.name', config('app.name')));
    }

    public function build()
    {
        if ($this->tarefa && !$this->tarefa->relationLoaded('Lista')) {
            $this->tarefa->load('Lista');
        }

        return $this->view('email.weekly-report.lembreteTarefa');
    }
}
