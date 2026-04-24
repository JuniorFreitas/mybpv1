<?php

namespace App\Console\Commands;

use App\Models\Treinamento;
use App\Models\TreinamentoVencimentoHistorico;
use App\Models\Pivot\TreinamentoVencimento;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use MasterTag\DataHora;

class ImportaTreinamentos extends Command
{
    private const STATUS_SUCESSO = 'SUCESSO';
    private const STATUS_FALHA = 'FALHA';
    private const STATUS_SKIP = 'SKIP';
    private const SKIP_PREFIX = '[SKIP] ';

    protected $signature = 'mybp:import-tre
                            {--empresa_id= : ID da empresa}
                            {--chunk-size=1000 : Tamanho do chunk}
                            {--arquivo= : Nome do arquivo JSON}
                            {--possuiCampoVencimento : Indica se possui campo de vencimento}
                            {--limpar-vencimentos-antigos : Remove vencimentos anteriores ao atualizar}
                            {--substituir-vencimentos : Substitui todos os vencimentos pelos do arquivo}
                            {--gerar-relatorio : Gera arquivo CSV com relatório da importação}
                            {--enviar-email= : Email para enviar o relatório (ex: usuario@email.com)}
                            {--email-cc= : Emails em cópia separados por vírgula}';

    protected $description = 'Importa treinamentos de arquivo JSON com suporte a processamento lazy, cache e controle de vencimentos';

    /**
     * OPÇÕES DISPONÍVEIS:
     *
     * --empresa_id=X              : ID da empresa (obrigatório)
     * --arquivo=nome              : Nome do arquivo JSON sem extensão (obrigatório)
     * --chunk-size=1000           : Quantidade de registros por chunk (padrão: 1000)
     * --possuiCampoVencimento     : Indica que o JSON já possui data_vencimento calculada
     * --limpar-vencimentos-antigos: Remove TODOS vencimentos na primeira vez que processa cada treinamento
     * --substituir-vencimentos    : Mantém apenas os vencimentos do arquivo (sincronização)
     * --gerar-relatorio           : Gera arquivo CSV (SEM enviar e-mail)
     * --enviar-email=email        : Gera CSV E envia para o e-mail informado
     * --email-cc=emails           : E-mails em cópia separados por vírgula
     *
     * ═══════════════════════════════════════════════════════════════════
     * EXEMPLOS DE USO - RELATÓRIOS
     * ═══════════════════════════════════════════════════════════════════
     *
     * 1️⃣ SEM RELATÓRIO (importação simples)
     * ────────────────────────────────────────────────────────────────────
     * php artisan mybp:importa-treinamento --empresa_id=1 --arquivo=dados
     *
     * Resultado: ✅ Importação executada (sem CSV, sem e-mail)
     *
     *
     * 2️⃣ COM RELATÓRIO CSV (sem e-mail)
     * ────────────────────────────────────────────────────────────────────
     * php artisan mybp:importa-treinamento \
     *     --empresa_id=1 \
     *     --arquivo=dados \
     *     --gerar-relatorio
     *
     * Resultado: ✅ Importação executada + 📄 CSV gerado (sem e-mail)
     * Arquivo salvo em: storage/app/relatorios/relatorio_*.csv
     *
     *
     * 3️⃣ COM RELATÓRIO CSV + E-MAIL
     * ────────────────────────────────────────────────────────────────────
     * php artisan mybp:importa-treinamento \
     *     --empresa_id=1 \
     *     --arquivo=dados \
     *     --enviar-email=gestor@empresa.com
     *
     * Resultado: ✅ Importação executada + 📄 CSV gerado + 📧 E-mail enviado
     *
     *
     * 4️⃣ COM RELATÓRIO CSV + E-MAIL + CÓPIAS
     * ────────────────────────────────────────────────────────────────────
     * php artisan mybp:importa-treinamento \
     *     --empresa_id=1 \
     *     --arquivo=dados \
     *     --enviar-email=gestor@empresa.com \
     *     --email-cc=rh@empresa.com,supervisor@empresa.com
     *
     * Resultado: ✅ Importação + 📄 CSV + 📧 E-mail para 3 pessoas
     *
     *
     * ═══════════════════════════════════════════════════════════════════
     * OUTROS EXEMPLOS
     * ═══════════════════════════════════════════════════════════════════
     *
     * # Reimportação completa COM relatório (sem e-mail)
     * php artisan mybp:importa-treinamento \
     *     --empresa_id=1 \
     *     --arquivo=dados \
     *     --limpar-vencimentos-antigos \
     *     --gerar-relatorio
     *
     * # Sincronização COM relatório e e-mail
     * php artisan mybp:importa-treinamento \
     *     --empresa_id=1 \
     *     --arquivo=dados \
     *     --substituir-vencimentos \
     *     --enviar-email=gestor@empresa.com
     *
     * # Importação com chunk personalizado e relatório
     * php artisan mybp:importa-treinamento \
     *     --empresa_id=1 \
     *     --arquivo=dados_grandes \
     *     --chunk-size=500 \
     *     --gerar-relatorio
     *
     *
     * ═══════════════════════════════════════════════════════════════════
     * INFORMAÇÕES DO RELATÓRIO CSV
     * ═══════════════════════════════════════════════════════════════════
     *
     * O relatório contém as seguintes colunas:
     * • Nome: Nome do colaborador
     * • CPF: CPF formatado (xxx.xxx.xxx-xx)
     * • Treinamento: Tipo do treinamento (NR10, NR35, etc)
     * • Data Treinamento: Data de realização
     * • Data Vencimento: Data de vencimento
     * • Status: SUCESSO ou FALHA
     * • Mensagem: Detalhes do processamento
     * • ID Treinamento: ID do registro no banco
     *
     * Local de salvamento: storage/app/relatorios/
     * Formato: CSV com delimitador ";" (ponto-e-vírgula)
     * Encoding: UTF-8 com BOM (compatível com Excel)
     *
     *
     * ═══════════════════════════════════════════════════════════════════
     * CASOS DE USO - VENCIMENTOS MÚLTIPLOS
     * ═══════════════════════════════════════════════════════════════════
     *
     * JSON com 2 registros do MESMO treinamento mas vencimentos diferentes:
     * [
     *   {"cpf": "123", "tipo": "NR10", "vencimento_id": 1},
     *   {"cpf": "123", "tipo": "NR10", "vencimento_id": 2}
     * ]
     *
     * • Modo padrão: [1, 2] + vencimentos antigos mantidos
     * • --limpar-vencimentos-antigos: Limpa tudo → Resultado: [1, 2]
     * • --substituir-vencimentos: Remove outros → Resultado: [1, 2]
     */

