<?php

namespace App\Mail\Admissao\Importacao;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ImportacaoConcluidaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public bool $comErro;

    public string $dataProcessamento;

    public function __construct(
        public string $nomeUsuario,
        public string $emailUsuario,
        public int $totalProcessadas,
        public int $totalSucesso,
        public int $totalErros,
        public ?string $relatorioConteudoCsv = null
    ) {
        $this->to($this->emailUsuario, $this->nomeUsuario);
        $this->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
        $this->comErro = $totalErros > 0;
        $this->dataProcessamento = now()->format('d/m/Y H:i');
        $assunto = $totalErros > 0
            ? 'Importação de Admissões – Concluída com erros'
            : 'Importação de Admissões – Concluída';
        $this->subject($assunto);
    }

    public function build(): self
    {
        $view = $this->view('email.admissao.importacao.concluida');

        if ($this->relatorioConteudoCsv !== null && $this->relatorioConteudoCsv !== '') {
            $view->attachData($this->relatorioConteudoCsv, 'relatorio_importacao_admissoes.csv', [
                'mime' => 'text/csv',
            ]);
        }

        return $view;
    }
}
