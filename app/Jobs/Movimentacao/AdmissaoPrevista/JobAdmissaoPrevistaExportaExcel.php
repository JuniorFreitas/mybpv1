<?php

namespace App\Jobs\Movimentacao\AdmissaoPrevista;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MasterTag\CsvExporter;
use MasterTag\DataHora;

class JobAdmissaoPrevistaExportaExcel implements ShouldQueue
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
            "Cargo",
            "Data da Solicitação",
            "Centro de Custo",
            "Tipo de Contrato",
            "Data da Admissão",
            "Observação",
            "Salário",
            "Gestor Aprovação",
            "Quem Aprovou/Reprovou",
            "Data da Aprovação/Reprovação",
            "Status"
        ];

        $CsvExport = new CsvExporter($this->user, 'Planejamento - Movimentação - Admissão', $header, $this->linhas);
        $CsvExport->export();
    }

    private function getDataRow($row): array
    {
        return [
            $row->UserCadastrou->nome,
            $row->Cargo->nome,
            (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5),
            $row->CentroCusto->label,
            $row->tipo_contrato,
            (new DataHora($row->data_admissao))->dataCompleta(),
            $row->obs,
            $row->salario_format,
            $row->GestorAprovacao->nome,
            $row->UserAprovacao ? $row->UserAprovacao->nome : "aguardando",
            $row->data_aprovacao ? (new DataHora($row->data_aprovacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao))->horaCompleta(), 0, 5) : '',
            $row->status_aprovacao ? $row->status_aprovacao : "aberto",
        ];
    }
}