    private bool $possuiCampoVencimento;
    private int $empresaId;
    private array $feedbackCache = [];
    private array $vencimentoCache = [];
    private array $treinamentosLimpos = []; // Rastreia quais treinamentos já tiveram vencimentos limpos
    private array $vencimentosProcessados = []; // Rastreia vencimentos adicionados por treinamento [treinamento_id => [vencimento_ids]]
    private array $resultadosImportacao = []; // Armazena resultados para o relatório CSV

    public function __construct()
    {
        parent::__construct();
    }

    // ========================================
    // MÉTODO PRINCIPAL
    // ========================================

    public function handle()
    {
        $this->possuiCampoVencimento = $this->option('possuiCampoVencimento');

        try {
            $this->inicializarImportacao();
            $dadosJson = $this->carregarArquivoJson();

            // Processar em chunks com transações isoladas
            $this->processarComTransacoesPorChunk($dadosJson);

            $this->info('Importação concluída com sucesso!');

            // Gerar relatório CSV se solicitado
            $this->processarRelatorio(false);

            return true;
        } catch (\Exception $e) {
            $this->exibirErroGeral($e);

            // Mesmo com erro, gerar relatório se solicitado
            $this->processarRelatorio(true);

            return false;
        }
    }

    private function processarRelatorio(bool $comErro = false): void
    {
        // Verificar se deve gerar relatório
        $deveGerarRelatorio = $this->option('gerar-relatorio') || $this->option('enviar-email');

        if (!$deveGerarRelatorio) {
            return;
        }

        try {
            $this->info("");
            $this->info("========================================");
            $this->info("📊 GERANDO RELATÓRIO DA IMPORTAÇÃO");
            $this->info("========================================");

            $caminhoCSV = $this->gerarRelatorioCSV();

            // Verificar se deve enviar por e-mail
            if ($this->option('enviar-email')) {
                $this->info("");
                $this->enviarRelatorioEmail($caminhoCSV, $comErro);
            } else {
                $this->info("");
                $this->info("✅ Relatório CSV gerado com sucesso!");
                $this->info("📁 Arquivo salvo em:");
                $this->info("   {$caminhoCSV}");
                $this->info("");
                $this->warn("💡 Para enviar por e-mail, use: --enviar-email=seu@email.com");
            }

            $this->info("========================================");
        } catch (\Exception $csvException) {
            $this->error("❌ Erro ao processar relatório: " . $csvException->getMessage());
        }
    }

    private function processarComTransacoesPorChunk(\Illuminate\Support\LazyCollection $dadosJson): void
    {
        $chunkSize = (int)$this->option('chunk-size') ?: 1000;
        $this->info("Processando em chunks de {$chunkSize} registros com transações isoladas");

        $totalProcessados = 0;
        $totalErros = 0;

        $dadosJson->chunk($chunkSize)->each(function ($chunk) use (&$totalProcessados, &$totalErros) {
            // Cada chunk tem sua própria transação
            \DB::beginTransaction();
            \DB::enableQueryLog();

            try {
                $this->info("=== Processando chunk de {$chunk->count()} registros ===");

                // Pré-carregar feedbacks para o chunk inteiro (otimização)
                $cpfsDoChunk = $chunk->pluck('cpf')->map(function ($cpf) {
                    return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
                })->unique()->toArray();

                $this->preCarregarFeedbacks($cpfsDoChunk);

                foreach ($chunk as $index => $dados) {
                    try {
                        $dados = $this->prepararDados($dados);
                        $globalIndex = $totalProcessados + $index;

                        $this->info("Processando registro {$globalIndex} - CPF: {$dados['cpf']}");

                        $this->processarRegistroIndividual($dados);
                    } catch (\Exception $e) {
                        $totalErros++;
                        $this->exibirErroRegistro($e, $globalIndex, $dados);

                        // Decidir se deve parar o chunk ou continuar
                        if ($totalErros > 10) {
                            throw new \Exception("Muitos erros detectados ({$totalErros}). Abortando chunk.");
                        }

                        $this->warn("Continuando processamento do chunk...");
                    }
                }

                // Commit da transação do chunk
                \DB::commit();

                $totalProcessados += $chunk->count();
                $this->info("Chunk processado com sucesso. Total acumulado: {$totalProcessados} registros");

                // Se estiver no modo substituir-vencimentos, fazer a limpeza final
                if ($this->option('substituir-vencimentos')) {
                    $this->finalizarSubstituicaoVencimentos();
                }
            } catch (\Exception $e) {
                // Rollback apenas deste chunk
                \DB::rollback();
                $this->error("Erro no chunk. Fazendo rollback do chunk atual.");
                $this->error("Erro: " . $e->getMessage());

                // Decidir se deve continuar com próximo chunk ou abortar tudo
                $this->warn("Continuando com próximo chunk...");
            }

            // Limpar cache após cada chunk para economizar memória
            $this->limparCache();

            // Liberar memória após cada chunk
            gc_collect_cycles();
        });

        $this->info("=== Resumo da Importação ===");
        $this->info("Total de registros processados: {$totalProcessados}");
        $this->info("Total de erros: {$totalErros}");

        if ($totalErros > 0) {
            $this->warn("Importação concluída com {$totalErros} erro(s)");
        }
    }

    // ========================================
    // MÉTODOS DE INICIALIZAÇÃO
    // ========================================

    private function inicializarImportacao(): void
    {
        $this->autenticarEmpresa();
        $this->info('Iniciando importação de treinamento...');

        $this->empresaId = (int)$this->option('empresa_id');

        if (empty($this->empresaId)) {
            throw new \Exception('Nenhum ID de empresa informado');
        }

        // Configurar otimizações de memória
        ini_set('memory_limit', '512M'); // Ajustar conforme necessário

        $chunkSize = (int)$this->option('chunk-size') ?: 1000;
        $this->info("Configurado para processar em chunks de {$chunkSize} registros");

        // Mostrar configurações de vencimento
        $this->exibirConfiguracoesVencimento();
    }

