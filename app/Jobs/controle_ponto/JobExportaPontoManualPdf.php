<?php

namespace App\Jobs\controle_ponto;

use App\Models\Feriado;
use App\Models\Sistema;
use App\Models\User;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MasterTag\DataHora;


class JobExportaPontoManualPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $queue;

    public $usuario;
    public $model;
    public $timeout = 600;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($usuario, $request)
    {
        $this->usuario = User::find($request['usuario_id']);
        $resultado = DB::table('feedback_curriculos AS fc')
            ->join('curriculos AS c', 'fc.curriculo_id', '=', 'c.id')
            ->join('admissoes AS a', function ($join) {
                $join->on('fc.id', '=', 'a.feedback_id')
                    ->where('a.status', '=', 'admitido')
                    ->whereNull('a.deleted_at');
            })
            ->join('mybp.centro_custos AS cc', 'a.centro_custo_id', '=', 'cc.id')
            ->leftJoin('mybp.centro_custo_filials AS ccf', 'cc.id', '=', 'ccf.centro_custo_id')
            ->leftJoin('mybp.cliente_filials AS cf', 'ccf.cliente_filial_id', '=', 'cf.id')
            ->leftJoin('mybp.dados_admissaos AS da2', 'a.id', '=', 'da2.admissao_id')
            ->whereIn('fc.id', $request['selecionados'])  // Filtra pelos IDs desejados
            ->whereNull('fc.deleted_at')
            ->where('fc.empresa_id', $this->usuario->empresa_id)
            ->select(
                'fc.id',
                DB::raw("CONCAT(c.nome, '') AS nome"),
                'c.nome AS nome_sem_status',
                DB::raw("DATE_FORMAT(a.data_admissao, '%d/%m/%Y') AS data_admissao"),
                'a.cargo',
                'a.matricula',
                'a.funcao',
                'a.pis',
                DB::raw("CONCAT(da2.ctps_numero, '-', da2.ctps_serie) AS ctps"),
                'a.filial',
                'a.centro_custo_filial_id',
                'cc.label AS centro_custo_label',
                'cf.dados AS centro_custo_dados',
                'a.centro_custo_filial_id',
                DB::raw('NULL AS data_desmobilizacao'),
                DB::raw('NULL AS dias')
            )
            ->orderBy('nome', 'ASC')->get()
            ->transform(function ($item) {
                $item->empresa = Sistema::getEmpresaFilialMatriz($item->centro_custo_filial_id, $this->usuario->empresa_id);
                return $item;
            })
            ->toArray();


        $dataInicio = new DataHora($request['data_inicio']);
        $dataFim = new DataHora($request['data_fim']);

        $calendario = [];
        $qntDias = DataHora::diferencaDias($dataInicio->dataInsert(), $dataFim->dataInsert());

        $dataInicio->subtrairDia(1);

        $repouso = collect($request['dias'])->filter(function ($item) {
            return $item['repouso'];
        })->map(function ($item) {
            return $item['label'];
        })->toArray();

        $dias_normais = collect($request['dias'])->filter(function ($item) {
            return !$item['repouso'];
        });

        foreach (range(0, $qntDias) as $d) {
            $dia = $dataInicio->addDia(1);
            $calendario[] = [
                'feriado' => (bool)Feriado::where('data', (new DataHora($dia))->dataInsert())->select(['id'])->where('ativo', true)->first(),
                'dia' => substr($dia, 0, 2),
                'diaExt' => substr((new DataHora($dia))->diaSemanaExtM(), 0, (new DataHora($dia))->diaSemanaExtM() == 'Sábado' ? 4 : 3),
                'repouso' => in_array((new DataHora($dia))->diaSemanaExtM(), $repouso),
                'entrada' => isset($dias_normais[(new DataHora($dia))->diaSemanaExtCarac()]) ? $dias_normais[(new DataHora($dia))->diaSemanaExtCarac()]['entrada'] : '',
                'intervalo_almoco' => isset($dias_normais[(new DataHora($dia))->diaSemanaExtCarac()]) ? $dias_normais[(new DataHora($dia))->diaSemanaExtCarac()]['intervalo_almoco'] : '',
                'fim_intervalo_almoco' => isset($dias_normais[(new DataHora($dia))->diaSemanaExtCarac()]) ? $dias_normais[(new DataHora($dia))->diaSemanaExtCarac()]['fim_intervalo_almoco'] : '',
                'saida' => isset($dias_normais[(new DataHora($dia))->diaSemanaExtCarac()]) ? $dias_normais[(new DataHora($dia))->diaSemanaExtCarac()]['saida'] : '',
            ];
        }

        $labeldia = '';

        foreach ($dias_normais as $dia) {
            $labeldia .= mb_strtoupper(substr($dia['label'], 0, $dia['label'] == 'Sábado' ? 4 : 3)) . ' - ' . $dia['entrada'] . ' às ' . $dia['saida'] . ' | ';
        }

        $dados = [
            'periodo' => DataHora::dataFormatada($request['data_inicio']) . ' à ' . DataHora::dataFormatada($request['data_fim']),
            'calendario' => $calendario,
            'selecionados' => $resultado,
            'repouso' => $repouso,
            'dias_normais' => $dias_normais,
            'quem_gerou' => $request['quem_gerou'],
            'labeldia' => substr($labeldia, 0, strlen($labeldia) - 3),
            'data_geracao' => (new DataHora())->dataHoraCompleta(),
            'nome_arquivo' => "folhadepontomanual_" . $this->usuario->empresa_id . "_" . (new DataHora())->nomeUnico() . ".pdf"
        ];

        cache()->remember('getfolha_manual', now()->addMinutes(20), function () use ($dados) {
            return $dados;
        });
//        $this->model = json_decode(json_encode($dados), true);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

//        ini_set('memory_limit', '-1');
//        ini_set('max_execution_time', '-1');
//
//        $nome_arquivo = "folhadepontomanual_" . (new DataHora())->nomeUnico() . ".pdf";
//
//        $pdf = PDF::setOptions([
//            'logOutputFile' => storage_path('logs/log.htm'),
//            'tempDir' => storage_path('logs/')
//        ])->loadView('pdf.controle-ponto.ponto-manual.manual', [
//                'dados' => $this->model]
//        )
//            ->setPaper('A4', 'landscape');
//
//        \Storage::disk('disco-exportacao')->put($nome_arquivo, $pdf->output());
//
//        Exportacao::create([
//            'user_id' => $this->usuario->id,
//            'arquivo' => $nome_arquivo,
//            'local' => 'Folha de Ponto Manual PDF',
//            'removido' => false,
//        ]);
//
//        Event::dispatch(new NotificacaoEvent([
//            'user_id' => $this->usuario->id,
//            'local' => 'Folha de Ponto Manual PDF',
//        ], NotificacaoEvent::EXPORTACAO_PDF, NotificacaoEvent::TIPO_PADRAO));
    }
}
