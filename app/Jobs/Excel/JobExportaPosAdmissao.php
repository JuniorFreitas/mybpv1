<?php

namespace App\Jobs\Excel;

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
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use MasterTag\DataHora;
use PDF;


class JobExportaPosAdmissao implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $queue;

    private $usuario_id;
    private $empresa_id;
    private $filtros;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($usuario_id, $empresa_id, $filtros)
    {
        $this->usuario_id = $usuario_id;
        $this->empresa_id = $empresa_id;
        $this->filtros = $filtros;
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

        $nameArquivo = "posadmissao" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";

        Exportacao::create([
            'user_id' => $this->usuario_id,
            'arquivo' => $nameArquivo,
            'local' => 'Relatório de Pós-Admissão',
            'removido' => false,
        ]);

        auth()->loginUsingId($this->usuario_id);

        $query = FeedbackCurriculo::has('Demissao')
            ->with('Admissao:id,feedback_id,area_etiqueta_id,cargo,data_admissao,salario,centro_custo_id',
                'Admissao.AreaEtiqueta',
                'Admissao.CentroCusto',
                'Curriculo:id,nome,cpf,nascimento,rg,orgao_expeditor',
                'Demissao.motivoRescisao',
                'VagaSelecionada',
                'EntrevistaDesligamento');

        $filtros = $this->filtros;

        if(count($filtros['selecionados']) > 0){
            $resultado = $query->whereIn('id',$filtros['selecionados'])->get()->toArray();
        }else{
            if (!is_null($filtros['campoArea']) && strlen($filtros['campoArea']) > 0 && isset($filtros['campoArea'])) {
                $query->whereHas('Admissao', function ($q) use ($filtros) {
                    $q->demitidos()->whereAreaEtiquetaId($filtros['campoArea']);
                });
            }
            if (!is_null($filtros['campoCargo']) && strlen($filtros['campoCargo']) > 0 && isset($filtros['campoCargo'])) {
                $query->whereHas('Admissao', function ($q) use ($filtros) {
                    $q->demitidos()->where('cargo', 'like', '%' . $filtros['campoCargo'] . '%');
                });
            }
            if (!is_null($filtros['campoUf']) && strlen($filtros['campoUf']) > 0 && isset($filtros['campoUf'])) {
                $query->whereHas('Curriculo', function ($q) use ($filtros) {
                    $q->whereUfVaga($filtros['campoUf']);
                });
            }
            $resultado = $query->get()->toArray();
        }

        $head = [
            'ID',
            'Nome',
            'CPF',
            'Área',
            'Cargo',
            'Data Admissão',
            'Data Demissão',
            'Data Entrevista',
            'Centro de Custo',
            'Salario'
        ];

        $rows = [];

        foreach ($resultado as $row) {
            $data_admissao = $row['admissao'] ? (new DataHora($row['admissao']['data_admissao']))->dataCompleta() : 'NÃO INFORMADA';
            $data_desmobilizacao = $row['demissao'] ? (new DataHora($row['demissao']['data_desmobilizacao']))->dataCompleta() : 'NÃO INFORMADA';
            $rows[] = [
                $row['admissao']['id'],
                $row['curriculo']['nome'],
                $row['curriculo']['cpf'],
                $row['admissao']['area_etiqueta_id'] ? $row['admissao']['area_etiqueta']['label'] : '',
                $row['admissao']['cargo'],
                $data_admissao,
                $data_desmobilizacao,
                $row['data_entrevista'],
                $row['admissao']['centro_custo'] ? $row['admissao']['centro_custo']['label'] : '',
                $row['admissao']['salario'],
            ];
        }

        \Excel::store(new ModeloRowsExport($head, $rows), $nameArquivo, Arquivo::DISCO_EXPORTACAO);

        Event::dispatch(new NotificacaoEvent([
            'user_id' => $this->usuario_id,
            'local' => 'Relatório de Pós-Admissão',
        ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));
    }
}