    private function exibirConfiguracoesVencimento(): void
    {
        $this->info("=== Configurações de Vencimento ===");

        if ($this->option('limpar-vencimentos-antigos')) {
            $this->warn("⚠️  ATENÇÃO: Todos os vencimentos antigos serão REMOVIDOS antes de processar");
        }

        if ($this->option('substituir-vencimentos')) {
            $this->warn("⚠️  ATENÇÃO: Apenas o vencimento do arquivo será mantido (outros serão removidos)");
        }

        if (!$this->option('limpar-vencimentos-antigos') && !$this->option('substituir-vencimentos')) {
            $this->info("✓ Modo padrão: Vencimentos serão adicionados/atualizados sem remover existentes");
        }

        $this->info("===================================");
    }

    private function autenticarEmpresa(): void
    {
        $empresaId = $this->option('empresa_id');

        if (empty($empresaId)) {
            throw new \Exception('Nenhum ID de empresa informado');
        }

        $this->info("Autenticando empresa com ID {$empresaId}...");
        \Auth::loginUsingId($empresaId);
    }

    // ========================================
    // MÉTODOS DE CARREGAMENTO DE ARQUIVO
    // ========================================

    private function carregarArquivoJson(): \Illuminate\Support\LazyCollection
    {
        $this->info('Lendo arquivo JSON...');

        $path = $this->obterCaminhoArquivo();
        $this->validarArquivo($path);

        return $this->criarLazyCollectionDoArquivo($path);
    }

    private function obterCaminhoArquivo(): string
    {
        $arquivo = $this->option('arquivo');

        if (empty($arquivo)) {
            throw new \Exception('Nenhum arquivo informado');
        }

        return base_path("scripts/import_treinamento_json/{$arquivo}.json");
    }

    private function validarArquivo(string $path): void
    {
        if (!file_exists($path)) {
            throw new \Exception("Arquivo {$path} não encontrado");
        }

        $this->info("Arquivo {$path} encontrado");
    }

    private function criarLazyCollectionDoArquivo(string $path): \Illuminate\Support\LazyCollection
    {
        return \Illuminate\Support\LazyCollection::make(function () use ($path) {
            $handle = fopen($path, 'r');

            if ($handle === false) {
                throw new \Exception("Erro ao abrir o arquivo {$path}");
            }

            try {
                // Ler todo o conteúdo para decodificar o JSON
                $content = fread($handle, filesize($path));

                if ($content === false) {
                    throw new \Exception("Erro ao ler o arquivo");
                }

                $data = json_decode($content, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Erro ao decodificar o JSON: ' . json_last_error_msg());
                }

                if (empty($data)) {
                    throw new \Exception('Nenhum dado encontrado no arquivo JSON');
                }

                $this->info("Arquivo JSON carregado. Total de registros: " . count($data));

                // Yield cada item individualmente para processamento lazy
                foreach ($data as $item) {
                    yield $item;
                }
            } finally {
                fclose($handle);
            }
        });
    }

    /**
     * Método alternativo para arquivos JSON muito grandes (NDJSON - newline delimited JSON)
     * Usar este método se o arquivo for NDJSON (um JSON por linha)
     */
    private function criarLazyCollectionNDJSON(string $path): \Illuminate\Support\LazyCollection
    {
        return \Illuminate\Support\LazyCollection::make(function () use ($path) {
            $handle = fopen($path, 'r');

            if ($handle === false) {
                throw new \Exception("Erro ao abrir o arquivo {$path}");
            }

            try {
                $lineNumber = 0;

                while (($line = fgets($handle)) !== false) {
                    $lineNumber++;
                    $line = trim($line);

                    if (empty($line)) {
                        continue;
                    }

                    $data = json_decode($line, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $this->warn("Erro ao decodificar linha {$lineNumber}: " . json_last_error_msg());
                        continue;
                    }

                    yield $data;
                }

                $this->info("Processamento lazy iniciado. Total de linhas: {$lineNumber}");
            } finally {
                fclose($handle);
            }
        });
    }

    // ========================================
    // MÉTODOS DE PROCESSAMENTO
    // ========================================

    private function processarRegistros(\Illuminate\Support\LazyCollection $dadosJson): void
    {
        $chunkSize = (int)$this->option('chunk-size') ?: 1000;
        $this->info("Processando em chunks de {$chunkSize} registros");

        $totalProcessados = 0;
        $totalErros = 0;

        $dadosJson->chunk($chunkSize)->each(function ($chunk) use (&$totalProcessados, &$totalErros) {
            $this->info("=== Processando chunk de {$chunk->count()} registros ===");

            // Pré-carregar feedbacks para o chunk inteiro (otimização)
            $cpfsDoChunk = $chunk->pluck('cpf')->map(function ($cpf) {
                return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
            })->unique()->toArray();

            $this->preCarregarFeedbacks($cpfsDoChunk);

            foreach ($chunk as $index => $dados) {
                try {
                    $dados = $this->prepararDados($dados);
                    $globalIndex = $totalProcessados + $index;

                    $this->info("Processando registro {$globalIndex} - CPF: {$dados['cpf']}");

                    $this->processarRegistroIndividual($dados);
                } catch (\Exception $e) {
                    $totalErros++;
                    $this->exibirErroRegistro($e, $globalIndex, $dados);

                    // Decidir se deve continuar ou parar
                    if ($totalErros > 10) {
                        $this->error("Muitos erros detectados ({$totalErros}). Abortando importação.");
                        throw $e;
                    }

                    $this->warn("Continuando processamento...");
                }
            }

            $totalProcessados += $chunk->count();
            $this->info("Total processado até agora: {$totalProcessados} registros");

            // Limpar cache após cada chunk para economizar memória
            $this->limparCache();

            // Liberar memória após cada chunk
            gc_collect_cycles();
        });

        $this->info("=== Resumo da Importação ===");
        $this->info("Total de registros processados: {$totalProcessados}");
        $this->info("Total de erros: {$totalErros}");

        if ($totalErros > 0) {
            $this->warn("Importação concluída com {$totalErros} erro(s)");
        }
    }

