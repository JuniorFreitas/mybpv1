<?php

namespace App\Jobs\Movimentacao\MudaCargoPrevista;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MasterTag\CsvExporter;
use MasterTag\DataHora;

class JobMudaCargoPrevistaExportaExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 0;

    public $user;
    private $linhas = [];

    public function __construct(User $user, $collection)
    {
        $this->user = $user;
        $collection->chunk(100, function ($rows) {
            foreach ($rows as $row) {
                $this->linhas[] = $this->getDataRow($row);
            }
        });
    }

    public function handle()
    {
        $header = [
            'Nome',
            'Centro de Custo Atual',
            'Filial Atual',
            'Centro de Custo Novo',
            'Filial Nova',
            'Cargo Atual',
            'Cargo Novo',
            'Função Atual',
            'Função Nova',
            'Salário Atual',
            'Salário Novo',
            'Solicitante',
            'Data Solicitação',
            'OBS Solicitante',
            'Gestor',
            'Gestor Aprovação',
            'Data da Aprovação',
            'Status',
            'OBS Gestor',
            'RH Aprovação',
            'Data da Aprovação RH',
            'Resposta RH',
            'OBS RH'
        ];

        $CsvExport = new CsvExporter($this->user, 'Planejamento - Movimentação - Mudança de Cargo', $header, $this->linhas);
        $CsvExport->export();
    }

    private function getDataRow($row): array
    {
        return [
            $row->Admissao->Feedback->Curriculo->nome,
            $row->CentroCustoAnterior->label ?? '',
            $row->anterior_filial ? $row->CentroCustoFilialAnterior->label : '',
            $row->mantem_centro_custo ? '' : $row->CentroCustoNovo->label,
            $row->novo_filial ? $row->CentroCustoFilialNovo->label : '',
            $row->VagaAbertaAnterior->Vaga->nome ?? '',
            $row->mantem_cargo ? '' : $row->VagaAbertaNova->Vaga->nome ?? '',
            $row->anterior_funcao,
            $row->mantem_funcao ? '' : $row->nova_funcao,
            $row->anterior_salario,
            $row->mantem_salario ? '' : $row->novo_salario,
            $row->Solicitante->nome,
            $row->data_solicitacao,
            $row->obs_solicitante,
            $row->Gestor->nome,
            $row->GestorAprovacao ? $row->GestorAprovacao->nome : '',
            $row->status_aprovacao_gestor ? $row->data_aprovacao_gestor : '',
            $row->status_aprovacao_gestor,
            $row->obs_gestor_aprovacao,
            $row->status_aprovacao_rh ? $row->RhAprovacao->nome : '',
            $row->status_aprovacao_rh ? (new DataHora())->dataHoraCompleta($row->data_aprovacao_rh) : '',
            $row->status_aprovacao_rh,
            $row->obs_rh
        ];
    }
}
