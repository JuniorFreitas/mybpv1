<?php

namespace App\Jobs\Excel;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Exports\ModeloRowsExport;
use App\Http\Controllers\Relatorios\EfetivoController;
use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\CentroCusto;
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

        $filtros = $this->filtros;

        $query = FeedbackCurriculo::has('Demissao')
            ->select([
                'id', 'curriculo_id', 'empresa_id', 'vagas_abertas_id', 'vaga_id'
            ])->filtrarPorCnpjECentroCusto($filtros)
            ->with('Admissao:id,feedback_id,area_etiqueta_id,cargo,data_admissao,salario,centro_custo_id',
                'Admissao.AreaEtiqueta',
                'Admissao.CentroCusto',
                'Curriculo:id,nome,cpf,nascimento,rg,orgao_expeditor',
                'Demissao.motivoRescisao',
                'VagaSelecionada',
                'EntrevistaDesligamento')->whereHas('Admissao', function ($q) {
                $q->where('status', Admissao::STATUS_DEMITIDO);
            })->Has('Demissao')->with('Demissao');


        if (count($filtros['selecionados']) > 0) {
            $resultado = $query->whereIn('id', $filtros['selecionados'])->get();
        } else {

            if ($filtros->filled('campoFeedback')) {
                if ($filtros->campoFeedback == "nao") {
                    $query->whereDoesntHave('EntrevistaDesligamento');
                } else {
                    $query->whereHas('EntrevistaDesligamento');
                }
            }

            $resultado = $query->get();
        }

        $cc = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);
        $resultado = collect($resultado)->transform(function ($item) use ($cc) {
            $cc_colaborador = collect($cc['centros_custos'])->collapse()->where('id', $item->admissao->centro_custo_id)->first();
            $item->admissao->emp_cnpj = null;
            $item->admissao->emp_nome_fantasia = null;
            $item->admissao->emp_centro_custo = null;
            $item->admissao->emp_tipo = null;

            if ($cc_colaborador) {
                $item->admissao->emp_cnpj = $cc_colaborador['cnpj_format'];
                $item->admissao->emp_nome_fantasia = $cc_colaborador['nome_fantasia'];
                $item->admissao->emp_centro_custo = $cc_colaborador['label'];
                $item->admissao->emp_tipo = $cc_colaborador['matriz'] ? 'Matriz' : 'Filial';
            }

            return $item;
        });

        $entrevista = [
            "entrevista_superior_imediato",
            "entrevista_motivo",
            "entrevista_trabalharia_novamente",
            "entrevista_contr_melhoria",
            "entrevista_relacao_interpessoal",
            "entrevista_recursos_fisicos",
            "entrevista_valores_normas",
            "entrevista_planejamento",
            "entrevista_sob_superior_imediato",
            "entrevista_direcao_empresa",
            "entrevista_oportunidades",
            "entrevista_salario_beneficio",
            "entrevista_atividade",
            "entrevista_comentarios",
            "entrevista_parecer_entrevistador",
            "entrevista_pode_voltar",
            "entrevista_porque_pode_voltar",
            "entrevista_quem_entrevistou",
            "entrevista_user_entrevista",
            "entrevista_data_entrevista",
            "entrevista_preenchido_por",
        ];

        $head = [
//            'ID',
            'CNPJ',
            'Nome',
            'CPF',
//            'Área',
            'Cargo',
            'Salario',
            'Data Admissão',
            'Data Demissão',
//            'Data Entrevista',
            'Centro de Custo',
        ];

        $head = array_merge($head, $entrevista);

        $rows = [];

        foreach ($resultado->toArray() as $row) {
            $data_admissao = $row['admissao'] ? (new DataHora($row['admissao']['data_admissao']))->dataCompleta() : 'NÃO INFORMADA';
            $data_desmobilizacao = $row['demissao'] ? (new DataHora($row['demissao']['data_desmobilizacao']))->dataCompleta() : 'NÃO INFORMADA';
            if (isset($row["entrevista_desligamento"])) {
                $entrevista = [
                    "superior_imediato" => $row['entrevista_desligamento']["superior_imediato"] ?? "",
                    "motivo" => $row['entrevista_desligamento']["motivo"] ?? "",
                    "trabalharia_novamente" => $row['entrevista_desligamento']["trabalharia_novamente"] ?? "",
                    "contr_melhoria" => $row['entrevista_desligamento']["contr_melhoria"] ?? "",
                    "relacao_interpessoal" => $row['entrevista_desligamento']["relacao_interpessoal"] ?? "",
                    "recursos_fisicos" => $row['entrevista_desligamento']["recursos_fisicos"] ?? "",
                    "valores_normas" => $row['entrevista_desligamento']["valores_normas"] ?? "",
                    "planejamento" => $row['entrevista_desligamento']["planejamento"] ?? "",
                    "sob_superior_imediato" => $row['entrevista_desligamento']["sob_superior_imediato"] ?? "",
                    "direcao_empresa" => $row['entrevista_desligamento']["direcao_empresa"] ?? "",
                    "oportunidades" => $row['entrevista_desligamento']["oportunidades"] ?? "",
                    "salario_beneficio" => $row['entrevista_desligamento']["salario_beneficio"] ?? "",
                    "atividade" => $row['entrevista_desligamento']["atividade"] ?? "",
                    "comentarios" => $row['entrevista_desligamento']["comentarios"] ?? "",
                    "parecer_entrevistador" => $row['entrevista_desligamento']["parecer_entrevistador"] ?? "",
                    "pode_voltar" => $row['entrevista_desligamento']["pode_voltar"] ? "Sim" : "Não",
                    "porque_pode_voltar" => $row['entrevista_desligamento']["porque_pode_voltar"] ?? "",
                    "quem_entrevistou" => $row['entrevista_desligamento']["quem_entrevistou"] ?? "",
                    "user_entrevista" => User::select('nome')->find($row['entrevista_desligamento']["user_entrevista"])->nome,
                    "data_entrevista" => $row['entrevista_desligamento']["data_entrevista"] ?? "",
                    "preenchido_por" => $row['entrevista_desligamento']["preenchido_por"] ?? "",
                ];
            } else {
                $entrevista = [
                    "superior_imediato" => "",
                    "motivo" => "",
                    "trabalharia_novamente" => "",
                    "contr_melhoria" => "",
                    "relacao_interpessoal" => "",
                    "recursos_fisicos" => "",
                    "valores_normas" => "",
                    "planejamento" => "",
                    "sob_superior_imediato" => "",
                    "direcao_empresa" => "",
                    "oportunidades" => "",
                    "salario_beneficio" => "",
                    "atividade" => "",
                    "comentarios" => "",
                    "parecer_entrevistador" => "",
                    "pode_voltar" => "",
                    "porque_pode_voltar" => "",
                    "quem_entrevistou" => "",
                    "user_entrevista" => "",
                    "data_entrevista" => "",
                    "preenchido_por" => "",
                ];
            }


            $rows[] = [
//                $row['admissao']['id'],
                $row['admissao']['emp_nome_fantasia'],
                $row['curriculo']['nome'],
                $row['curriculo']['cpf'],
//                $row['admissao']['area_etiqueta_id'] ? $row['admissao']['area_etiqueta']['label'] : '',
                $row['admissao']['cargo'],
                $row['admissao']['salario'],
                $data_admissao,
                $data_desmobilizacao,
                $row['admissao']['emp_centro_custo'] ?? "NÃO ENCONTRADO",

//                isset($row['entrevista_desligamento']) ? ((new DataHora($row['entrevista_desligamento']['data_entrevista']))->dataHoraCompleta()) : "",
                $entrevista['superior_imediato'],
                $entrevista['motivo'],
                $entrevista['trabalharia_novamente'],
                $entrevista['contr_melhoria'],
                $entrevista['relacao_interpessoal'],
                $entrevista['recursos_fisicos'],
                $entrevista['valores_normas'],
                $entrevista['planejamento'],
                $entrevista['sob_superior_imediato'],
                $entrevista['direcao_empresa'],
                $entrevista['oportunidades'],
                $entrevista['salario_beneficio'],
                $entrevista['atividade'],
                $entrevista['comentarios'],
                $entrevista['parecer_entrevistador'],
                $entrevista['pode_voltar'],
                $entrevista['porque_pode_voltar'],
                $entrevista['quem_entrevistou'],
                $entrevista['user_entrevista'],
                $entrevista['data_entrevista'],
                $entrevista['preenchido_por'],
            ];

        }

        \Excel::store(new ModeloRowsExport($head, $rows), $nameArquivo, Arquivo::DISCO_EXPORTACAO);

        Event::dispatch(new NotificacaoEvent([
            'user_id' => $this->usuario_id,
            'local' => 'Relatório de Pós-Admissão',
        ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));
    }
}