    private function limparCache(): void
    {
        $this->feedbackCache = [];
        $this->treinamentosLimpos = []; // Limpar também o controle de limpeza para o próximo chunk
        // Manter o cache de vencimentos pois é menor e reutilizável
        // $this->vencimentoCache = [];
    }

    private function finalizarSubstituicaoVencimentos(): void
    {
        $this->info("=== Finalizando substituição de vencimentos ===");

        foreach ($this->vencimentosProcessados as $treinamentoId => $vencimentosIds) {
            $this->info("Processando treinamento ID {$treinamentoId}");
            $this->info("Vencimentos que devem ser mantidos: " . implode(', ', $vencimentosIds));

            // Remover todos os vencimentos que NÃO foram processados neste chunk
            $this->removerVencimentosNaoPresentes($treinamentoId, $vencimentosIds);
        }

        // Limpar o array para o próximo chunk
        $this->vencimentosProcessados = [];

        $this->info("=== Substituição de vencimentos finalizada ===");
    }

    private function prepararDados(array $dados): array
    {
        $dados['cpf'] = $this->formatarCpf($dados['cpf']);
        $dados['vencimento_id'] = isset($dados['vencimento_id'])
            ? trim((string)$dados['vencimento_id'])
            : null;
        $dados['segmento_treinamento_id'] = $this->normalizarSegmentoTreinamentoId($dados['segmento_treinamento_id'] ?? null);
        $dados['tipo'] = 'Fixo';
        $dados['empresa_id'] = $this->empresaId;
        $dados['cadastrou'] = $this->empresaId;
        $dados['arquivo_id'] = null;
        $dados['numero_fat'] = null;

        return $dados;
    }

    private function processarRegistroIndividual(array $dados): void
    {
        $feedback = $this->buscarFeedback($dados['cpf']);

        if (!$feedback) {
            $this->error("Nenhum feedback encontrado para o CPF: {$dados['cpf']}");
            $this->registrarResultado($dados, self::STATUS_FALHA, 'Feedback não encontrado');
            return;
        }

        $dados['feedback_id'] = (int)$feedback->feedback_id;

        try {
            $this->atualizarSegmentoTreinamentoAdmissaoSeExistir(
                $dados['feedback_id'],
                $dados['segmento_treinamento_id']
            );

            $dados['vencimento_id'] = $this->obterVencimentoValidoOuFalhar($dados['vencimento_id']);

            // Calcular data de vencimento antes de processar
            $dados = $this->calcularDataVencimento($dados);

            $treinamentoModel = $this->buscarOuCriarTreinamento($dados);
            $this->salvarHistorico($dados['feedback_id'], $treinamentoModel->id, $this->empresaId);

            // Registrar sucesso
            $this->registrarResultado($dados, self::STATUS_SUCESSO, 'Treinamento processado com sucesso', $treinamentoModel);
        } catch (\Exception $e) {
            if ($this->ehExcecaoDeSkip($e)) {
                $mensagem = $this->limparMensagemSkip($e->getMessage());
                $this->warn($mensagem);
                $this->registrarResultado($dados, self::STATUS_SKIP, $mensagem);

                return;
            }

            $this->registrarResultado($dados, self::STATUS_FALHA, $e->getMessage());
            throw $e;
        }
    }

    // ========================================
    // MÉTODOS DE BUSCA
    // ========================================

    private function buscarFeedback(string $cpf): ?object
    {
        // Usar cache para evitar queries repetidas
        if (isset($this->feedbackCache[$cpf])) {
            return $this->feedbackCache[$cpf];
        }

        $feedback = \DB::table('feedback_curriculos')
            ->select([
                'feedback_curriculos.id as feedback_id',
                'curriculos.cpf as cpf',
                'feedback_curriculos.empresa_id'
            ])
            ->join('curriculos', 'curriculos.id', '=', 'feedback_curriculos.curriculo_id')
            ->where('curriculos.cpf', $cpf)
            ->where('feedback_curriculos.empresa_id', $this->empresaId)
            ->first();

        // Armazenar no cache
        $this->feedbackCache[$cpf] = $feedback;

        return $feedback;
    }

    /**
     * Pré-carrega feedbacks em batch para melhor performance
     */
    private function preCarregarFeedbacks(array $cpfs): void
    {
        $this->info("Pré-carregando feedbacks para " . count($cpfs) . " CPFs...");

        $feedbacks = \DB::table('feedback_curriculos')
            ->select([
                'feedback_curriculos.id as feedback_id',
                'curriculos.cpf as cpf',
                'feedback_curriculos.empresa_id'
            ])
            ->join('curriculos', 'curriculos.id', '=', 'feedback_curriculos.curriculo_id')
            ->whereIn('curriculos.cpf', $cpfs)
            ->where('feedback_curriculos.empresa_id', $this->empresaId)
            ->get();

        foreach ($feedbacks as $feedback) {
            $this->feedbackCache[$feedback->cpf] = $feedback;
        }

        $this->info("Feedbacks pré-carregados: " . count($feedbacks));
    }

    private function buscarTreinamentoExistente(array $dados): ?Treinamento
    {
        $this->info("Buscando treinamento existente...");
        $this->info("Dados para busca - Tipo: {$dados['tipo']}, Feedback ID: {$dados['feedback_id']}");

        return Treinamento::where('tipo', $dados['tipo'])
            ->where('feedback_id', $dados['feedback_id'])
            ->first();
    }

    private function buscarVencimento($vencimentoId): ?object
    {
        $vencimentoIdNormalizado = $this->normalizarVencimentoId($vencimentoId);

        if ($vencimentoIdNormalizado === null) {
            return null;
        }

        // Usar cache para vencimentos
        if (isset($this->vencimentoCache[$vencimentoIdNormalizado])) {
            return $this->vencimentoCache[$vencimentoIdNormalizado];
        }

        $vencimento = \DB::table('vencimentos')->where('id', $vencimentoIdNormalizado)->first();

        $this->vencimentoCache[$vencimentoIdNormalizado] = $vencimento;

        return $vencimento;
    }

    // ========================================
    // MÉTODOS DE CRIAÇÃO/ATUALIZAÇÃO
    // ========================================

