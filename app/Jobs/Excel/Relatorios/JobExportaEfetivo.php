<?php

namespace App\Jobs\Excel\Relatorios;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Exports\ModeloRowsExport;
use App\Http\Controllers\Relatorios\EfetivoController;
use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\EmpresaConfig;
use App\Models\Exportacao;
use App\Models\FeedbackCurriculo;
use App\Models\Feriado;
use App\Models\OcorrenciaJornada;
use App\Models\PontoEletronico;
use App\Models\Sistema;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use MasterTag\DataHora;
use PDF;


class JobExportaEfetivo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $queue;

    private $request;
    private $usuario_id;
    private $empresa_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($usuario_id, $empresa_id)
    {
        $this->usuario_id = $usuario_id;
        $this->empresa_id = $empresa_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');

        $nameArquivo = "relatorio_efetivo" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";

        Exportacao::create([
            'user_id' => $this->usuario_id,
            'arquivo' => $nameArquivo,
            'local' => 'Relatório de Efetivos',
            'removido' => false,
        ]);

        auth()->loginUsingId($this->usuario_id);

        $request = request();
        $resultado = EfetivoController::filtro($request)->get()->transform(function ($item) {
            $item->data_admissao = $item->data_admissao ?: 'NÃO INFORMADA';
            $item->salario = $item->salario ?: '0,00';
            $item->cargo = $item->cargo ?: 'NÃO INFORMADO';
            $item->tipo_admissao = $item->tipo_admissao ?: 'NÃO INFORMADA';
            $item->centro_custo_label = $item->CentroCusto ? $item->CentroCusto->label : 'NÃO INFORMADO';
            return $item;
        })->toArray();

        $head = [
            "Código",
            "Nome",
            "Centro de Custo",
            "Cargo",
            "Salário",
            "Tipo de admissão",
            "Data da Admissão",
        ];

        $rows = [];

        foreach ($resultado as $admissao) {
            $rows[] = array(
                $admissao['feedback']['curriculo_id'],
                $admissao['feedback']['curriculo']['nome'],
                $admissao['cargo'],
                $admissao['centro_custo_label'],
                $admissao['salario'],
                $admissao['tipo_admissao'],
                $admissao['data_admissao']
            );
        }

        \Excel::store(new ModeloRowsExport($head, $rows), $nameArquivo, Arquivo::DISCO_EXPORTACAO);

        Event::dispatch(new NotificacaoEvent([
            'user_id' => $this->usuario_id,
            'local' => 'Relatório de Efetivos',
        ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));
    }
}
