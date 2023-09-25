<?php

namespace App\Jobs\Movimentacao\FeriasPrevista;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MasterTag\CsvExporter;
use MasterTag\DataHora;

class JobFeriasPrevistaExportaExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 0;

    public User $user;
    private array $linhas = [];

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
            'Cargo',
            'Data da Admissão',
            'Centro de Custo',
            'Período Aquisitivo',
            'Última Data',
            'Quantidade de Faltas',
            'Data Saída',
            'Data Retorno',
            'Quantidade de Dias',
            'Saldo de Dias',
            'Abono Pecuniário',
            'Adiantamento Décimo Terceiro',
            'Quem Cadastrou',
            'Gestor Indicado',
            'Gestor Aprovação',
            'Data da Aprovação',
            'Status',
            'RH Aprovação',
            'Data da Aprovação RH',
            'Resposta RH',
        ];

        $CsvExport = new CsvExporter($this->user, 'Planejamento - Movimentação - Férias', $header, $this->linhas);
        $CsvExport->export();
    }

    private function getDataRow($row): array
    {
        return [
            $row->Admissao->Feedback->Curriculo->nome,
            $row->Admissao->cargo,
            $row->Admissao->data_admissao,
            $row->Admissao->CentroCusto ? $row->Admissao->CentroCusto->label : 'Não Informado',
            $row->PeriodoAquisitivo->label,
            $row->ultima_data,
            (string)$row->qnt_faltas,
            $row->data_saida,
            $row->data_retorno,
            (string)$row->qnt_dias,
            (string)$row->dias_saldo,
            $row->abono_pecuniario ? 'Sim' : 'Não',
            $row->adiantamento_decimo_terceiro ? 'Sim' : 'Não',
            $row->Solicitante->nome,
            $row->Gestor->nome,
            $row->GestorAprovacao ? $row->GestorAprovacao->nome : '',
            $row->status_aprovacao_gestor ? $row->data_aprovacao_gestor : '',
            $row->status_aprovacao_gestor,
            $row->status_aprovacao_rh ? $row->RhAprovacao->nome : '',
            $row->status_aprovacao_rh ? (new DataHora())->dataHoraCompleta($row->data_aprovacao_rh) : '',
            $row->status_aprovacao_rh,
        ];
    }
}
