<?php

namespace App\Console\Commands;

use App\Models\Cliente;
use Illuminate\Console\Command;

class TreinamentoDuplicaddos extends Command
{
    protected $signature = 'mybp:treinamento-duplicados {--empresa_id=} {--dry-run : Executar sem deletar}';

    public function handle()
    {
        $empresas = $this->buscarEmpresas($this->option('empresa_id'));
        $this->info('Empresas encontradas: ' . count($empresas));

        $totalGeral = [
            'ids_para_remover' => [],
            'total_duplicatas' => 0,
            'duplicatas_detalhadas' => []
        ];

        foreach ($empresas as $empresa) {
            $this->info('Processando empresa: ' . $empresa['razao_social'] . ' (ID: ' . $empresa['id'] . ')');

            $todosFeedbacksDaEmpresa = $this->buscaTodosFeedbacksDaEmpresa($empresa['id']);
            $this->info('Total de Feedbacks: ' . count($todosFeedbacksDaEmpresa));

            if ($todosFeedbacksDaEmpresa->isEmpty()) {
                $this->warn('Nenhum feedback encontrado para esta empresa.');
                continue;
            }

            // Processar treinamentos duplicados desta empresa
            $resultado = $this->processarTreinamentosDuplicados($todosFeedbacksDaEmpresa, $empresa['id']);

            // Acumular resultados gerais
            $totalGeral['ids_para_remover'] = array_merge($totalGeral['ids_para_remover'], $resultado['ids_para_remover']);
            $totalGeral['total_duplicatas'] += $resultado['total_duplicatas'];
            $totalGeral['duplicatas_detalhadas'] = array_merge($totalGeral['duplicatas_detalhadas'], $resultado['duplicatas_detalhadas']);

            // Exibir resultados desta empresa
            $this->info("  - Duplicatas encontradas: " . count($resultado['duplicatas_detalhadas']));
            $this->info("  - IDs para remover: " . count($resultado['ids_para_remover']));

            if (!empty($resultado['duplicatas_detalhadas'])) {
                $this->warn("  Detalhes das duplicatas:");
                foreach ($resultado['duplicatas_detalhadas'] as $duplicata) {
                    $this->line("    Vencimento: {$duplicata['vencimento_label']} (ID: {$duplicata['vencimento_id']})");
                    $this->line("    Mantido: Treinamento {$duplicata['mantido']['treinamento_id']} (Data: {$duplicata['mantido']['data_vencimento']})");
                    foreach ($duplicata['removidos'] as $removido) {
                        $this->line("    Remover: Treinamento {$removido['treinamento_id']} (Data: {$removido['data_vencimento']})");
                    }
                    $this->line("    ---");
                }
            }
        }

        // Resultados finais
        $this->info('=== RESUMO GERAL ===');
        $this->info('Total de treinamentos para remover: ' . count($totalGeral['ids_para_remover']));
        $this->info('Total de vencimentos duplicados: ' . count($totalGeral['duplicatas_detalhadas']));

        // Executar remoção se não for dry-run
        if (!$this->option('dry-run') && !empty($totalGeral['ids_para_remover'])) {
            if ($this->confirm('Deseja executar a remoção dos treinamentos duplicados?')) {
                $this->executarRemocao($totalGeral['ids_para_remover']);
            }
        } elseif ($this->option('dry-run')) {
            $this->warn('Modo dry-run ativo. Nenhum registro foi removido.');
        }
    }

    private function buscarEmpresas($empresa_id = null)
    {
        $empresa = Cliente::select(['id', 'razao_social', 'cnpj', 'ativo'])
            ->withoutGlobalScopes()
            ->where('ativo', true);

        if ($empresa_id) {
            $empresa->where('id', $empresa_id);
        }

        return $empresa->get()->toArray();
    }

    private function buscaTodosFeedbacksDaEmpresa($empresa_id): \Illuminate\Support\Collection
    {
        return \App\Models\FeedbackCurriculo::select(['empresa_id', 'id', 'curriculo_id'])
            ->withoutGlobalScopes()
            ->where('empresa_id', $empresa_id)
            ->orderByDesc('id')
            ->pluck('id');
    }

