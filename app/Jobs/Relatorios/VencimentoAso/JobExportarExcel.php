<?php

namespace App\Jobs\Relatorios\VencimentoAso;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Exports\ModeloRowsExport;
use App\Models\Exportacao;
use App\Models\User;
use App\Services\Relatorios\AsoVencimentoRelatorioService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use MasterTag\DataHora;

/**
 * @deprecated Preferir VencimentoAsosController::exportExcel + JobExportaExcel.
 * Mantido para chamadas legadas; usa a regra única do service (padrão CIH / Maatwebsite).
 */
class JobExportarExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $queue;
    public $dados;
    public $local;
    public $usuario_id;
    public $nome_arquivo;
    public $timeout = 0;

    public function __construct($usuario_id, $local, $dados, $nome_arquivo)
    {
        $this->local = $local;
        $this->usuario_id = $usuario_id;
        $this->nome_arquivo = $nome_arquivo;
        $this->dados = is_array($dados) ? $dados : [];
    }

    public function handle(): void
    {
        Auth::loginUsingId($this->usuario_id);
        $user = User::find($this->usuario_id);
        if (!$user) {
            throw new \RuntimeException("Usuário {$this->usuario_id} não encontrado");
        }

        $service = app(AsoVencimentoRelatorioService::class);
        $request = Request::create('/', 'POST', $this->dados);
        $resultado = $service->listarParaTela($user, $request);

        $head = [
            'Nome',
            'Cargo',
            'CNPJ da Empresa',
            'Empresa',
            'Centro de Custo',
            'Data da Admissão',
            'Tipo do Exame',
            'Data do Aso',
            'Vencimento ASO',
            'Dias',
            'Status',
        ];

        $rows = [];
        foreach ($resultado['dados'] as $row) {
            $dias = (int) ($row['dias_vencer'] ?? 0);
            $rows[] = [
                $row['colaborador'] ?? 'Não informado',
                $row['cargo'] ?? 'Não informado',
                $row['emp_cnpj'] ?? 'Não informado',
                $row['emp_nome_fantasia'] ?? 'Não informado',
                $row['emp_centro_custo'] ?? 'Não informado',
                $row['data_admissao'] ?? 'Não informado',
                $row['exame_tipo'] ?? 'Não informado',
                $this->formatarData($row['data_aso'] ?? null) ?: 'Não informado',
                $this->formatarData($row['data_vencimento'] ?? null) ?: 'Não informado',
                $dias,
                $dias < 0 ? 'VENCIDO' : 'A VENCER',
            ];
        }

        Exportacao::create([
            'user_id' => $user->id,
            'arquivo' => $this->nome_arquivo,
            'local' => $this->local,
            'removido' => false,
        ]);

        \Excel::store(new ModeloRowsExport($head, $rows), $this->nome_arquivo, 'disco-exportacao');

        Event::dispatch(new NotificacaoEvent([
            'user_id' => $user->id,
            'local' => $this->local,
        ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));
    }

    private function formatarData($data): string
    {
        if (!$data) {
            return '';
        }
        if (is_string($data) && str_contains($data, '/')) {
            return $data;
        }
        try {
            return (new DataHora($data))->dataCompleta();
        } catch (\Throwable $e) {
            return (string) $data;
        }
    }
}