    private function buscarOuCriarTreinamento(array $dados): Treinamento
    {
        $treinamentoModel = $this->buscarTreinamentoExistente($dados);

        if (!$treinamentoModel) {
            return $this->criarNovoTreinamento($dados);
        }

        return $this->atualizarTreinamentoExistente($treinamentoModel, $dados);
    }

    private function criarNovoTreinamento(array $dados): Treinamento
    {
        $this->info("Treinamento não encontrado, criando novo treinamento para o CPF: {$dados['cpf']}");

        $this->info("Dados para criação: " . json_encode($dados, JSON_PRETTY_PRINT));

        $treinamentoNovo = Treinamento::create($dados);
        $treinamentoModel = Treinamento::find($treinamentoNovo->id);

        $this->info("Treinamento criado com sucesso: ID {$treinamentoModel->id}, Tipo: {$treinamentoModel->tipo}");

        $this->vincularVencimento($treinamentoModel, $dados);

        return $treinamentoModel;
    }

    private function atualizarTreinamentoExistente(Treinamento $treinamentoModel, array $dados): Treinamento
    {
        $this->warn("Treinamento já existe para o CPF: {$dados['cpf']}, ID: {$treinamentoModel->id}");
        $this->info("Dados do treinamento existente: " . json_encode($treinamentoModel->toArray(), JSON_PRETTY_PRINT));

        // Verificar se deve limpar vencimentos antigos (apenas uma vez por treinamento)
        if ($this->option('limpar-vencimentos-antigos') && !isset($this->treinamentosLimpos[$treinamentoModel->id])) {
            $this->info("Limpando TODOS os vencimentos do treinamento ID: {$treinamentoModel->id} (primeira vez)");
            $this->limparTodosVencimentos($treinamentoModel->id);
            $this->treinamentosLimpos[$treinamentoModel->id] = true;
        } elseif ($this->option('limpar-vencimentos-antigos')) {
            $this->info("Vencimentos do treinamento ID: {$treinamentoModel->id} já foram limpos anteriormente");
        }

        $this->atualizarDadosTreinamento($treinamentoModel, $dados);
        $this->processarVencimentosExistentes($treinamentoModel, $dados);

        return $treinamentoModel;
    }

    private function atualizarDadosTreinamento(Treinamento $treinamentoModel, array $dados): void
    {
        $this->info("Atualizando dados do treinamento ID: {$treinamentoModel->id}");

        $dadosAtualizacao = collect($dados)->except([
            'vencimento_id',
            'data_vencimento',
            'data_treinamento'
        ])->toArray();

        $this->info("Dados que serão atualizados: " . json_encode($dadosAtualizacao, JSON_PRETTY_PRINT));

        $treinamentoModel->update($dadosAtualizacao);
        $treinamentoModel->refresh();

        $this->info("Treinamento atualizado com sucesso!");
    }

    // ========================================
    // MÉTODOS DE VENCIMENTO
    // ========================================

    /**
     * ESTRATÉGIAS DE GERENCIAMENTO DE VENCIMENTOS:
     *
     * 1. MODO PADRÃO (sem flags):
     *    - Adiciona novos vencimentos
     *    - Atualiza vencimentos existentes
     *    - MANTÉM vencimentos antigos que não estão no arquivo
     *    Uso: Importação incremental
     *    Exemplo: Treinamento tem [1,2,3], arquivo tem [2,4] → Resultado: [1,2,3,4]
     *
     * 2. --limpar-vencimentos-antigos:
     *    - Remove TODOS os vencimentos NA PRIMEIRA VEZ que processa o treinamento
     *    - Adiciona todos os vencimentos do arquivo para aquele treinamento
     *    - Se o JSON tem 2 registros do mesmo treinamento com vencimentos diferentes,
     *      a limpeza ocorre apenas no primeiro registro, os próximos apenas adicionam
     *    Uso: Reimportação completa
     *    Exemplo: Treinamento tem [1,2,3], arquivo tem [4,5] → Limpa tudo → Resultado: [4,5]
     *
     * 3. --substituir-vencimentos:
     *    - Coleta TODOS os vencimentos do arquivo para cada treinamento durante o chunk
     *    - Ao FINAL do chunk, remove vencimentos que NÃO estão no arquivo
     *    - Mantém/Atualiza os vencimentos do arquivo
     *    Uso: Sincronização com o arquivo (arquivo é a fonte da verdade)
     *    Exemplo: Treinamento tem [1,2,3], arquivo tem [2,4] → Resultado: [2,4]
     *
     * IMPORTANTE: No modo --substituir-vencimentos, se o JSON tem múltiplos registros
     * do mesmo treinamento, TODOS os vencimentos desses registros serão mantidos.
     */

    private function calcularDataVencimento(array $dados): array
    {
        if (!empty($dados['data_vencimento'])) {
            return $dados;
        }

        $vencimento = $this->buscarVencimento($dados['vencimento_id']);

        if (!$vencimento) {
            throw $this->criarExcecaoSkip("Vencimento ID {$dados['vencimento_id']} não encontrado");
        }

        $dataTreinamento = (new DataHora($dados['data_treinamento']))->dataInsert();
        $dataVencimento = new DataHora($dataTreinamento);
        $dataVencimento->addDia($vencimento->prazo_fixo);

        $dados['data_vencimento'] = $dataVencimento->dataInsert();

        return $dados;
    }

    private function vincularVencimento(Treinamento $treinamentoModel, array $dados): void
    {
        $vencimentoId = $this->obterVencimentoValidoOuFalhar($dados['vencimento_id']);

        $this->info("Criando TreinamentoVencimento...");

        $pivotData = $this->prepararDadosPivot($treinamentoModel->id, [
            ...$dados,
            'vencimento_id' => $vencimentoId,
        ]);
        $this->info("Dados do pivot: " . json_encode($pivotData, JSON_PRETTY_PRINT));

        $treinamentoVencimento = TreinamentoVencimento::create($pivotData);
        $this->info("TreinamentoVencimento criado com sucesso: ID {$treinamentoVencimento->id}");
    }

