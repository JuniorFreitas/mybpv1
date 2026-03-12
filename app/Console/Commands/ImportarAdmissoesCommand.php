<?php

namespace App\Console\Commands;

use App\Services\Admissao\Importacao\LeitorPlanilhaAdmissao;
use App\Services\Admissao\Importacao\MapperLinhaPlanilhaParaPayload;
use App\Services\Admissao\Importacao\PersistidorAdmissaoImportada;
use App\Services\Admissao\Importacao\ResolvedorVagaAreaCentroCusto;
use App\Services\Admissao\Importacao\ValidadorLinhaPlanilhaAdmissao;
use Illuminate\Console\Command;
use RuntimeException;

class ImportarAdmissoesCommand extends Command
{
    protected $signature = 'mybp:importar-admissoes
                            {arquivo : Caminho relativo a storage/app/ ou absoluto do .xlsx}
                            {empresa_id : ID da empresa}
                            {--user_id= : ID do usuário responsável (opcional)}
                            {--chunk=100 : Tamanho do lote de linhas}
                            {--relatorio= : Caminho para salvar CSV/Excel com resultado por linha}';

    protected $description = 'Importa admissões a partir de planilha Excel (aba Dados)';

    public function handle(): int
    {
        $arquivo = $this->argument('arquivo');
        $empresaId = (int) $this->argument('empresa_id');
        $userId = $this->option('user_id') ? (int) $this->option('user_id') : null;
        $chunkSize = (int) $this->option('chunk');
        $relatorioPath = $this->option('relatorio');

        $path = $this->resolverPath($arquivo);
        if (!is_readable($path)) {
            $this->error("Arquivo não encontrado ou não legível: {$arquivo}");
            return self::FAILURE;
        }

        $leitor = new LeitorPlanilhaAdmissao();
        $validador = new ValidadorLinhaPlanilhaAdmissao();
        $resolvedor = new ResolvedorVagaAreaCentroCusto();
        $mapper = new MapperLinhaPlanilhaParaPayload();
        $persistidor = new PersistidorAdmissaoImportada();

        $relatorio = [];
        $totalProcessadas = 0;
        $totalSucesso = 0;
        $totalErros = 0;
        $numeroLinhaGlobal = 2;

        try {
            $iterator = $leitor->ler($path, $chunkSize);
            foreach ($iterator as $chunk) {
                foreach ($chunk as $linha) {
                    $errosLinha = $validador->validar($linha, $numeroLinhaGlobal, $empresaId);
                    if (!empty($errosLinha)) {
                        foreach ($errosLinha as $campo => $dados) {
                            $relatorio[] = $this->linhaRelatorio(
                                $numeroLinhaGlobal,
                                $this->mascararCpfRelatorio($linha['cpf'] ?? ''),
                                'erro_validacao',
                                $campo,
                                $dados['mensagem'] ?? '',
                                $dados['como_corrigir'] ?? ''
                            );
                        }
                        $totalErros++;
                    } else {
                        $rVaga = $resolvedor->resolverVaga($empresaId, $linha['cod_vaga'] ?? '');
                        $rArea = $resolvedor->resolverArea($empresaId, $linha['cod_area'] ?? '');
                        $rCc = $resolvedor->resolverCentroCusto($empresaId, $linha['centro_custo'] ?? '');
                        if ($rVaga['erro'] !== null) {
                            $relatorio[] = $this->linhaRelatorio($numeroLinhaGlobal, $this->mascararCpfRelatorio($linha['cpf'] ?? ''), 'erro_resolucao', 'cod_vaga', $rVaga['erro'], 'Use o código numérico ou cadastre a vaga.');
                            $totalErros++;
                        } elseif ($rCc['erro'] !== null) {
                            $relatorio[] = $this->linhaRelatorio($numeroLinhaGlobal, $this->mascararCpfRelatorio($linha['cpf'] ?? ''), 'erro_resolucao', 'centro_custo', $rCc['erro'], 'Use o código ou cadastre o centro de custo.');
                            $totalErros++;
                        } else {
                            $areaId = $rArea['id'];
                            if ($areaId === null && ($linha['cod_area'] ?? '') !== '') {
                                $relatorio[] = $this->linhaRelatorio($numeroLinhaGlobal, $this->mascararCpfRelatorio($linha['cpf'] ?? ''), 'erro_resolucao', 'cod_area', $rArea['erro'] ?? 'Área não encontrada.', 'Use o código ou nome cadastrado.');
                                $totalErros++;
                            } else {
                                $payload = $mapper->map($linha, (int) $rVaga['id'], $areaId, (int) $rCc['id']);
                                $resultado = $persistidor->persistir($payload, $empresaId, $userId);
                                if (!$resultado['sucesso']) {
                                    $relatorio[] = $this->linhaRelatorio($numeroLinhaGlobal, $this->mascararCpfRelatorio($linha['cpf'] ?? ''), 'erro_persistencia', '', $resultado['erro'] ?? 'Erro ao persistir.', 'Verifique os dados e tente novamente.');
                                    $totalErros++;
                                } else {
                                    $totalSucesso++;
                                }
                            }
                        }
                    }
                    $totalProcessadas++;
                    $numeroLinhaGlobal++;
                }
            }
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Erro durante a importação: ' . $e->getMessage());
            return self::FAILURE;
        }

        if ($relatorioPath !== null && $relatorioPath !== '') {
            $this->escreverRelatorio($relatorioPath, $relatorio);
            $this->info("Relatório salvo em: {$relatorioPath}");
        }

        $this->info("Processadas {$totalProcessadas} linhas: {$totalSucesso} sucesso, {$totalErros} com erro.");
        return self::SUCCESS;
    }

    private function resolverPath(string $arquivo): string
    {
        if (str_starts_with($arquivo, '/') || preg_match('#^[A-Za-z]:\\\\#', $arquivo)) {
            return $arquivo;
        }
        return storage_path('app/' . ltrim($arquivo, '/'));
    }

    private function mascararCpfRelatorio($cpf): string
    {
        $digitos = preg_replace('/[^0-9]/', '', (string) $cpf);
        if (strlen($digitos) < 4) {
            return '***.***.***-**';
        }
        return '***.***.***-' . substr($digitos, -4);
    }

    /**
     * @param array<int, array{linha_planilha: int, cpf_planilha: string, status: string, campo: string, mensagem: string, como_corrigir: string}> $relatorio
     */
    private function escreverRelatorio(string $path, array $relatorio): void
    {
        $dir = dirname($path);
        if ($dir !== '.' && !is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $fp = fopen($path, 'w');
        if ($fp === false) {
            return;
        }
        fputcsv($fp, ['linha_planilha', 'cpf_planilha', 'status', 'campo', 'mensagem', 'como_corrigir'], ';');
        foreach ($relatorio as $linha) {
            fputcsv($fp, [
                $linha['linha_planilha'],
                $linha['cpf_planilha'],
                $linha['status'],
                $linha['campo'],
                $linha['mensagem'],
                $linha['como_corrigir'],
            ], ';');
        }
        fclose($fp);
    }

    private function linhaRelatorio(int $linhaPlanilha, string $cpfPlanilha, string $status, string $campo, string $mensagem, string $comoCorrigir): array
    {
        return [
            'linha_planilha' => $linhaPlanilha,
            'cpf_planilha' => $cpfPlanilha,
            'status' => $status,
            'campo' => $campo,
            'mensagem' => $mensagem,
            'como_corrigir' => $comoCorrigir,
        ];
    }
}
