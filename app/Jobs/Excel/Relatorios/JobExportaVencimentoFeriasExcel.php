<?php

namespace App\Jobs\Excel\Relatorios;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Exports\ModeloRowsExport;
use App\Models\Exportacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

class JobExportaVencimentoFeriasExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
//    public $delay;
    public $queue;
    public $headdings = [];
    public $rows = [];
    public $nome_arquivo;
    public $local;
    public $usuario;
    private $dados;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($usuario, $local, $dados, $nome_arquivo)
    {
        $this->nome_arquivo = $nome_arquivo;
        $this->local = $local;
        $this->usuario = $usuario;
        $this->delay = now()->addSeconds(rand(5, 10));
        $this->dados = $dados;
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

        $headdings = [
            '#',
            'Atualizado via script',
            'Nome',
            'Cargo',
            'Função',
            'Data da Admissão',
            'Centro de Custo',
            'Período Aquisitivo',
            'Quantidade de Avos',
            'Última Atualização',
        ];
        $rows = [];

        foreach ($this->dados as $row) {
            $rows[] = array(
                $row['avos_id'],
                $row['atualizado_via_script'],
                $row['nome'],
                $row['cargo'],
                $row['funcao'],
                $row['data_admissao'],
                $row['centro_custo'],
                $row['periodo_aquisitivo'],
                $row['total_avos'],
                $row['ultima_atualizacao']
            );
        }

        \Excel::store(new ModeloRowsExport($headdings, $rows), $this->nome_arquivo, 'disco-exportacao');

        Event::dispatch(new NotificacaoEvent([
            'user_id' => $this->usuario,
            'local' => $this->local,
        ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));
    }
}