    private function processarVencimentosExistentes(Treinamento $treinamentoModel, array $dados): void
    {
        try {
            $this->info("=== INÍCIO processarVencimentosExistentes ===");
            $this->info("Treinamento ID: {$treinamentoModel->id}");
            $this->info("Vencimento ID do arquivo: {$dados['vencimento_id']}");

            $this->validarVencimentoExiste($dados['vencimento_id']);
            $this->sincronizarVencimento($treinamentoModel, $dados);

            $this->info("=== FIM processarVencimentosExistentes ===");
        } catch (\Exception $e) {
            $this->error("Erro em processarVencimentosExistentes: " . $e->getMessage());
            throw $e;
        }
    }

    private function validarVencimentoExiste($vencimentoId): int
    {
        return $this->obterVencimentoValidoOuFalhar($vencimentoId);
    }

    private function sincronizarVencimento(Treinamento $treinamentoModel, array $dados): void
    {
        $dados = $this->calcularDataVencimento($dados);
        $vencimentoId = $this->obterVencimentoValidoOuFalhar($dados['vencimento_id']);
        $novoVencimentoData = $this->prepararDadosVencimento($dados);

        $this->info("Dados do novo vencimento: " . json_encode($novoVencimentoData, JSON_PRETTY_PRINT));

        $vencimentoJaVinculado = $this->verificarVencimentoVinculado(
            $treinamentoModel->id,
            $vencimentoId
        );

        if ($vencimentoJaVinculado) {
            $this->atualizarVencimentoExistente($treinamentoModel->id, $vencimentoId, $novoVencimentoData);
        } else {
            $this->criarNovoVencimento($treinamentoModel->id, $vencimentoId, $novoVencimentoData);
        }

        // Registrar que este vencimento foi processado para este treinamento
        if ($this->option('substituir-vencimentos')) {
            if (!isset($this->vencimentosProcessados[$treinamentoModel->id])) {
                $this->vencimentosProcessados[$treinamentoModel->id] = [];
            }
            if (!in_array($vencimentoId, $this->vencimentosProcessados[$treinamentoModel->id])) {
                $this->vencimentosProcessados[$treinamentoModel->id][] = $vencimentoId;
            }
        }
    }

    private function verificarVencimentoVinculado(int $treinamentoId, int $vencimentoId): bool
    {
        return \DB::table('treinamento_vencimento')
            ->where('treinamento_id', $treinamentoId)
            ->where('vencimento_id', $vencimentoId)
            ->exists();
    }

    private function atualizarVencimentoExistente(int $treinamentoId, int $vencimentoId, array $dados): void
    {
        $this->info("Vencimento ID {$vencimentoId} já está vinculado. Atualizando dados...");

        \DB::table('treinamento_vencimento')
            ->where('treinamento_id', $treinamentoId)
            ->where('vencimento_id', $vencimentoId)
            ->update($dados);

        $this->info("Dados do vencimento atualizados com sucesso");
    }

    private function criarNovoVencimento(int $treinamentoId, int $vencimentoId, array $dados): void
    {
        $this->info("Vinculando novo vencimento ID {$vencimentoId} ao treinamento");

        \DB::table('treinamento_vencimento')->insert([
            'treinamento_id' => $treinamentoId,
            'vencimento_id' => $vencimentoId,
            ...$dados
        ]);

        $this->info("Vencimento vinculado com sucesso");
    }

    private function removerVencimento(int $treinamentoId, int $vencimentoId): void
    {
        $this->info("Removendo vencimento ID {$vencimentoId} do treinamento ID {$treinamentoId}");

        $deleted = \DB::table('treinamento_vencimento')
            ->where('treinamento_id', $treinamentoId)
            ->where('vencimento_id', $vencimentoId)
            ->delete();

        if ($deleted) {
            $this->info("Vencimento removido com sucesso");
        } else {
            $this->warn("Nenhum vencimento foi removido");
        }
    }

    private function removerVencimentosNaoPresentes(int $treinamentoId, array $vencimentosManterIds): void
    {
        $this->info("Removendo vencimentos não presentes no arquivo para treinamento ID {$treinamentoId}");
        $this->info("Vencimentos que serão mantidos: " . implode(', ', $vencimentosManterIds));

        // Primeiro, listar quais vencimentos serão removidos
        $vencimentosARemover = \DB::table('treinamento_vencimento')
            ->where('treinamento_id', $treinamentoId)
            ->whereNotIn('vencimento_id', $vencimentosManterIds)
            ->pluck('vencimento_id')
            ->toArray();

        if (!empty($vencimentosARemover)) {
            $this->warn("Vencimentos que serão removidos: " . implode(', ', $vencimentosARemover));
        }

        $deleted = \DB::table('treinamento_vencimento')
            ->where('treinamento_id', $treinamentoId)
            ->whereNotIn('vencimento_id', $vencimentosManterIds)
            ->delete();

        if ($deleted > 0) {
            $this->info("{$deleted} vencimento(s) removido(s) com sucesso");
        } else {
            $this->info("Nenhum vencimento antigo para remover");
        }
    }

    private function limparTodosVencimentos(int $treinamentoId): void
    {
        $this->warn("Removendo TODOS os vencimentos do treinamento ID {$treinamentoId}");

        $deleted = \DB::table('treinamento_vencimento')
            ->where('treinamento_id', $treinamentoId)
            ->delete();

        $this->info("{$deleted} vencimento(s) removido(s)");
    }

    private function prepararDadosPivot(int $treinamentoId, array $dados): array
    {
        return [
            'vencimento_id' => $dados['vencimento_id'],
            'treinamento_id' => $treinamentoId,
            'data_vencimento' => $dados['data_vencimento'],
            'data_treinamento' => $dados['data_treinamento'],
            'numero_fat' => $dados['numero_fat'],
            'arquivo_id' => $dados['arquivo_id']
        ];
    }

    private function prepararDadosVencimento(array $dados): array
    {
        return [
            'data_vencimento' => (new DataHora($dados['data_vencimento']))->dataInsert(),
            'data_treinamento' => (new DataHora($dados['data_treinamento']))->dataInsert(),
            'numero_fat' => $dados['numero_fat'],
            'arquivo_id' => $dados['arquivo_id']
        ];
    }

    // ========================================
    // MÉTODOS DE HISTÓRICO
    // ========================================

