<?php

namespace App\Jobs\Movimentacao\DemissaoPrevista;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MasterTag\CsvExporter;
use MasterTag\DataHora;

class JobDemissaoPrevistaExportaExcel implements ShouldQueue
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
            "Quem Solicitou",
            "Data da Solicitação",
            "Centro de Custo",
            "Filial",
            "Colaborador",
            "Cargo",
            "Data Demissão",
            "Tipo de Aviso",
            "Gestor Aprovação",
            "Observação",
            "Status",
            "Quem Aprovou/Reprovou",
            "Data da Aprovação/Reprovação",
            'Observação Aprovação/Reprovação',
            "Status RH",
            "Quem Aprovou/Reprovou RH",
            "Data da Aprovação/Reprovação RH",
            'Observação Aprovação/Reprovação RH'
        ];

        $CsvExport = new CsvExporter($this->user, 'Planejamento - Movimentação - Demissão', $header, $this->linhas);
        $CsvExport->export();
    }

    private function getDataRow($row): array
    {
        return [
            $row->UserCadastrou->nome,
            (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5),
            $row->CentroCusto->label,
            $row->filial ? $row->CentroCustoFilial->label : '',
            $row->Colaborador->nome,
            $row->Colaborador->FeedBack->VagaAberta->Vaga->nome,
            (new DataHora($row->data_demissao))->dataCompleta(),
            $row->tipo_aviso,
            $row->GestorAprovacao->nome,
            $row->obs,
            $row->status_aprovacao ?: "aberto",
            $row->UserAprovacao ? $row->UserAprovacao->nome : "aguardando",
            $row->data_aprovacao ? (new DataHora($row->data_aprovacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao))->horaCompleta(), 0, 5) : '',
            $row->obs_aprovacao,
            $row->status_aprovacao ?: "",
            $row->RhAprovacao ? $row->RhAprovacao->nome : "",
            $row->data_aprovacao_rh ? (new DataHora($row->data_aprovacao_rh))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao_rh))->horaCompleta(), 0, 5) : '',
            $row->obs_aprovacao_rh,
        ];
    }
}
