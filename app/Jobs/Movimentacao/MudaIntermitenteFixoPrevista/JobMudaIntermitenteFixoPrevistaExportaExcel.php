<?php

namespace App\Jobs\Movimentacao\MudaIntermitenteFixoPrevista;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MasterTag\CsvExporter;
use MasterTag\DataHora;

class JobMudaIntermitenteFixoPrevistaExportaExcel implements ShouldQueue
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
            "Área Etiqueta",
            "Colaborador",
            "Cargo Anterior",
            "Salário Anterior",
            "Cargo Novo",
            "Salário Novo",
            "Gestor Aprovação",
            "Motivos",
            "Status",
            "Quem Aprovou/Reprovou",
            "Data da Aprovação/Reprovação",
            'Observação Aprovação/Reprovação',
            'RH Aprovação',
            'Data da Aprovação RH',
            'Resposta RH',
            'OBS RH'
        ];

        $CsvExport = new CsvExporter($this->user, 'Planejamento - Movimentação - Mudança de Intermitente para Fixo', $header, $this->linhas);
        $CsvExport->export();
    }

    private function getDataRow($row): array
    {
        return [
            $row->UserCadastrou->nome,
            (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5),
            $row->CentroCusto->label ?: '',
            $row->novo_filial ? ($row->CentroCustoFilial ? $row->CentroCustoFilial->Filial->dados->razao_social : '') : '',
            $row->AreaEtiqueta ? $row->AreaEtiqueta->label : '',
            $row->Colaborador->nome,
            $row->VagaAbertaAnterior->titulo,
            $row->salario_anterior_format,
            $row->VagaAbertaNova->titulo,
            $row->novo_salario_format,
            $row->GestorAprovacao->nome,
            $row->motivos,
            $row->status_aprovacao ?: "aberto",
            $row->UserAprovacao ? $row->UserAprovacao->nome : "aguardando",
            $row->data_aprovacao ? (new DataHora($row->data_aprovacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao))->horaCompleta(), 0, 5) : '',
            $row->obs_aprovacao,
            $row->status_aprovacao_rh ? $row->RhAprovacao->nome : '',
            $row->status_aprovacao_rh ? (new DataHora())->dataHoraCompleta($row->data_aprovacao_rh) : '',
            $row->status_aprovacao_rh,
            $row->obs_rh
        ];
    }
}
