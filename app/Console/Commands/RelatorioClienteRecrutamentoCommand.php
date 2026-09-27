<?php

namespace App\Console\Commands;

use App\Services\Relatorios\ClienteRecrutamentoRelatorioService;
use Illuminate\Console\Command;

/**
 * Relatório de recrutamento: currículos, vagas, admitidos, status e demitidos (Matriz/Filial).
 *
 * Uso:
 *   php artisan mybp:relatorio-cliente-recrutamento 63122
 *   php artisan mybp:relatorio-cliente-recrutamento 63122 --dias=90
 */
class RelatorioClienteRecrutamentoCommand extends Command
{
    protected $signature = 'mybp:relatorio-cliente-recrutamento
                            {cliente_id : ID do cliente (empresa_id / clientes.id)}
                            {--dias=90 : Janela em dias (default 90)}
                            {--output= : Diretório de saída (default storage/app/relatorios)}';

    protected $description = 'Gera CSV+HTML de currículos, vagas, admitidos/demitidos e status por Matriz/Filial';

    public function handle(ClienteRecrutamentoRelatorioService $service): int
    {
        $clienteId = (int) $this->argument('cliente_id');
        $dias = (int) $this->option('dias');
        $output = $this->option('output') ?: null;

        if ($clienteId <= 0) {
            $this->error('cliente_id inválido.');

            return self::FAILURE;
        }

        $this->info("Gerando relatório de recrutamento cliente_id={$clienteId} · {$dias} dias...");

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
                ['Currículos cadastrados', $r['curriculos_periodo']],
                ['Candidaturas (Feedback)', $r['candidaturas_periodo']],
                ['Vagas abertas criadas', $r['vagas_abertas_criadas']],
                ['Vagas preenchidas (ADMITIDO)', $r['vagas_preenchidas_periodo']],
                ['ADMITIDO snapshot', $r['admitidos_snapshot']->total.' (M '.$r['admitidos_snapshot']->matriz.' / F '.$r['admitidos_snapshot']->filial.')'],
                ['DEMITIDO snapshot', $r['demitidos_snapshot']->total.' (M '.$r['demitidos_snapshot']->matriz.' / F '.$r['demitidos_snapshot']->filial.')'],
                ['DEMITIDO período', $r['demitidos_periodo']['total'].' (M '.$r['demitidos_periodo']['matriz'].' / F '.$r['demitidos_periodo']['filial'].')'],
            ]
        );

        $this->newLine();
        $this->info('Arquivos:');
        foreach ($result['arquivos'] as $tipo => $path) {
            $this->line("  [{$tipo}] {$path}");
        }

        return self::SUCCESS;
    }
}
