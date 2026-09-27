<?php

namespace App\Jobs\Relatorios\VencimentoAso;

use App\Models\User;
use App\Services\Relatorios\AsoVencimentoRelatorioService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use MasterTag\DataHora;

/**
 * @deprecated Preferir VencimentoAsosController::exportExcel + JobExportaExcel.
 * Mantido para chamadas legadas; usa a regra única do service.
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

        $head = [[
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
        ]];

        $rows = [];
        foreach ($resultado['dados'] as $row) {
            $dias = (int) ($row['dias_vencer'] ?? 0);
            $rows[] = [
                $row['colaborador'] ?? '',
                $row['cargo'] ?? '',
                $row['emp_cnpj'] ?? '',
                $row['emp_nome_fantasia'] ?? '',
                $row['emp_centro_custo'] ?? '',
                $row['data_admissao'] ?? '',
                $row['exame_tipo'] ?? '',
                $this->formatarData($row['data_aso'] ?? null),
                $this->formatarData($row['data_vencimento'] ?? null),
                $dias,
                $dias < 0 ? 'VENCIDO' : 'A VENCER',
            ];
        }

        \Artisan::call('mybp:exportarExcel', [
            'usuario' => $user,
            'local' => $this->local,
            'dados' => array_merge($head, $rows),
            'arquivo' => $this->nome_arquivo,
        ]);
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
