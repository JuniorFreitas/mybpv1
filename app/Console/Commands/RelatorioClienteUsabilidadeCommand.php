<?php

namespace App\Console\Commands;

use App\Services\Relatorios\ClienteUsabilidadeRelatorioService;
use Illuminate\Console\Command;

/**
 * Relatório de usabilidade + admissão por cliente (últimos N dias).
 *
 * Uso:
 *   php artisan mybp:relatorio-cliente-usabilidade 103967
 *   php artisan mybp:relatorio-cliente-usabilidade 103967 --dias=90
 *   docker compose exec mybpdp php artisan mybp:relatorio-cliente-usabilidade 103967 --dias=90
 */
class RelatorioClienteUsabilidadeCommand extends Command
{
    protected $signature = 'mybp:relatorio-cliente-usabilidade
                            {cliente_id : ID do cliente (empresa_id / clientes.id)}
                            {--dias=90 : Janela em dias (default 90)}
                            {--output= : Diretório de saída (default storage/app/relatorios)}';

    protected $description = 'Gera CSV+HTML de acessos, uso (activity_log) e admissão do cliente nos últimos N dias';

    public function handle(ClienteUsabilidadeRelatorioService $service): int
    {
        $clienteId = (int) $this->argument('cliente_id');
        $dias = (int) $this->option('dias');
        $output = $this->option('output') ?: null;

        if ($clienteId <= 0) {
            $this->error('cliente_id inválido.');

            return self::FAILURE;
        }

        $this->info("Gerando relatório cliente_id={$clienteId} · {$dias} dias...");

        try {
            $result = $service->gerar($clienteId, $dias, $output);
        } catch (\InvalidArgumentException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Falha ao gerar relatório: '.$e->getMessage());

            return self::FAILURE;
        }

        $r = $result['resumo'];
        $this->newLine();
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Cliente', ($r['razao_social'] ?? '').' ('.$r['apelido'].')'],
                ['Período', $r['periodo_de'].' → '.$r['periodo_ate']],
                ['Usuários', $r['usuarios_total']],
                ['Logins no período', $r['logins_periodo']],
                ['Com ação no log', $r['com_acao_periodo']],
                ['Ações activity_log', $r['acoes_totais']],
                ['Admissões criadas', $r['admissoes_periodo']],
                ['Admissões totais', $r['admissoes_total_empresa']],
                ['DEMITIDO (snapshot)', $r['demitidos_snapshot_total'] ?? $r['demitidos_total'] ?? 0],
                ['DEMITIDO no período', $r['demitidos_periodo'] ?? 0],
                ['Desmobilizações', $r['desmobilizacoes_periodo']],
                ['Treinamentos criados', $r['treinamentos_criados_periodo']],
            ]
        );

        $this->newLine();
        $this->info('Arquivos:');
        foreach ($result['arquivos'] as $tipo => $path) {
            $this->line("  [{$tipo}] {$path}");
        }

        $this->newLine();
        $this->comment($r['leitura']);

        return self::SUCCESS;
    }
}
