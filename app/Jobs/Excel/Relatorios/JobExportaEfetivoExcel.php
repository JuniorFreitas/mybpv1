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

class JobExportaEfetivoExcel implements ShouldQueue
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
            'Aprovado via automação',
            'Status das Férias',
            'Nome',
            'Cargo',
            'Função',
            'Data da Admissão',
            'Gestor aprovação',
            'Gestor que aprovou/reprovou',
            'Gestor status',
            'Gestor data da resposta',
            'Centro de Custo',
            'Quantidade de dias',
            'Dias de saldo',
            'Tem faltas',
            'Quantidade de Faltas',
            'Período Aquisitivo',
            'Data da saída',
            'Data do retorno',
            'Última data',
            'Solicitante',
            'Usuario RH',
            'Resposta do RH',
            'Data da aprovação do RH',
        ];
        $rows = [];

        foreach ($this->dados as $row) {
            $rows[] = array(
                $row['ferias_id'],
                $row['aprovado_via_script'],
                $row['status'],
                $row['nome'],
                $row['cargo'],
                $row['funcao'],
                $row['data_admissao'],
                $row['gestor'],
                $row['quem_aprovou'],
                $row['status_aprovacao'],
                $row['data_aprovacao'],
                $row['centro_custo'],
                $row['qnt_dias'],
                $row['dias_saldo'],
                $row['tem_faltas'],
                $row['qnt_faltas'],
                $row['periodo_aquisitivo'],
                $row['data_saida'],
                $row['data_retorno'],
                $row['ultima_data'],
                $row['usuario_cadastrou'],
                $row['rh'],
                $row['resposta_rh'],
                $row['data_aprovacao_rh'],
            );
        }

        \Excel::store(new ModeloRowsExport($headdings, $rows), $this->nome_arquivo, 'disco-exportacao');

        Event::dispatch(new NotificacaoEvent([
            'user_id' => $this->usuario,
            'local' => $this->local,
        ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));
    }
}
