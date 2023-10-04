<?php

namespace App\Jobs\Movimentacao\DemissaoPrevista;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MasterTag\CsvExporter;

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
            "Data Admissão",
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
            $row->solicitante_nome,
            $row->data_solicitacao,
            $row->centro_custo,
            $row->filial > 0 ? $row->centro_custo_filial_id : '',
            $row->colaborador_nome,
            $row->data_admissao,
            $row->cargo,
            $row->data_demissao,
            $row->tipo_aviso,
            $row->gestor_nome,
            $row->obs,
            $row->status_aprovacao ?: "aberto",
            $row->UserAprovacao->nome ?? "aguardando",
            $row->data_aprovacao ?? '',
            $row->obs,
            $row->status_aprovacao ?: "",
            $row->rh_aprovacao_nome ?? "",
            $row->data_aprovacao_rh ?? '',
            $row->obs_rh,
        ];
    }
}