    private function processarTreinamentosDuplicados($todosFeedbacksDaEmpresa, $empresa_id)
    {
        $idsParaRemover = [];
        $duplicatasEncontradas = [];

        // Processar feedbacks em chunks de 500 para evitar estouro de memória
        $todosFeedbacksDaEmpresa->chunk(500)->each(function ($chunkFeedbacks) use (&$idsParaRemover, &$duplicatasEncontradas, $empresa_id) {

            $this->line("    Processando chunk de " . count($chunkFeedbacks) . " feedbacks...");

            $treinamentosFeedback = \App\Models\Treinamento::withoutGlobalScopes()
                ->whereIn('feedback_id', $chunkFeedbacks)
                ->orderBy('feedback_id')
                ->with('Vencimentos')
                ->get()
                ->groupBy('feedback_id');

            foreach ($treinamentosFeedback as $feedbackId => $treinamentos) {
                // Coletar todos os vencimentos com suas informações
                $todosVencimentos = [];

                foreach ($treinamentos as $treinamento) {
                    foreach ($treinamento->vencimentos as $vencimento) {
                        $todosVencimentos[] = [
                            'treinamento_id' => $treinamento->id,
                            'vencimento_id' => $vencimento->id,
                            'vencimento_label' => $vencimento->label,
                            'data_vencimento_default' => $vencimento->pivot->data_vencimento_default,
                        ];
                    }
                }

                // Agrupar por vencimento_id e encontrar duplicatas
                $vencimentosAgrupados = collect($todosVencimentos)->groupBy('vencimento_id');

                foreach ($vencimentosAgrupados as $vencimentoId => $grupoVencimentos) {
                    if ($grupoVencimentos->count() > 1) {
                        // Ordenar por data (mais recente primeiro) e remover os mais antigos
                        $vencimentosOrdenados = $grupoVencimentos->sortByDesc('data_vencimento_default');
                        $vencimentoMantido = $vencimentosOrdenados->first();
                        $vencimentosParaRemover = $vencimentosOrdenados->skip(1);

                        // Armazenar detalhes da duplicata para validação
                        $duplicatasEncontradas[] = [
                            'empresa_id' => $empresa_id,
                            'vencimento_id' => $vencimentoId,
                            'vencimento_label' => $vencimentoMantido['vencimento_label'],
                            'feedback_id' => $feedbackId,
                            'mantido' => [
                                'treinamento_id' => $vencimentoMantido['treinamento_id'],
                                'data_vencimento' => $vencimentoMantido['data_vencimento_default']
                            ],
                            'removidos' => $vencimentosParaRemover->map(function ($vencimento) {
                                return [
                                    'treinamento_id' => $vencimento['treinamento_id'],
                                    'data_vencimento' => $vencimento['data_vencimento_default']
                                ];
                            })->values()->toArray()
                        ];

                        foreach ($vencimentosParaRemover as $vencimento) {
                            $idsParaRemover[] = $vencimento['treinamento_id'];
                        }
                    }
                }
            }

            // Limpar variáveis para liberar memória
            unset($treinamentosFeedback);
        });

        // Remover duplicatas dos IDs
        $idsParaRemover = array_unique($idsParaRemover);

        return [
            'ids_para_remover' => $idsParaRemover,
            'total_duplicatas' => count($idsParaRemover),
            'duplicatas_detalhadas' => $duplicatasEncontradas
        ];
    }

    private function executarRemocao(array $idsParaRemover)
    {
        $this->info('Iniciando remoção de ' . count($idsParaRemover) . ' treinamentos...');

        // Remover em chunks para evitar problemas com queries muito grandes
        collect($idsParaRemover)->chunk(100)->each(function ($chunk) {
            \App\Models\Treinamento::withoutGlobalScopes()
                ->whereIn('id', $chunk->toArray())
                ->delete();

            $this->line('Removidos ' . count($chunk) . ' treinamentos...');
        });

        $this->info('Remoção concluída!');
    }
}
