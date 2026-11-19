<?php

namespace App\Jobs\Relatorios\Ferias\Vencimento;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Exports\ModeloRowsExport;
use App\Models\Exportacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

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

        $head = [
            "Nome do colaborador",
            "Data de admissão",
            "Cargo",
            "Centro de Custo",
            "Período aquisitivo",
            "Quantidade de dias de atraso",
            "Tempo atrasado",
            "Situação",
            "Data saida",
            "Data retorno",
            "Saldo",
            "Última atualização",
        ];

        $rows = [];
        foreach ($this->dados as $row) {
            $todos_periodos = is_object($row['todos_periodos'] ?? null) && method_exists($row['todos_periodos'], 'toArray')
                ? $row['todos_periodos']->toArray()
                : (array) ($row['todos_periodos'] ?? []);

            foreach ($todos_periodos as $p) {
                $rows[] = [
                    $row['nome'] ?? 'Não informado',
                    $row['data_admissao'] ?? 'Não informado',
                    $row['cargo'] ?? 'Não informado',
                    $row['centro_custo'] ?? 'Não informado',
                    $p['periodo_aquisitivo'] ?? 'Não informado',
                    $row['dias_atraso'] ?? 0,
                    ($row['dias_atraso'] ?? 0) > 0 ? ($row['tempo_atrasado'] ?? '') : '',
                    $p['status_ferias'] ?? 'Não informado',
                    is_null($p['data_saida'] ?? null) ? '---' : $p['data_saida'],
                    is_null($p['data_retorno'] ?? null) ? '---' : $p['data_retorno'],
                    $p['total_avos'] ?? 'Não informado',
                    $p['ultima_atualizacao'] ?? 'Não informado',
                ];
            }
        }

        $userId = is_object($Usuario) ? (int) $Usuario->id : (int) $this->usuario_id;

        Exportacao::create([
            'user_id' => $userId,
            'arquivo' => $this->nome_arquivo,
            'local' => $this->local,
            'removido' => false,
        ]);

        \Excel::store(new ModeloRowsExport($head, $rows), $this->nome_arquivo, 'disco-exportacao');

        Event::dispatch(new NotificacaoEvent([
            'user_id' => $userId,
            'local' => $this->local,
        ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));
    }
}
