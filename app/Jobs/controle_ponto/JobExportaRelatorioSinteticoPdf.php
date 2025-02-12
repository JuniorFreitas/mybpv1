<?php

namespace App\Jobs\controle_ponto;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Models\EmpresaConfig;
use App\Models\Exportacao;
use App\Models\OcorrenciaJornada;
use App\Models\PontoEletronico;
use App\Models\Sistema;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use MasterTag\DataHora;
use PDF;


class JobExportaRelatorioSinteticoPdf implements ShouldQueue
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
    public function __construct($request, $usuario_id, $empresa_id)
    {
        $this->request = $request;
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

        $nome_arquivo = "folhadepontomanual_" . (new DataHora())->nomeUnico() . ".pdf";

        $pdf = PDF::setOptions([
            'logOutputFile' => storage_path('logs/log.htm'),
            'tempDir' => storage_path('logs/')
        ])->loadView('pdf.controle-ponto.ponto-manual.manual', [
                'dados' => $this->geraDados()]
        )
            ->setPaper('A4', 'landscape');

        \Storage::disk('disco-exportacao')->put($nome_arquivo, $pdf->output());

        Exportacao::create([
            'user_id' => $this->usuario_id,
            'arquivo' => $nome_arquivo,
            'local' => 'Folha de Ponto Manual PDF',
            'removido' => false,
        ]);

        Event::dispatch(new NotificacaoEvent([
            'user_id' => $this->usuario_id,
            'local' => 'Folha de Ponto Manual PDF',
        ], NotificacaoEvent::EXPORTACAO_PDF, NotificacaoEvent::TIPO_PADRAO));
    }

    public function geraDados()
    {
        $request = $this->request;
        $usuario_id = $this->usuario_id;
        $empresa_id = $this->empresa_id;

        $dia_inicial_frequencia = EmpresaConfig::whereEmpresaId($empresa_id)->first(['dia_nova_frequencia'])->dia_nova_frequencia;

        $correlation_id = "{$empresa_id}_{$usuario_id}_{$dia_inicial_frequencia}_" . date('m_Y') . "_{$request->status}_{$request->centro_custo_filial_id}";

        $dataInicialMes = new DataHora("{$dia_inicial_frequencia}/" . date('m/Y') . " 00:00:00");
        $dataFimMes = clone $dataInicialMes;
        $dataFimMes->addDia(30);

        $request->intervalo = "{$dataInicialMes->dataCompleta()} até {$dataFimMes->dataCompleta()}";
        $intervalo = explode(" até ", $request->intervalo);
        $inicio = new DataHora($intervalo[0] . " 00:00:00");
        $fim = new DataHora($intervalo[1] . " 23:59:59");

        $dadosDaEmpresa = $request->centro_custo_filial_id ? Sistema::getFilial($empresa_id, $request->centro_custo_filial_id) : Sistema::getEmpresa($empresa_id);

        $funcionarios = PontoEletronico::select('funcionario_id')
            ->whereBetween('created_at', [$inicio->dataHoraInsert(), $fim->dataHoraInsert()])
            ->where('empresa_id', $empresa_id)
            ->whereHas('Funcionario.Feedback', function ($q) use ($request) {
                $request->status == 'admitidos' ? $q->admitidos() : $q->demitidos();
                $q->whereHas('Admissao', function ($q) use ($request) {
                    if ($request->centro_custo_filial_id) {
                        $q->where('centro_custo_filial_id', $request->centro_custo_filial_id);
                    }
                });
            })
            ->groupBy('funcionario_id')
            ->havingRaw('COUNT(funcionario_id) > 1')
            ->pluck('funcionario_id');

        $ll = [];

        foreach ($funcionarios as $funcionario_id) {
            $consulta = PontoEletronico::whereBetween('created_at', [$inicio->dataHoraInsert(), $fim->dataHoraInsert()])
                ->whereFuncionarioId($funcionario_id)
                ->orderBy('created_at');

            //Total horas normais
            $totalFaltas = clone $consulta;
            $totalDiasTrabalhados = clone $consulta;
            $totalHorasNormais = clone $consulta;
            $totalHorasNoturnas = clone $consulta;
            $totalHorasExtra = clone $consulta;
            $totalHorasNegativas = clone $consulta;

            $totalHorasNormais = $totalHorasNormais->whereDoesntHave('PeriodosEmAberto')->sum('duracao_normal');
            $totalHorasNoturnas = $totalHorasNoturnas->whereDoesntHave('PeriodosEmAberto')->sum('duracao_noturna');
            $totalHorasExtra = $totalHorasExtra->whereDoesntHave('PeriodosEmAberto')->where('duracao_extra', '>', 0)->sum('duracao_extra');
            $totalHorasNegativas = abs($totalHorasNegativas->whereDoesntHave('PeriodosEmAberto')->where('duracao_extra', '<', 0)->sum('duracao_extra'));

            $dadosDoFuncionario = Sistema::getColaboradorDados($funcionario_id, $empresa_id, $request->status == 'admitidos');

            $ll[] = [
                'funcionario_id' => (int)$funcionario_id,
                'empresa_id' => (int)$empresa_id,
                'funcionario' => $dadosDoFuncionario,
                'total_faltas' => $totalFaltas->whereOcorrenciaId(OcorrenciaJornada::FALTA)->count(),
                'total_dias_trabalhados' => $totalDiasTrabalhados->whereOcorrenciaId(OcorrenciaJornada::DIA_TRABALHADO)->count(),
                'total_horas_normais' => PontoEletronico::formataTempo($totalHorasNormais),
                'total_horas_noturnas' => PontoEletronico::formataTempo($totalHorasNoturnas),
                'total_horas_extra' => PontoEletronico::formataTempo($totalHorasExtra),
                'total_horas_negativas' => PontoEletronico::formataTempo($totalHorasNegativas),
                'saldo_horas' => PontoEletronico::formataTempo(($totalHorasExtra + $totalHorasNoturnas) - $totalHorasNegativas),
            ];
        }

        return [
            'periodo' => $inicio->dataCompleta() . " até " . $fim->dataCompleta(),
            'dados_empresa' => $dadosDaEmpresa,
            'dados_ponto' => collect($ll)->sortBy('dados_funcionario.nome')->values()->all(),
            'total_funcionarios' => count($ll),
            'correlation_id' => $correlation_id,
        ];
    }
}
