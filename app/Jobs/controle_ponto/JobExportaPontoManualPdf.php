<?php

namespace App\Jobs\controle_ponto;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Models\Exportacao;
use App\Models\FeedbackCurriculo;
use App\Models\Feriado;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use MasterTag\DataHora;
use PDF;


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
        $resultado = FeedbackCurriculo::select(['id', 'curriculo_id', 'empresa_id', 'vagas_abertas_id'])
            ->whereHas('Admissao', function ($query) {
                $query->where('status', 'admitido');
            })
            ->with(
                'Admissao:id,feedback_id,data_admissao,cargo,funcao,pis,centro_custo_id,matricula',
                'Admissao.DadosAdmissoes',
                'Curriculo:id,nome,nascimento,rg,orgao_expeditor',
                'Empresa:id,cnpj,razao_social,nome_fantasia,cep,logradouro,numero,complemento,bairro,municipio,uf,contato',
                'Empresa.Logo:id,nome,layout,disco,imagem,file,thumb',
                'Admissao.CentroCusto:id,label'
            )->whereIn('id', $request['selecionados'])->get()->map(function ($item) {
                $ctps_numero = $item->Admissao->DadosAdmissoes ? $item->Admissao->DadosAdmissoes->ctps_numero : '';
                $ctps_serie = $item->Admissao->DadosAdmissoes ? $item->Admissao->DadosAdmissoes->ctps_serie : '';
                return [
                    'nome' => $item->Curriculo->nome,
                    'cargo' => $item->Admissao->cargo,
                    'centro_custo' => $item->Admissao->CentroCusto ? $item->Admissao->CentroCusto->label : 'Não Informado',
                    'data_admissao' => $item->Admissao->data_admissao,
                    'funcao' => $item->Admissao->funcao,
                    'pis' => $item->Admissao->pis,
                    'ctps' => $ctps_numero . '-' . $ctps_serie,
                    'empresa' => $item->Empresa,
                    'matricula' => $item->Admissao->matricula
                ];
            })->toArray();

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

        $dados = [
            'periodo' => DataHora::dataFormatada($request['data_inicio']) . ' à ' . DataHora::dataFormatada($request['data_fim']),
            'calendario' => $calendario,
            'selecionados' => $resultado,
            'repouso' => $repouso,
            'dias_normais' => $dias_normais,
            'empresa' => $resultado[0]['empresa'],
            'quem_gerou' => $request['quem_gerou'],
        ];

        $this->model = $dados;

//        $this->model = $model;
//        $this->nome_arquivo = $nome_arquivo;
//        $this->view = $view;
//        $this->local = $local;
//        $this->usuario = $usuario;
//        $this->delay = now()->addSeconds(rand(1, 1));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $nome_arquivo = "relatorio_aniversariantes_" . (new DataHora())->nomeUnico() . ".pdf";

        $pdf = PDF::setOptions([
            'logOutputFile' => storage_path('logs/log.htm'),
            'tempDir' => storage_path('logs/')
        ])->loadView('pdf.controle-ponto.ponto-manual.manual', [
            'dados' => $this->model]
        )
            ->setPaper('A4', 'landscape');

        \Storage::disk('disco-exportacao')->put($nome_arquivo, $pdf->output());

        Exportacao::create([
            'user_id' => $this->usuario->id,
            'arquivo' => $nome_arquivo,
            'local' => 'Folha de Ponto Manual PDF',
            'removido' => false,
        ]);

        Event::dispatch(new NotificacaoEvent([
            'user_id' => $this->usuario->id,
            'local' => 'Folha de Ponto Manual PDF',
        ], NotificacaoEvent::EXPORTACAO_PDF, NotificacaoEvent::TIPO_PADRAO));
    }
}
