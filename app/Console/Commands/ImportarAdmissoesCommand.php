<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Admissao\Importacao\LeitorPlanilhaAdmissao;
use App\Services\Admissao\Importacao\MapperLinhaPlanilhaParaPayload;
use App\Services\Admissao\Importacao\PersistidorAdmissaoImportada;
use App\Services\Admissao\Importacao\ResolvedorVagaAreaCentroCusto;
use App\Services\Admissao\Importacao\ValidadorLinhaPlanilhaAdmissao;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
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
        $userId = $this->option('user_id') ? (int) $this->option('user_id') : $this->resolverUsuarioEmpresa($empresaId);
        $chunkSize = (int) $this->option('chunk');
        $relatorioPath = $this->option('relatorio');

        $this->info('Iniciando importação de admissões...');
        $path = $this->resolverPath($arquivo);
        if (!is_readable($path)) {
            $this->error("Arquivo não encontrado ou não legível: {$arquivo}");
            return self::FAILURE;
        }
        $this->line("  Arquivo: {$path}");
        $this->line("  Empresa ID: {$empresaId}");
        $this->line('  Chunk: ' . $chunkSize . ' linhas');
        if ($relatorioPath) {
            $this->line("  Relatório: {$relatorioPath}");
        }

        if ($userId === null) {
            $this->newLine();
            $this->error('Nenhum usuário encontrado para a empresa. Em background não há sessão; é necessário estar logado.');
            $this->line('  Cadastre um usuário ativo para a empresa ou informe --user_id=<id>.');
            return self::FAILURE;
        }

        Auth::loginUsingId($userId);
        $this->line('  Usuário (login): ' . $userId);
        $this->newLine();

        $this->info('Carregando serviços (leitor, validador, resolvedor, mapper, persistidor)...');
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
            $this->info('Lendo planilha (aba Dados)...');
            $iterator = $leitor->ler($path, $chunkSize);
            $chunkNum = 0;
            foreach ($iterator as $chunk) {
                $chunkNum++;
                $linhasNoChunk = count($chunk);
                $this->line("  Chunk {$chunkNum}: processando {$linhasNoChunk} linha(s) (linhas " . $numeroLinhaGlobal . ' a ' . ($numeroLinhaGlobal + $linhasNoChunk - 1) . ')');
                foreach ($chunk as $linha) {
                    $errosLinha = $validador->validar($linha, $numeroLinhaGlobal, $empresaId);
                    if (!empty($errosLinha)) {
                        foreach ($errosLinha as $campo => $dados) {
                            $msg = $dados['mensagem'] ?? '';
                            $comoCorrigir = $dados['como_corrigir'] ?? '';
                            $relatorio[] = $this->linhaRelatorio(
                                $numeroLinhaGlobal,
                                $this->mascararCpfRelatorio($linha['cpf'] ?? ''),
                                'erro_validacao',
                                $campo,
                                $msg,
                                $comoCorrigir
                            );
                            $this->warn("  Linha {$numeroLinhaGlobal} [{$campo}]: {$msg}");
                            if ($comoCorrigir !== '') {
                                $this->line("    → {$comoCorrigir}");
                            }
                        }
                        $totalErros++;
                    } else {
                        $rVaga = $resolvedor->resolverVaga($empresaId, $linha['cod_vaga'] ?? '');
                        $rArea = $resolvedor->resolverArea($empresaId, $linha['cod_area'] ?? '');
                        $rCc = $resolvedor->resolverCentroCusto($empresaId, $linha['centro_custo'] ?? '');
                        if ($rVaga['erro'] !== null) {
                            $relatorio[] = $this->linhaRelatorio($numeroLinhaGlobal, $this->mascararCpfRelatorio($linha['cpf'] ?? ''), 'erro_resolucao', 'cod_vaga', $rVaga['erro'], 'Use o código numérico ou cadastre a vaga.');
                            $this->warn("  Linha {$numeroLinhaGlobal} [cod_vaga]: {$rVaga['erro']}");
                            $this->line('    → Use o código numérico ou cadastre a vaga.');
                            $totalErros++;
                        } elseif ($rCc['erro'] !== null) {
                            $relatorio[] = $this->linhaRelatorio($numeroLinhaGlobal, $this->mascararCpfRelatorio($linha['cpf'] ?? ''), 'erro_resolucao', 'centro_custo', $rCc['erro'], 'Use o código ou cadastre o centro de custo.');
                            $this->warn("  Linha {$numeroLinhaGlobal} [centro_custo]: {$rCc['erro']}");
                            $this->line('    → Use o código ou cadastre o centro de custo.');
                            $totalErros++;
                        } else {
                            $areaId = $rArea['id'];
                            if ($areaId === null && ($linha['cod_area'] ?? '') !== '') {
                                $msgArea = $rArea['erro'] ?? 'Área não encontrada.';
                                $relatorio[] = $this->linhaRelatorio($numeroLinhaGlobal, $this->mascararCpfRelatorio($linha['cpf'] ?? ''), 'erro_resolucao', 'cod_area', $msgArea, 'Use o código ou nome cadastrado.');
                                $this->warn("  Linha {$numeroLinhaGlobal} [cod_area]: {$msgArea}");
                                $this->line('    → Use o código ou nome cadastrado.');
                                $totalErros++;
                            } else {
                                $payload = $mapper->map($linha, (int) $rVaga['id'], $areaId, (int) $rCc['id']);
                                $resultado = $persistidor->persistir($payload, $empresaId, $userId);
                                if (!$resultado['sucesso']) {
                                    $msgPersist = $resultado['erro'] ?? 'Erro ao persistir.';
                                    $relatorio[] = $this->linhaRelatorio($numeroLinhaGlobal, $this->mascararCpfRelatorio($linha['cpf'] ?? ''), 'erro_persistencia', '', $msgPersist, 'Verifique os dados e tente novamente.');
                                    $this->warn("  Linha {$numeroLinhaGlobal} [persistência]: {$msgPersist}");
                                    $this->line('    → Verifique os dados e tente novamente.');
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
            $this->newLine();
            $this->error($e->getMessage());
            $this->exibirDetalhesExcecao($e);
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error('Erro durante a importação: ' . $e->getMessage());
            $this->exibirDetalhesExcecao($e);
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Processamento da planilha concluído.');

        if ($relatorioPath !== null && $relatorioPath !== '') {
            $this->line('Gerando relatório de erros...');
            $this->escreverRelatorio($relatorioPath, $relatorio);
            $this->info("Relatório salvo em: {$relatorioPath}");
        }

        $this->info("Resumo: {$totalProcessadas} linhas processadas — {$totalSucesso} sucesso, {$totalErros} com erro.");
        $this->line('Importação finalizada.');
        return self::SUCCESS;
    }

    private function exibirDetalhesExcecao(\Throwable $e): void
    {
        $this->line('  Arquivo: ' . $e->getFile() . ':' . $e->getLine());
        if ($this->getOutput()->isVerbose()) {
            $this->newLine();
            $this->line('<comment>Stack trace:</comment>');
            $this->line($e->getTraceAsString());
        }
        if ($e->getPrevious() !== null) {
            $this->newLine();
            $this->warn('Causa anterior: ' . $e->getPrevious()->getMessage());
            $this->line('  ' . $e->getPrevious()->getFile() . ':' . $e->getPrevious()->getLine());
        }
    }

    /**
     * Retorna o ID de um usuário da empresa (usuario de sistema) para login na importação.
     * Preferência: Administrador, depois primeiro usuário ativo da empresa.
     */
    private function resolverUsuarioEmpresa(int $empresaId): ?int
    {
        $user = User::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderByRaw("tipo = ? DESC", [User::ADMINISTRADOR])
            ->first(['id']);

        return $user?->id;
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