    private function salvarHistorico(int $feedbackId, int $treinamentoId, int $empresaId): void
    {
        try {
            $this->info("=== INÍCIO salvarHistorico ===");
            $this->info("Salvando histórico - Treinamento: {$treinamentoId}, Feedback: {$feedbackId}, Empresa: {$empresaId}");

            $treinamento = $this->buscarTreinamentoComVencimentos($treinamentoId);

            if (!$treinamento) {
                throw new \Exception("Treinamento ID {$treinamentoId} não encontrado");
            }

            $this->criarRegistroHistorico($feedbackId, $empresaId, $treinamento);

            $this->info("=== FIM salvarHistorico ===");
        } catch (\Exception $e) {
            $this->error("Erro em salvarHistorico: " . $e->getMessage());
            throw $e;
        }
    }

    private function buscarTreinamentoComVencimentos(int $treinamentoId): ?Treinamento
    {
        $this->info("Buscando treinamento com vencimentos...");

        $treinamento = Treinamento::with('Vencimentos')->find($treinamentoId);

        if ($treinamento) {
            $this->info("Treinamento encontrado. Vencimentos: " . $treinamento->Vencimentos->count());
        }

        return $treinamento;
    }

    private function criarRegistroHistorico(int $feedbackId, int $empresaId, Treinamento $treinamento): void
    {
        TreinamentoVencimentoHistorico::create([
            'feedback_id' => $feedbackId,
            'empresa_id' => $empresaId,
            'treinamento_id' => $treinamento->id,
            'user_id' => $empresaId,
            'treinamentos_vencimentos' => $treinamento->Vencimentos
        ]);

        $this->info("Histórico salvo com sucesso");
    }

    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================

    private function formatarCpf(string $cpf): string
    {
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }

    private function normalizarVencimentoId($vencimentoId): ?int
    {
        if ($vencimentoId === null) {
            return null;
        }

        $vencimentoId = trim((string)$vencimentoId);

        if ($vencimentoId === '' || !ctype_digit($vencimentoId)) {
            return null;
        }

        $vencimentoIdNormalizado = (int)$vencimentoId;

        return $vencimentoIdNormalizado > 0 ? $vencimentoIdNormalizado : null;
    }

    private function normalizarSegmentoTreinamentoId($segmentoTreinamentoId): ?int
    {
        if ($segmentoTreinamentoId === null) {
            return null;
        }

        $segmentoTreinamentoId = trim((string)$segmentoTreinamentoId);

        if ($segmentoTreinamentoId === '' || !ctype_digit($segmentoTreinamentoId)) {
            return null;
        }

        $segmentoTreinamentoIdNormalizado = (int)$segmentoTreinamentoId;

        return $segmentoTreinamentoIdNormalizado > 0 ? $segmentoTreinamentoIdNormalizado : null;
    }

    private function atualizarSegmentoTreinamentoAdmissaoSeExistir(int $feedbackId, ?int $segmentoTreinamentoId): void
    {
        if ($segmentoTreinamentoId === null) {
            return;
        }

        $admissao = \DB::table('admissoes')
            ->select(['id', 'segmento_treinamento_id'])
            ->where('feedback_id', $feedbackId)
            ->whereNull('deleted_at')
            ->first();

        if (!$admissao) {
            return;
        }

        if ((int)$admissao->segmento_treinamento_id === $segmentoTreinamentoId) {
            return;
        }

        \DB::table('admissoes')
            ->where('id', $admissao->id)
            ->update(['segmento_treinamento_id' => $segmentoTreinamentoId]);

        $this->info("Admissão {$admissao->id} atualizada com segmento_treinamento_id {$segmentoTreinamentoId}");
    }

    private function obterVencimentoValidoOuFalhar($vencimentoId): int
    {
        $vencimentoIdNormalizado = $this->normalizarVencimentoId($vencimentoId);

        if ($vencimentoIdNormalizado === null) {
            throw $this->criarExcecaoSkip("Vencimento ID {$vencimentoId} inválido");
        }

        $vencimento = $this->buscarVencimento($vencimentoIdNormalizado);

        if (!$vencimento) {
            throw $this->criarExcecaoSkip("Vencimento ID {$vencimentoIdNormalizado} não encontrado");
        }

        return $vencimentoIdNormalizado;
    }

    private function criarExcecaoSkip(string $mensagem): \RuntimeException
    {
        return new \RuntimeException(self::SKIP_PREFIX . $mensagem);
    }

    private function ehExcecaoDeSkip(\Throwable $exception): bool
    {
        return str_starts_with($exception->getMessage(), self::SKIP_PREFIX);
    }

    private function limparMensagemSkip(string $mensagem): string
    {
        return str_starts_with($mensagem, self::SKIP_PREFIX)
            ? substr($mensagem, strlen(self::SKIP_PREFIX))
            : $mensagem;
    }

    // ========================================
    // MÉTODOS DE TRATAMENTO DE ERROS
    // ========================================

    private function exibirErroRegistro(\Exception $e, int $index, array $dados): void
    {
        $this->error("Erro no registro {$index} (CPF: {$dados['cpf']}): " . $e->getMessage());
        $this->error("Stack trace: " . $e->getTraceAsString());

        $this->exibirUltimasQueries(5);
    }

    private function exibirErroGeral(\Exception $e): void
    {
        $this->error('Erro ao executar comando: ' . $e->getMessage());
        $this->error('Stack trace: ' . $e->getTraceAsString());

        $this->exibirTodasQueries();
    }

    private function exibirUltimasQueries(int $quantidade): void
    {
        $queries = \DB::getQueryLog();
        $this->error("Últimas {$quantidade} queries executadas:");

        foreach (array_slice($queries, -$quantidade) as $query) {
            $this->error("SQL: {$query['query']}");
            $this->error("Bindings: " . json_encode($query['bindings']));
        }
    }

    private function exibirTodasQueries(): void
    {
        $queries = \DB::getQueryLog();
        $this->error("Queries executadas:");

        foreach ($queries as $query) {
            $this->error("SQL: {$query['query']}");
            $this->error("Bindings: " . json_encode($query['bindings']));
        }
    }

    // ========================================
    // MÉTODOS DE RELATÓRIO E E-MAIL
    // ========================================

