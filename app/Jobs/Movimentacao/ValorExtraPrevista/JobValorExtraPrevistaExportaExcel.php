<?php

namespace App\Jobs\Movimentacao\ValorExtraPrevista;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MasterTag\CsvExporter;
use MasterTag\DataHora;

class JobValorExtraPrevistaExportaExcel implements ShouldQueue
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
            "Quem solicitou",
            "Data da Solicitação",
            "CENTRO DE CUSTO",
            "COLABORADOR",
            "Cargo",
            "TIPO",
            "PERÍODO EM DIAS",
            "GESTOR APROVAÇÃO",
            "OBSERVAÇÃO",
            "STATUS",
            "QUEM APROVOU/REPROVOU",
            "DATA DA APROVAÇÃO/REPROVAÇÃO",
            'OBSERVAÇÃO APROVAÇÃO/REPROVAÇÃO',
        ];

        $CsvExport = new CsvExporter($this->user, 'Planejamento - Movimentação - Liderança de Pessoal e Valor Extra', $header, $this->linhas);
        $CsvExport->export();
    }

    private function getDataRow($row): array
    {
        return [
            $row->UserCadastrou->nome,
            (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5),
            $row->CentroCusto->label,
            $row->Colaborador->nome,
            $row->Colaborador->FeedBack->VagaSelecionada->nome,
            $row->tipo,
            $row->periodo_dias,
            $row->GestorAprovacao->nome,
            $row->obs,
            $row->status_aprovacao ? $row->status_aprovacao : "aberto",
            $row->QuemAprovou ? $row->QuemAprovou->nome : "aguardando",
            $row->data_aprovacao ? (new DataHora($row->data_aprovacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao))->horaCompleta(), 0, 5) : '',
            $row->obs_aprovacao,
        ];
    }
}
