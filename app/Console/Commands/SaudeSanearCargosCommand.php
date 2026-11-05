<?php

namespace App\Console\Commands;

use App\Domain\SaudeOcupacional\Colaboradores\SaneamentoCargosService;
use Illuminate\Console\Command;

/**
 * Vincula colaboradores ativos sem cargo ao catálogo (vagas) pelo nome do cargo da admissão.
 * Sem --aplicar só mostra o que faria. Não mostra nomes de pessoas.
 */
class SaudeSanearCargosCommand extends Command
{
    protected $signature = 'mybp:saude-sanear-cargos
        {empresa_id : Id da empresa (cliente)}
        {--aplicar : Grava o vaga_id dos colaboradores casados (sem a opção, só simula)}';

    protected $description = 'Saúde Ocupacional: vincula colaboradores sem cargo ao catálogo de cargos pelo nome do cargo da admissão';

    public function handle(SaneamentoCargosService $saneamento): int
    {
        $empresaId = (int) $this->argument('empresa_id');
        if ($empresaId <= 0) {
            $this->error('Informe um empresa_id válido.');

            return self::FAILURE;
        }

        $analise = $saneamento->analisar($empresaId);

        $this->info("Empresa {$empresaId}: {$analise['sem_cargo']} colaborador(es) ativo(s) sem cargo no catálogo.");
        $this->line("Casam pelo nome do cargo: {$analise['casaveis']}.");

        if ($analise['nao_casados'] !== []) {
            $this->warn('Cargos da admissão sem correspondente no catálogo (cadastre em Cadastro → Cargos ou corrija a admissão):');
            $this->table(
                ['Cargo na admissão', 'Colaboradores'],
                collect($analise['nao_casados'])->map(fn ($n, $cargo) => [$cargo, $n])->values()->take(50)->all()
            );
        }

        if (!$this->option('aplicar')) {
            $this->comment('Simulação. Rode de novo com --aplicar para gravar.');

            return self::SUCCESS;
        }

        $gravados = $saneamento->aplicar($empresaId, $analise['mapa']);
        $this->info("{$gravados} colaborador(es) vinculado(s) ao cargo.");

        return self::SUCCESS;
    }
}