    private function registrarResultado(array $dados, string $status, string $mensagem, ?Treinamento $treinamento = null): void
    {
        // Buscar nome do currículo
        $nome = 'Não encontrado';
        if (isset($dados['cpf'])) {
            $curriculo = \DB::table('curriculos')
                ->select('nome')
                ->where('cpf', $dados['cpf'])
                ->first();

            if ($curriculo) {
                $nome = $curriculo->nome;
            }
        }

        // Buscar label do vencimento
        $labelVencimento = 'N/A';
        if (isset($dados['vencimento_id'])) {
            $vencimento = $this->buscarVencimento($dados['vencimento_id']);
            if ($vencimento && isset($vencimento->label)) {
                $labelVencimento = $vencimento->label;
            } elseif (!empty($dados['tipo'])) {
                $labelVencimento = $dados['tipo'];
            }
        }

        // Data de vencimento já foi calculada em processarRegistroIndividual se necessário
        $dataVencimento = $dados['data_vencimento'] ?? 'N/A';

        $resultado = [
            'nome' => $nome,
            'cpf' => $dados['cpf'] ?? 'N/A',
            'treinamento' => $labelVencimento, // Label do vencimento ao invés do tipo
            'data_treinamento' => $dados['data_treinamento'] ?? 'N/A',
            'data_vencimento' => $dataVencimento, // Data já calculada
            'status' => $status,
            'mensagem' => $mensagem,
            'treinamento_id' => $treinamento ? $treinamento->id : null,
        ];

        $this->resultadosImportacao[] = $resultado;
    }

    private function gerarRelatorioCSV(): string
    {
        $this->info("=== Gerando Relatório CSV ===");

        $timestamp = date('Y-m-d_H-i-s');
        $nomeArquivo = "relatorio_importacao_treinamento_{$this->empresaId}_{$timestamp}.csv";
        $caminhoCompleto = storage_path("app/relatorios/{$nomeArquivo}");

        // Criar diretório se não existir
        $diretorio = dirname($caminhoCompleto);
        if (!is_dir($diretorio)) {
            mkdir($diretorio, 0755, true);
        }

        $arquivo = fopen($caminhoCompleto, 'w');

        if ($arquivo === false) {
            throw new \Exception("Não foi possível criar o arquivo CSV");
        }

        // Adicionar BOM para UTF-8 (Excel compatível)
        fprintf($arquivo, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Cabeçalhos
        fputcsv($arquivo, [
            'Nome',
            'CPF',
            'Treinamento',
            'Data Treinamento',
            'Data Vencimento',
            'Status',
            'Mensagem',
            'ID Treinamento'
        ], ';');

        // Dados
        foreach ($this->resultadosImportacao as $resultado) {
            fputcsv($arquivo, [
                $resultado['nome'],
                $resultado['cpf'],
                $resultado['treinamento'],
                $resultado['data_treinamento'],
                $resultado['data_vencimento'],
                $resultado['status'],
                $resultado['mensagem'],
                $resultado['treinamento_id'] ?? ''
            ], ';');
        }

        fclose($arquivo);

        // Estatísticas
        $totalRegistros = count($this->resultadosImportacao);
        $sucessos = count(array_filter($this->resultadosImportacao, fn($r) => $r['status'] === self::STATUS_SUCESSO));
        $falhas = count(array_filter($this->resultadosImportacao, fn($r) => $r['status'] === self::STATUS_FALHA));
        $skips = count(array_filter($this->resultadosImportacao, fn($r) => $r['status'] === self::STATUS_SKIP));

        $this->info("Relatório gerado com sucesso!");
        $this->info("Total de registros: {$totalRegistros}");
        $this->info("Sucessos: {$sucessos}");
        $this->info("Falhas: {$falhas}");
        $this->info("Skips: {$skips}");
        $this->info("Arquivo: {$caminhoCompleto}");

        return $caminhoCompleto;
    }

    private function enviarRelatorioEmail(string $caminhoCSV, bool $comErro = false): void
    {
        $this->info("=== Enviando Relatório por E-mail ===");

        $emailDestino = $this->option('enviar-email');

        if (empty($emailDestino)) {
            $this->error("Nenhum e-mail de destino informado");
            return;
        }

        // Validar e-mail
        if (!filter_var($emailDestino, FILTER_VALIDATE_EMAIL)) {
            $this->error("E-mail inválido: {$emailDestino}");
            return;
        }

        try {
            // Estatísticas para o e-mail
            $totalRegistros = count($this->resultadosImportacao);
            $sucessos = count(array_filter($this->resultadosImportacao, fn($r) => $r['status'] === self::STATUS_SUCESSO));
            $falhas = count(array_filter($this->resultadosImportacao, fn($r) => $r['status'] === self::STATUS_FALHA));
            $skips = count(array_filter($this->resultadosImportacao, fn($r) => $r['status'] === self::STATUS_SKIP));

            $dados = [
                'empresa_id' => $this->empresaId,
                'arquivo' => $this->option('arquivo'),
                'total_registros' => $totalRegistros,
                'sucessos' => $sucessos,
                'falhas' => $falhas,
                'skips' => $skips,
                'data_processamento' => date('d/m/Y H:i:s'),
                'com_erro' => $comErro,
                'caminho_csv' => $caminhoCSV
            ];

            // Enviar e-mail usando Laravel Mail
            \Mail::send('email.relatorio-importacao-treinamento', $dados, function ($message) use ($emailDestino, $caminhoCSV, $comErro) {
                $message->to($emailDestino);

                // Adicionar cópias se informado
                $emailsCC = $this->option('email-cc');
                if (!empty($emailsCC)) {
                    $ccs = array_map('trim', explode(',', $emailsCC));
                    foreach ($ccs as $cc) {
                        if (filter_var($cc, FILTER_VALIDATE_EMAIL)) {
                            $message->cc($cc);
                        }
                    }
                }

                $assunto = $comErro
                    ? 'Relatório de Importação de Treinamentos - COM ERROS'
                    : 'Relatório de Importação de Treinamentos - CONCLUÍDO';

                $message->subject($assunto);
                $message->attach($caminhoCSV, [
                    'as' => basename($caminhoCSV),
                    'mime' => 'text/csv'
                ]);
            });

            $this->info("E-mail enviado com sucesso para: {$emailDestino}");
        } catch (\Exception $e) {
            $this->error("Erro ao enviar e-mail: " . $e->getMessage());
            $this->error("O relatório CSV foi salvo em: {$caminhoCSV}");
        }
    }
}
