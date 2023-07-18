<?php

namespace App\Jobs\Relatorios\VencimentoAso;

use App\Http\Controllers\Relatorios\VencimentoAsosController;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MasterTag\DataHora;

class JobExportarExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
//    public $delay;
    public $queue;
    public $dados;
    public $local;
    public $usuario;
    public $usuario_id;
    public $nome_arquivo;
    public $timeout = 0;


    /**
     * @param $usuario_id
     * @param $local
     * @param $dados
     * @param $nome_arquivo
     */
    public function __construct($usuario_id, $local, $dados, $nome_arquivo)
    {
        $this->local = $local;
        $this->usuario_id = $usuario_id;
        $this->nome_arquivo = $nome_arquivo;
        $empresa_id = User::find($usuario_id)->empresa_id;
        $Usuario = auth()->loginUsingId($this->usuario_id);

        $this->dados = VencimentoAsosController::filtro($empresa_id, $dados);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $filtrados = $this->dados;

        $Usuario = auth()->loginUsingId($this->usuario_id);

        $head = [[
            "Nome",
            "Cargo",
            "Data da Admissão",
            "Tipo do Exame",
            "Data do Aso",
            "Vencimento ASO",
            "Dias",
            "Status"
        ]];

        $rows = [];

        foreach ($filtrados as $row) {

            $rows[] = array(
                $row['colaborador'],
                $row['cargo'],
                $row['data_admissao'],
                $row['exame_tipo'],
                $row['data_admissao'],
                $row['data_vencimento'],
                abs($row['dias_vencer']),
                $row['dias_vencer'] < 0 ? 'Vencido' : 'Vencendo'
            );
        }

        $array = [
            'usuario' => $Usuario,
            'local' => $this->local,
            'dados' => array_merge($head, $rows),
            'arquivo' => $this->nome_arquivo
        ];

        \Artisan::call("mybp:exportarExcel", $array);
    }
}
