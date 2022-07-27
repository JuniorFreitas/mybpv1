<?php

namespace App\Jobs\Excel;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Exports\ModeloRowsExport;
use App\Models\Exportacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use MasterTag\DataHora;

class JobExcelCurriculos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
//    public $delay;
    public $queue;
    public $headdings;
    public $rows;
    public $nome_arquivo;
    public $local;
    public $dados;
    public $usuario;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($usuario, $dados)
    {
        $headdings = ["Nome", "CPF", "Nascimento", "PCD", "Data Cadastro", "E-mail", "Endereço", "Bairro", "Municipio", "Estado", "Vaga"];
        $rows = [];
        foreach ($dados as $row) {
            $rows[] = [
                $row->nome,
                $row->cpf,
                $row->nascimento,
                !$row->pcd ? "Não" : "Sim",
                (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5),
                $row->email,
                $row->logradouro,
                $row->bairro,
                $row->VagaAberta->Municipio ? $row->VagaAberta->Municipio->nome : "",
                $row->VagaAberta->Municipio ? $row->VagaAberta->Municipio->uf : "",
                $row->VagaAberta->VagaSelecionada->nome,
            ];
        }

        $nameArquivo = "recrutamento" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";

        $this->headdings = $headdings;
        $this->rows = $rows;
        $this->nome_arquivo = $nameArquivo;
        $this->local = 'Recrutamento';
        $this->usuario = $usuario;
//        $this->delay = now()->addSeconds(rand(5, 10));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Exportacao::create([
            'user_id' => $this->usuario,
            'arquivo' => $this->nome_arquivo,
            'local' => $this->local,
            'removido' => false,
        ]);

        \Excel::store(new ModeloRowsExport($this->headdings, $this->rows), $this->nome_arquivo, 'disco-exportacao');

        Event::dispatch(new NotificacaoEvent([
            'user_id' => $this->usuario,
            'local' => $this->local,
        ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));
    }
}
