<?php

namespace App\Jobs\Relatorios\Ferias\Vencimento;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class JobExportarExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
//    public $delay;
    public $queue;
    public array $dados;
    public string $local;
    public string $usuario;
    public int|string $usuario_id;
    public string $nome_arquivo;
    public int $timeout = 0;


    /**
     * Create a new job instance.
     *
     * @param string|int $usuario_id
     * @param string $local
     * @param array $dados
     * @param string $nome_arquivo
     */
    public function __construct(string|int $usuario_id, string $local, array $dados, string $nome_arquivo)
    {
        $this->local = $local;
        $this->usuario_id = $usuario_id;
        $this->nome_arquivo = $nome_arquivo;

        $this->dados = $dados;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $Usuario = auth()->loginUsingId($this->usuario_id);

        $head = [[
            "Nome do colaborador",
            "Data de admissão",
            "Cargo",
//            "Função",
            "Centro de Custo",
            "Período aquisitivo",
            "Quantidade de dias de atraso",
            "Tempo atrasado",
            "Situação",
            "Data saida",
            "Data retorno",
            "Saldo",
            "Última atualização",
        ]];

        $rows = [];
//        $filtrados = json_decode($this->dados, true);

        foreach ($this->dados as $row) {
            $todos_periodos = $row['todos_periodos']->toArray();
            foreach ($todos_periodos as $p) {
                $rows[] = [
                    $row['nome'],
                    $row['data_admissao'],
                    $row['cargo'],
//                    $row['funcao'],
                    $row['centro_custo'],
                    $p['periodo_aquisitivo'],
                    $row['dias_atraso'],
                    $row['dias_atraso'] > 0 ? $row['tempo_atrasado'] : '',
                    $p['status_ferias'],
                    is_null($p['data_saida']) ? '---' : $p['data_saida'],
                    is_null($p['data_retorno']) ? '---' : $p['data_retorno'],
                    $p['total_avos'],
                    $p['ultima_atualizacao'],
                ];
            }
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
