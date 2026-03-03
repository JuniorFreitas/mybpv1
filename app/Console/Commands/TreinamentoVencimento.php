<?php

namespace App\Console\Commands;

use App\Models\Admissao;
use App\Models\Cliente;
use App\Models\SegmentoTreinamento;
use App\Models\Sistema;
use App\Models\TipoRecebeEmail;
use App\Models\Treinamento;
use App\Models\User;
use App\Models\Vencimento;
use App\Services\Treinamento\FeedbackCurriculoFilter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MasterTag\DataHora;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Settings;

class TreinamentoVencimento extends Command
{
    protected $signature = 'mybp:treinamento-vencimento {--id=} {--all} {--force} {--lote-size=100} {--delay=1} {--chunk-size=1000}';
    protected $description = 'Verifica treinamentos vencidos e próximos a vencer e envia e-mail para usuários configurados';

    // Constantes para melhor manutenibilidade
    private const DIAS_ALERTA = 45;
    private const DIAS_PROXIMO = 30;
    private const DIAS_ATENCAO = 60;

    private const CATEGORIAS = [
        'VENCIDO' => 'VENCIDO',
        'PROXIMO' => 'PROXIMO',
        'ATENCAO' => 'ATENCAO',
        'REGULAR' => 'REGULAR'
    ];

    // Configurações de chunk e lote
    private const CHUNK_SIZE_DEFAULT = 1000;
    private const LOTE_SIZE_DEFAULT = 100;
    private const DELAY_DEFAULT = 1; // segundos entre lotes
    private const MEMORY_LIMIT_MB = 256; // Limite de memória em MB para limpeza

    public function handle(): int
    {
        $this->info('Iniciando verificação de treinamentos...');
        $this->info("Ambiente: Docker PHP 8.2 | Memória inicial: " . $this->formatBytes(memory_get_usage(true)));

        // Configurar limites de memória
        ini_set('memory_limit', '1024M');
        gc_enable();

        $empresas = $this->buscarEmpresas();
        $this->info("Total de empresas encontradas: {$empresas->count()}");

        $tempoInicio = microtime(true);
        $empresasProcessadas = 0;

        foreach ($empresas as $empresa) {
            $this->processarEmpresa($empresa);
            $empresasProcessadas++;

            // Limpeza de memória estratégica
            $this->limparMemoriaEstrategica($empresasProcessadas);
        }

        $tempoTotal = microtime(true) - $tempoInicio;
        $this->exibirEstatisticasFinaisComando($empresasProcessadas, $tempoTotal);

        return 0;
    }

    private function limparMemoriaEstrategica(int $contador): void
    {
        // Limpeza a cada empresa
        if ($contador % 1 === 0) {
            $memoryBefore = memory_get_usage(true);

            // Força coleta de lixo
            gc_collect_cycles();

            $memoryAfter = memory_get_usage(true);
            $memoriaLiberada = $memoryBefore - $memoryAfter;

            $this->info("Memória atual: " . $this->formatBytes($memoryAfter) .
                " | Liberada: " . $this->formatBytes($memoriaLiberada));
        }

        // Limpeza mais agressiva a cada 5 empresas
        if ($contador % 5 === 0) {
            $this->info("Executando limpeza profunda de memória...");

            // Múltiplas passadas de coleta de lixo
            for ($i = 0; $i < 3; $i++) {
                gc_collect_cycles();
            }

            // Verifica se precisa de limpeza adicional
            $memoryUsageMB = memory_get_usage(true) / 1024 / 1024;
            if ($memoryUsageMB > self::MEMORY_LIMIT_MB) {
                $this->warn("Uso de memória alto: {$memoryUsageMB}MB - Executando limpeza adicional");

                // Força limpeza adicional
                if (function_exists('gc_mem_caches')) {
                    gc_mem_caches();
                }
            }
        }
    }

    private function buscarEmpresas(): \Illuminate\Database\Eloquent\Collection
    {
        if ($this->option('id')) {
            return Cliente::withoutGlobalScopes()
                ->whereAtivo(true)
                ->whereId($this->option('id'))
                ->select(['id', 'razao_social', 'cnpj', 'apelido',
                    'logradouro', 'bairro', 'cep', 'numero', 'complemento', 'municipio', 'uf'
                ])
                ->with(['Filiais', 'ClienteConfig'])
                ->get();
        }

        if (!$this->option('all')) {
            $empresasIds = Sistema::listaEmpresasParaScheduleTreinamentoVencimento();
            if (empty($empresasIds)) {
                return collect();
            }
            return Cliente::withoutGlobalScopes()
                ->whereAtivo(true)
                ->whereIn('id', $empresasIds)
                ->select(['id', 'razao_social', 'cnpj', 'apelido',
                    'logradouro', 'bairro', 'cep', 'numero', 'complemento', 'municipio', 'uf'
                ])
                ->with(['Filiais', 'ClienteConfig'])
                ->get();
        }

        return Cliente::withoutGlobalScopes()
            ->whereAtivo(true)
            ->select(['id', 'razao_social', 'cnpj', 'apelido',
                'logradouro', 'bairro', 'cep', 'numero', 'complemento', 'municipio', 'uf'
            ])
            ->with(['Filiais', 'ClienteConfig'])
            ->get();
    }

    private function processarEmpresa($empresa): void
    {
        $this->info("Processando empresa: {$empresa->razao_social}");
        $memoryInicial = memory_get_usage(true);

        $usuariosEmail = $this->buscarUsuariosEmail($empresa->id);
        $this->info("Usuários para receber e-mail: {$usuariosEmail->count()}");

        if ($usuariosEmail->isEmpty()) {
            $this->info('Nenhum usuário configurado para receber e-mail. Pulando...');
            return;
        }

        $vencimentos = $this->buscarVencimentos($empresa->id);

        // Processamento em chunks para otimizar memória
        $treinamentosAgrupados = $this->processarTreinamentosEmChunks($empresa->id, $vencimentos);

        if ($this->naoHaTreinamentosAlerta($treinamentosAgrupados)) {
            $this->info('Nenhum treinamento vencido ou próximo a vencer encontrado. Pulando...');
            return;
        }

        $this->exibirResumo($treinamentosAgrupados);
        $this->enviarNotificacoesPorLotes($empresa, $usuariosEmail, $treinamentosAgrupados);

        // Limpeza final da empresa
        $memoryFinal = memory_get_usage(true);
        $memoryUsed = $memoryFinal - $memoryInicial;

        $this->info("Processamento concluído para empresa: {$empresa->razao_social}");
        $this->info("Memória utilizada nesta empresa: " . $this->formatBytes($memoryUsed));

        // Força limpeza após cada empresa
        gc_collect_cycles();
    }

    private function processarTreinamentosEmChunks($empresaId, $vencimentos): array
    {
        $this->info("Iniciando processamento usando mesma lógica do export...");

        $chunkSize = (int)$this->option('chunk-size') ?: self::CHUNK_SIZE_DEFAULT;
        $this->info("Tamanho do chunk: {$chunkSize}");

        $treinamentosAgrupados = [
            self::CATEGORIAS['VENCIDO'] => [],
            self::CATEGORIAS['PROXIMO'] => [],
            self::CATEGORIAS['ATENCAO'] => []
        ];

        try {
            // Fazer login temporário de um usuário da empresa para os scopes funcionarem
            $usuarioTemp = User::withoutGlobalScopes()
                ->where('empresa_id', $empresaId)
                ->where('ativo', true)
                ->whereNotNull('login')
                ->first();

            if ($usuarioTemp) {
                $this->info("Fazendo login temporário do usuário: {$usuarioTemp->nome} (ID: {$usuarioTemp->id})");
                \Auth::login($usuarioTemp);
            } else {
                $this->warn("Nenhum usuário encontrado para login temporário. Continuando sem autenticação...");
            }

            // Buscar feedbacks com admissão, currículo e treinamentos
            // Usando a mesma base do export de treinamentos
            $query = \App\Models\FeedbackCurriculo::select([
                    'id', 'curriculo_id', 'telefone_id', 'vaga_id', 'vagas_abertas_id', 'vaga_projeto_id', 'empresa_id'
                ])
                ->with([
                    'Curriculo:id,nome,cpf,nascimento,pcd,uf_vaga,email,rg,orgao_expeditor',
                    'Admissao' => function($query) {
                        $query->where('status', \App\Models\Admissao::STATUS_ADMISSAO_ADMITIDO)
                              ->with('CentroCusto:id,label')
                              ->with('SegmentoTreinamento:id,nome,slug');
                    },
                    'Treinamento:id,cadastrou,feedback_id,tipo,created_at,updated_at',
                    'Treinamento.Vencimentos',
                ])
                ->whereHas('Admissao', function($q) {
                    $q->where('status', \App\Models\Admissao::STATUS_ADMISSAO_ADMITIDO);
                })
                ->where('empresa_id', $empresaId);

            $totalRegistros = $query->count();
            $this->info("Total de registros encontrados: {$totalRegistros}");

            if ($totalRegistros === 0) {
                $this->info("Nenhum registro encontrado para processar.");
                return $treinamentosAgrupados;
            }

            $totalProcessados = 0;
            $chunkAtual = 0;

            // Processar em chunks
            $query->chunk($chunkSize, function ($feedbacks) use (&$treinamentosAgrupados, &$totalProcessados, &$chunkAtual, $vencimentos) {
                $chunkAtual++;
                $this->info("Processando chunk {$chunkAtual} com {$feedbacks->count()} registros...");

                $memoryChunkInicio = memory_get_usage(true);

                foreach ($feedbacks as $feedback) {
                    // Verificar se tem admissão
                    if (!$feedback->Admissao) {
                        continue;
                    }

                    // Verificar se tem treinamentos
                    if (!$feedback->Treinamento || !$feedback->Treinamento->Vencimentos) {
                        continue;
                    }

                    $segmentoId = $feedback->Admissao->segmento_treinamento_id ?? SegmentoTreinamento::getIdAlumar();
                    $segmentoNome = $feedback->Admissao && $feedback->Admissao->SegmentoTreinamento
                        ? $feedback->Admissao->SegmentoTreinamento->nome
                        : '--';
                    // Processar cada vencimento do treinamento
                    foreach ($feedback->Treinamento->Vencimentos as $vencimento) {
                        if ($segmentoId && $vencimento->segmento_treinamento_id !== null && (int) $vencimento->segmento_treinamento_id !== (int) $segmentoId) {
                            continue;
                        }
                        // Buscar dados do treinamento_vencimento
                        $treinamentoVencimento = \DB::table('treinamento_vencimento')
                            ->where('treinamento_id', $feedback->Treinamento->id)
                            ->where('vencimento_id', $vencimento->id)
                            ->first();

                        if (!$treinamentoVencimento) {
                            continue;
                        }

                        // Criar objeto similar ao método antigo
                        $item = (object) [
                            'id' => $feedback->Treinamento->id,
                            'feedback_id' => $feedback->id,
                            'treinamento_id' => $feedback->Treinamento->id,
                            'vencimento_id' => $vencimento->id,
                            'data_treinamento' => $treinamentoVencimento->data_treinamento,
                            'data_vencimento' => $treinamentoVencimento->data_vencimento,
                            'numero_fat' => $treinamentoVencimento->numero_fat ?? null,
                            'segmento_nome' => $segmentoNome,
                        ];

                        // Classificar o treinamento
                        $this->classificarTreinamentoItem($item, $vencimentos);

                        // Filtrar apenas os que precisam de alerta
                        if ($item->dias_vencer <= self::DIAS_ALERTA) {
                            $this->adicionarTreinamentoAoGrupo($treinamentosAgrupados, $item, $feedback);
                        }
                    }
                }

                $totalProcessados += $feedbacks->count();

                // Limpeza de memória do chunk
                unset($feedbacks);
                gc_collect_cycles();

                $memoryChunkFim = memory_get_usage(true);
                $memoryChunkUsada = $memoryChunkFim - $memoryChunkInicio;

                $this->info("Chunk {$chunkAtual} processado. Memória utilizada: " . $this->formatBytes($memoryChunkUsada));
                $this->info("Total processados: {$totalProcessados}");
            });

            $this->info("Processamento concluído. Total de registros processados: {$totalProcessados}");

        } catch (\Exception $e) {
            $this->error("Erro no processamento: {$e->getMessage()}");
            $this->error("Stack trace: {$e->getTraceAsString()}");
        } finally {
            // Limpar autenticação temporária
            \Auth::logout();
        }

        return $treinamentosAgrupados;
    }

    /**
     * Classifica um item individual de treinamento
     */
    private function classificarTreinamentoItem($item, $vencimentos): void
    {
        $hoje = new DataHora();
        $dataAtual = $hoje->dataInsert();

        $item->dias_vencer = $this->calcularDiasParaVencer($item->data_vencimento, $dataAtual);
        $item->vencimento_nome = $this->obterNomeVencimento($item->vencimento_id, $vencimentos);
        $this->definirCategoriaEStatus($item);
    }

    /**
     * Adiciona um treinamento ao grupo apropriado
     */
    private function adicionarTreinamentoAoGrupo(&$treinamentosAgrupados, $item, $feedback): void
    {
        if ($item->categoria === self::CATEGORIAS['REGULAR']) {
            return;
        }

        $feedbackId = $feedback->id;
        $categoria = $item->categoria;

        if (!isset($treinamentosAgrupados[$categoria][$feedbackId])) {
            $treinamentosAgrupados[$categoria][$feedbackId] = $this->criarGrupoFuncionarioFromFeedback($feedback);
        }

        $treinamentosAgrupados[$categoria][$feedbackId]['treinamentos'][] = $this->extrairDadosTreinamento($item);
    }

    /**
     * Cria grupo de funcionário a partir do feedback
     */
    private function criarGrupoFuncionarioFromFeedback($feedback): array
    {
        $admissao = $feedback->Admissao;
        $curriculo = $feedback->Curriculo;

        // Buscar centro de custo
        $centroCusto = \App\Models\CentroCusto::find($admissao->centro_custo_id);
        $centroCustoLabel = $centroCusto ? $centroCusto->label : 'N/A';

        $segmentoNome = $admissao && $admissao->SegmentoTreinamento
            ? $admissao->SegmentoTreinamento->nome
            : '--';

        return [
            'funcionario' => [
                'nome' => $curriculo->nome,
                'cargo' => $admissao->cargo,
                'data_admissao' => $admissao->data_admissao,
                'admissao_id' => $admissao->id,
                'funcao' => $admissao->funcao,
                'centro_custo_id' => $admissao->centro_custo_id,
                'centro_custo_label' => $centroCustoLabel,
                'centro_custo_filial' => Sistema::getFilial($feedback->empresa_id, $admissao->centro_custo_filial_id) ?: null,
                'filial' => $admissao->filial,
                'numero_cracha' => $admissao->numero_cracha,
                'matricula' => $admissao->matricula,
                'curriculo_id' => $curriculo->id,
                'cpf' => $curriculo->cpf,
                'empresa_id' => $feedback->empresa_id,
                'feedback_id' => $feedback->id,
                'cnpj_lotacao' => Sistema::getEmpresaFilialMatriz($admissao->centro_custo_filial_id, $feedback->empresa_id) ?? null,
                'segmento' => $segmentoNome,
                'segmento_id' => $admissao->segmento_treinamento_id,
            ],
            'treinamentos' => []
        ];
    }

    private function buscarUsuariosEmail(int $empresaId): \Illuminate\Database\Eloquent\Collection
    {
        $idTipoRecebeEmail = TipoRecebeEmail::whereNome(TipoRecebeEmail::VENCIMENTO_TREINAMENTO)->first()->id;

        return User::withoutGlobalScopes()
            ->join('user_recebe_email as ure', 'ure.user_id', '=', 'users.id')
            ->whereEmpresaId($empresaId)
            ->where('users.ativo', true)
            ->where('ure.tipo_email_id', $idTipoRecebeEmail)
            ->where('ure.ativo', true)
            ->select(['users.id', 'users.nome', 'login as email', 'users.empresa_id'])
            ->get();
    }

    private function buscarVencimentos(int $empresaId): \Illuminate\Database\Eloquent\Collection
    {
        return Vencimento::withoutGlobalScopes()
            ->whereEmpresaId($empresaId)
            ->whereAtivo(true)
            ->select(['id', 'label', 'descricao', 'ativo', 'label_reduzida', 'exibir_na_carteira'])
            ->get()
            ->keyBy('id');
    }

    private function calcularDiasParaVencer($dataVencimento, string $dataAtual)
    {
        if (!$dataVencimento) {
            return PHP_INT_MAX;
        }

        $dataVencimentoComHora = (new DataHora($dataVencimento))->dataInsert() . ' 23:59:59';
        return DataHora::diferencaDias($dataAtual . ' 00:00:00', $dataVencimentoComHora);
    }

    private function obterNomeVencimento($vencimentoId, $vencimentos)
    {
        $vencimento = $vencimentos->get($vencimentoId);
        return $vencimento ? $vencimento->label : "Vencimento #{$vencimentoId}";
    }

    private function definirCategoriaEStatus($item): void
    {
        if ($item->dias_vencer < 0) {
            $item->categoria = self::CATEGORIAS['VENCIDO'];
            $item->status_csv = 'VENCIDO';
            $item->status_texto = 'Vencido há ' . abs($item->dias_vencer) . ' dia(s)';
            $item->prioridade = abs($item->dias_vencer);
        } elseif ($item->dias_vencer <= self::DIAS_PROXIMO) {
            $item->categoria = self::CATEGORIAS['PROXIMO'];
            $item->status_csv = 'VENCENDO';
            $item->status_texto = "Vence em {$item->dias_vencer} dia(s)";
            $item->prioridade = self::DIAS_PROXIMO - $item->dias_vencer;
        } elseif ($item->dias_vencer <= self::DIAS_ATENCAO) {
            $item->categoria = self::CATEGORIAS['ATENCAO'];
            $item->status_csv = 'PROXIMO A VENCER';
            $item->status_texto = "Vence em {$item->dias_vencer} dia(s)";
            $item->prioridade = 10;
        } else {
            $item->categoria = self::CATEGORIAS['REGULAR'];
            $item->status_csv = 'EM DIA';
            $item->status_texto = 'Em dia';
            $item->prioridade = 0;
        }
    }

    private function extrairDadosTreinamento($treinamento): array
    {
        return [
            'id' => $treinamento->id,
            'vencimento_id' => $treinamento->vencimento_id,
            'vencimento_nome' => $treinamento->vencimento_nome,
            'segmento' => $treinamento->segmento_nome ?? '--',
            'data_treinamento' => $treinamento->data_treinamento,
            'data_vencimento' => $treinamento->data_vencimento,
            'dias_vencer' => $treinamento->dias_vencer,
            'status_texto' => $treinamento->status_texto,
            'status_label' => $treinamento->status_csv,
            'prioridade' => $treinamento->prioridade
        ];
    }

    private function naoHaTreinamentosAlerta($treinamentosAgrupados): bool
    {
        return empty($treinamentosAgrupados[self::CATEGORIAS['VENCIDO']]) &&
            empty($treinamentosAgrupados[self::CATEGORIAS['PROXIMO']]) &&
            empty($treinamentosAgrupados[self::CATEGORIAS['ATENCAO']]);
    }

    private function exibirResumo($treinamentosAgrupados): void
    {
        $this->info("\n=== RESUMO DE TREINAMENTOS ===");

        $this->exibirCategoria('TREINAMENTOS VENCIDOS', $treinamentosAgrupados[self::CATEGORIAS['VENCIDO']]);
        $this->exibirCategoria('TREINAMENTOS PRÓXIMOS A VENCER (30 DIAS)', $treinamentosAgrupados[self::CATEGORIAS['PROXIMO']]);
        $this->exibirCategoria('TREINAMENTOS EM ATENÇÃO (60 DIAS)', $treinamentosAgrupados[self::CATEGORIAS['ATENCAO']]);
    }

    private function exibirCategoria($titulo, $funcionarios): void
    {
        $this->info("\n{$titulo}: " . count($funcionarios));

        foreach ($funcionarios as $dados) {
            $this->info("\n{$dados['funcionario']['nome']} - {$dados['funcionario']['cargo']}");

            foreach ($dados['treinamentos'] as $treinamento) {
                $dataFormatada = date('d/m/Y', strtotime($treinamento['data_vencimento']));
                $this->info("  - {$treinamento['vencimento_nome']}: {$dataFormatada} ({$treinamento['status_texto']})");
            }
        }
    }

    /**
     * Envia notificações por lotes para otimizar com SendGrid
     */
    private function enviarNotificacoesPorLotes($empresa, $usuariosEmail, $treinamentosAgrupados): void
    {
        $this->info("\n=== INICIANDO ENVIO POR LOTES ===");
        $this->info("Total de usuários: {$usuariosEmail->count()}");

        // Upload único do arquivo Excel para S3
        $arquivoS3 = $this->criarEUploadExcelS3($empresa, $treinamentosAgrupados);

        if (!$arquivoS3) {
            $this->error('Falha ao criar arquivo Excel S3. Abortando envio de e-mails.');
            return;
        }

        $dadosEmail = $this->prepararDadosEmail($empresa, $treinamentosAgrupados, $arquivoS3);

        // Verificar template de email
        $this->verificarTemplateEmail();

        // Configurações de lote
        $tamanhoLote = (int)$this->option('lote-size') ?: self::LOTE_SIZE_DEFAULT;
        $delaySegundos = (int)$this->option('delay') ?: self::DELAY_DEFAULT;

        $this->info("Configuração de lotes:");
        $this->info("- Tamanho do lote: {$tamanhoLote} destinatários");
        $this->info("- Delay entre lotes: {$delaySegundos} segundo(s)");

        // Dividir usuários em lotes
        $usuariosArray = $usuariosEmail->toArray();
        $lotes = array_chunk($usuariosArray, $tamanhoLote);
        $totalLotes = count($lotes);

        $this->info("Total de lotes a processar: {$totalLotes}");

        // Estatísticas de envio
        $estatisticas = [
            'lotes_processados' => 0,
            'emails_enviados' => 0,
            'erros' => 0,
            'tempo_inicio' => microtime(true)
        ];

        // Processar cada lote
        foreach ($lotes as $indiceLote => $loteUsuarios) {
            $numeroLote = $indiceLote + 1;
            $quantidadeUsuarios = count($loteUsuarios);

            $this->info("\n--- PROCESSANDO LOTE {$numeroLote}/{$totalLotes} ---");
            $this->info("Usuários no lote: {$quantidadeUsuarios}");

            try {
                $resultado = $this->enviarEmailParaLote($loteUsuarios, $empresa, $dadosEmail, $numeroLote);

                if ($resultado['sucesso']) {
                    $estatisticas['emails_enviados'] += $quantidadeUsuarios;
                    $this->info("✓ Lote {$numeroLote} enviado com sucesso!");
                } else {
                    $estatisticas['erros']++;
                    $this->error("✗ Erro no lote {$numeroLote}: {$resultado['erro']}");
                }

                $estatisticas['lotes_processados']++;

                // Limpeza de memória entre lotes
                gc_collect_cycles();

                // Delay entre lotes (exceto no último)
                if ($numeroLote < $totalLotes && $delaySegundos > 0) {
                    $this->info("Aguardando {$delaySegundos} segundo(s) antes do próximo lote...");
                    sleep($delaySegundos);
                }

            } catch (\Exception $e) {
                $estatisticas['erros']++;
                $this->error("✗ Erro crítico no lote {$numeroLote}: {$e->getMessage()}");

                // Em caso de erro crítico, decidir se continua ou para
                if ($this->deveInterromperPorErro($e)) {
                    $this->error("Interrompendo processamento devido a erro crítico.");
                    break;
                }
            }
        }

        $this->exibirEstatisticasFinais($estatisticas, $empresa);

        // Limpeza em caso de erro total
        if ($estatisticas['emails_enviados'] === 0 && isset($arquivoS3['caminho_s3'])) {
            $this->limparArquivoS3($arquivoS3['caminho_s3']);
        }
    }

    /**
     * Verifica se template de email existe e informa sobre compatibilidade
     */
    private function verificarTemplateEmail(): void
    {
        $templateUsado = $this->obterTemplateEmail();

        if (str_contains($templateUsado, 'excel')) {
            $this->info("✓ Template específico para Excel encontrado");
        } else {
            $this->info("ℹ Usando template padrão - dados do Excel serão adaptados automaticamente");
            $this->info("📄 Para melhor experiência, considere criar o template: email.treinamento.vencendo_excel_s3");
        }
    }

    /**
     * Envia email para um lote específico de usuários
     */
    private function enviarEmailParaLote($loteUsuarios, $empresa, $dadosEmail, $numeroLote): array
    {
        try {
            // Preparar destinatários
            $usuarioPrincipal = $loteUsuarios[0];
            $usuariosCopia = array_slice($loteUsuarios, 1);

            $this->info("Destinatário principal: {$usuarioPrincipal['email']}");

            if (!empty($usuariosCopia)) {
                $emailsCopia = array_column($usuariosCopia, 'email');
                $this->info("Cópias (BCC): " . implode(', ', array_slice($emailsCopia, 0, 3)) .
                    (count($emailsCopia) > 3 ? ' e mais ' . (count($emailsCopia) - 3) : ''));
            }

            // Verificar se view personalizada existe, senão usar a padrão
            $viewTemplate = $this->obterTemplateEmail();

            // Enviar email
            Mail::send(
                ['html' => $viewTemplate],
                $dadosEmail,
                function ($m) use ($usuarioPrincipal, $usuariosCopia, $empresa, $numeroLote) {
                    $m->from('naoresponda@mybp.com.br', 'Sistema MyBP');

                    // Assunto com indicação de Excel
                    $assunto = "[MyBP] Relatório de Vencimentos de Treinamentos (Excel) - {$empresa->razao_social}";
                    $m->subject($assunto);

                    $m->to($usuarioPrincipal['email'], $usuarioPrincipal['nome']);

                    // Headers para melhor rastreamento
                    try {
                        if (method_exists($m, 'getSwiftMessage') && $m->getSwiftMessage()) {
                            $headers = $m->getSwiftMessage()->getHeaders();
                            if ($headers) {
                                $headers->addTextHeader('X-Mailer', 'MyBP Sistema v1.0');
                                $headers->addTextHeader('X-Batch-Number', (string)$numeroLote);
                                $headers->addTextHeader('X-Priority', '3');
                                $headers->addTextHeader('X-Report-Type', 'Excel');
                            }
                        }
                    } catch (\Exception $e) {
                        // Ignorar erros de headers
                    }

                    // Adicionar cópias ocultas (BCC)
                    foreach ($usuariosCopia as $usuario) {
                        $m->bcc($usuario['email'], $usuario['nome']);
                    }
                }
            );

            return [
                'sucesso' => true,
                'destinatarios' => count($loteUsuarios)
            ];

        } catch (\Exception $e) {
            return [
                'sucesso' => false,
                'erro' => $e->getMessage(),
                'destinatarios' => count($loteUsuarios)
            ];
        }
    }

    /**
     * Obtém o template de email apropriado
     */
    private function obterTemplateEmail(): string
    {
        // Lista de templates em ordem de preferência
        $templatesPreferencia = [
            'email.treinamento.vencendo_excel_s3',  // Template específico para Excel
            'email.treinamento.vencendo_s3',        // Template existente para S3
            'email.treinamento.vencendo',           // Template básico
        ];

        foreach ($templatesPreferencia as $template) {
            try {
                // Verificar se view existe
                if (view()->exists($template)) {
                    $this->info("Usando template de email: {$template}");
                    return $template;
                }
            } catch (\Exception $e) {
                // Continuar para próximo template
                continue;
            }
        }

        $this->info("Usando template padrão: email.treinamento.vencendo_s3");
        return 'email.treinamento.vencendo_s3';
    }

    /**
     * Decide se deve interromper o processamento por causa de um erro
     */
    private function deveInterromperPorErro(\Exception $e): bool
    {
        // Tipos de erro que justificam interromper o processamento
        $errosCriticos = [
            'Connection refused',
            'Authentication failed',
            'Invalid API key',
            'Rate limit exceeded',
            'Service unavailable'
        ];

        $mensagemErro = strtolower($e->getMessage());

        foreach ($errosCriticos as $erroCritico) {
            if (strpos($mensagemErro, strtolower($erroCritico)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cria e faz upload do arquivo Excel para S3 com otimizações de memória
     */
    private function criarEUploadExcelS3($empresa, $treinamentosAgrupados): ?array
    {
        $caminhoS3 = null;
        $caminhoTempLocal = null;

        try {
            $this->info("Iniciando criação do arquivo Excel...");
            $memoryInicial = memory_get_usage(true);

            // Criar arquivo temporário - seguindo padrão do JobExportaCihCsvFinal
            $nomeArquivo = "treinamento_vencimento_" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
            $caminhoTempLocal = sys_get_temp_dir() . '/' . $nomeArquivo;

            // Criar spreadsheet em modo de baixo uso de memória
            $spreadsheet = $this->criarSpreadsheetOtimizado($empresa, $treinamentosAgrupados);

            $this->info("Salvando arquivo Excel localmente...");

            // Configurar writer para otimização de memória
            $writer = new Xlsx($spreadsheet);

            // Configurações disponíveis na versão atual
            if (method_exists($writer, 'setPreCalculateFormulas')) {
                $writer->setPreCalculateFormulas(false);
            }

            // Verificar se método de cache em disco existe
            if (method_exists($writer, 'setUseDiskCaching')) {
                $writer->setUseDiskCaching(true, sys_get_temp_dir());
            }

            // Configurações adicionais de otimização
            if (method_exists($writer, 'setOffice2003Compatibility')) {
                $writer->setOffice2003Compatibility(false);
            }

            // Salvar arquivo temporariamente
            $writer->save($caminhoTempLocal);

            // Liberar memória do spreadsheet
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $writer);
            gc_collect_cycles();

            $memoryAposExcel = memory_get_usage(true);
            $memoryUsadaExcel = $memoryAposExcel - $memoryInicial;
            $this->info("Memória utilizada para criar Excel: " . $this->formatBytes($memoryUsadaExcel));

            // Verificar se arquivo foi criado com sucesso
            if (!file_exists($caminhoTempLocal)) {
                throw new \Exception("Arquivo Excel não foi criado com sucesso");
            }

            $tamanhoArquivo = filesize($caminhoTempLocal);
            $this->info("Arquivo Excel criado: " . $this->formatBytes($tamanhoArquivo));

            // Upload para S3 - seguindo padrão do JobExportaCihCsvFinal
            $caminhoS3 = $nomeArquivo;

            $this->info("Fazendo upload do arquivo para S3...");

            // Ler arquivo em chunks para otimizar memória
            $conteudoArquivo = $this->lerArquivoEmChunks($caminhoTempLocal);

            $uploadSuccess = Storage::disk('s3')->put($caminhoS3, $conteudoArquivo, [
                'visibility' => 'private',
                'ContentType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'ContentDisposition' => 'attachment; filename="' . $nomeArquivo . '"',
                'Metadata' => [
                    'empresa_id' => (string)$empresa->id,
                    'data_geracao' => date('Y-m-d H:i:s'),
                    'tipo' => 'relatorio_treinamentos'
                ]
            ]);

            if (!$uploadSuccess) {
                throw new \Exception("Falha ao fazer upload do arquivo para S3");
            }

            $urlTemporaria = Storage::disk('s3')->temporaryUrl($caminhoS3, now()->addDays(7));

            $this->info("✓ Arquivo enviado para S3: {$caminhoS3}");
            $this->info("✓ URL temporária gerada (válida por 7 dias)");

            // Limpeza do arquivo temporário
            if (file_exists($caminhoTempLocal)) {
                unlink($caminhoTempLocal);
            }

            return [
                'url' => $urlTemporaria,
                'nome_arquivo' => $nomeArquivo,
                'caminho_s3' => $caminhoS3,
                'tamanho' => $tamanhoArquivo
            ];

        } catch (\Exception $e) {
            $this->error("Erro ao criar arquivo Excel S3: {$e->getMessage()}");

            // Limpeza em caso de erro
            if ($caminhoTempLocal && file_exists($caminhoTempLocal)) {
                unlink($caminhoTempLocal);
            }

            if ($caminhoS3) {
                $this->limparArquivoS3($caminhoS3);
            }

            return null;
        }
    }

    /**
     * Cria spreadsheet otimizado para uso eficiente de memória
     */
    private function criarSpreadsheetOtimizado($empresa, array $treinamentosAgrupados): Spreadsheet
    {
        // Configurações de otimização de memória para PhpSpreadsheet
        try {
            if (class_exists('\PhpOffice\PhpSpreadsheet\Settings')) {
                \PhpOffice\PhpSpreadsheet\Settings::setLibXmlLoaderOptions(LIBXML_COMPACT);
            }
        } catch (\Exception $e) {
            $this->info("Aviso: Não foi possível configurar LibXML: {$e->getMessage()}");
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Relatório de Treinamentos');

        // Configurar propriedades do documento
        $spreadsheet->getProperties()
            ->setCreator('Sistema MyBP')
            ->setLastModifiedBy('Sistema MyBP')
            ->setTitle('Relatório de Vencimentos de Treinamentos')
            ->setSubject('Treinamentos')
            ->setDescription("Relatório de treinamentos vencidos e próximos a vencer - {$empresa->razao_social}")
            ->setKeywords('treinamentos vencimentos mybp')
            ->setCategory('Relatórios');

        // Cabeçalho da empresa
        $this->adicionarCabecalhoEmpresa($sheet, $empresa);

        // Cabeçalho das colunas
        $linhaCabecalho = 6;
        $cabecalhos = [
            'A' => 'Funcionário',
            'B' => 'Cargo',
            'C' => 'Função',
            'D' => 'Data de Admissão',
            'E' => 'Centro de Custo',
            'F' => 'Padrão de Treinamento',
            'G' => 'Número do Crachá',
            'H' => 'Matrícula',
            'I' => 'Treinamento',
            'J' => 'Data do Treinamento',
            'K' => 'Data de Vencimento',
            'L' => 'Dias para Vencer',
            'M' => 'Status',
            'N' => 'Status com dias'
        ];

        foreach ($cabecalhos as $coluna => $titulo) {
            $sheet->setCellValue($coluna . $linhaCabecalho, $titulo);
        }

        // Estilizar cabeçalho
        $this->aplicarEstiloCabecalho($sheet, $linhaCabecalho);

        // Adicionar dados em lotes para otimizar memória
        $linha = $linhaCabecalho + 1;
        $totalLinhas = 0;

        foreach ($treinamentosAgrupados as $categoria => $funcionarios) {
            foreach ($funcionarios as $funcionario) {
                $funcionarioData = $funcionario['funcionario'];

                foreach ($funcionario['treinamentos'] as $treinamento) {
                    $this->adicionarLinhaFuncionario($sheet, $linha, $funcionarioData, $treinamento, $categoria);
                    $linha++;
                    $totalLinhas++;

                    // Limpeza de memória a cada 500 linhas
                    if ($totalLinhas % 500 === 0) {
                        gc_collect_cycles();
                        $this->info("Processadas {$totalLinhas} linhas do Excel...");
                    }
                }
            }
        }

        // Aplicar estilos finais
        $this->aplicarEstilosFinais($sheet, $linha - 1);

        // Auto-ajustar largura das colunas (somente para colunas principais)
        foreach (range('A', 'N') as $coluna) {
            try {
                $sheet->getColumnDimension($coluna)->setAutoSize(true);
            } catch (\Exception $e) {
                // Se falhar, definir largura fixa
                $sheet->getColumnDimension($coluna)->setWidth(15);
            }
        }

        $this->info("Excel criado com {$totalLinhas} linhas de dados");

        return $spreadsheet;
    }

    private function adicionarCabecalhoEmpresa($sheet, $empresa): void
    {
        // Título principal
        $sheet->setCellValue('A1', 'RELATÓRIO DE VENCIMENTOS DE TREINAMENTOS');
        $sheet->mergeCells('A1:N1');

        // Informações da empresa
        $sheet->setCellValue('A2', "Empresa: {$empresa->razao_social}");
        $sheet->mergeCells('A2:N2');

        $sheet->setCellValue('A3', "CNPJ: {$empresa->cnpj}");
        $sheet->mergeCells('A3:N3');

        $sheet->setCellValue('A4', "Data de Geração: " . date('d/m/Y H:i:s'));
        $sheet->mergeCells('A4:N4');

        // Linha em branco
        $sheet->setCellValue('A5', '');

        // Estilizar cabeçalho da empresa
        try {
            $sheet->getStyle('A1:N1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);

            $sheet->getStyle('A2:N4')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12]
            ]);
        } catch (\Exception $e) {
            $this->info("Aviso: Não foi possível aplicar estilo ao cabeçalho da empresa: {$e->getMessage()}");
            // Aplicar estilo básico como fallback
            $sheet->getStyle('A1:N1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A2:N4')->getFont()->setBold(true)->setSize(12);
        }
    }

    private function adicionarLinhaFuncionario($sheet, $linha, $funcionarioData, $treinamento, $categoria): void
    {
        $dataAdmissao = $funcionarioData['data_admissao'] ? (new DataHora($funcionarioData['data_admissao']))->dataCompleta() : '';
        $datatreinamento = $treinamento['data_treinamento'] ? (new DataHora($treinamento['data_treinamento']))->dataCompleta() : '';
        $dataVencimento = $treinamento['data_vencimento'] ? (new DataHora($treinamento['data_vencimento']))->dataCompleta() : '';

        $dados = [
            'A' => $funcionarioData['nome'],
            'B' => $funcionarioData['cargo'],
            'C' => $funcionarioData['funcao'],
            'D' => $dataAdmissao,
            'E' => $funcionarioData['centro_custo_label'],
            'F' => $funcionarioData['segmento'] ?? '--',
            'G' => $funcionarioData['numero_cracha'] ?? '',
            'H' => $funcionarioData['matricula'] ?? '',
            'I' => $treinamento['vencimento_nome'],
            'J' => $datatreinamento,
            'K' => $dataVencimento,
            'L' => $treinamento['dias_vencer'],
            'M' => $treinamento['status_label'],
            'N' => $treinamento['status_texto']
        ];

        foreach ($dados as $coluna => $valor) {
            $sheet->setCellValue($coluna . $linha, $valor);
        }

        // Aplicar cor baseada na categoria
        $this->aplicarCorPorCategoria($sheet, $linha, $categoria);
    }

    private function aplicarEstiloCabecalho($sheet, $linha): void
    {
        try {
            $sheet->getStyle("A{$linha}:N{$linha}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '366092']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ]);
        } catch (\Exception $e) {
            $this->info("Aviso: Não foi possível aplicar estilo ao cabeçalho: {$e->getMessage()}");
            // Aplicar estilo básico como fallback
            $sheet->getStyle("A{$linha}:N{$linha}")->getFont()->setBold(true);
        }
    }

    private function aplicarCorPorCategoria($sheet, $linha, $categoria): void
    {
        $cor = '';
        switch ($categoria) {
            case self::CATEGORIAS['VENCIDO']:
                $cor = 'FFE6E6'; // Vermelho claro
                break;
            case self::CATEGORIAS['PROXIMO']:
                $cor = 'FFF2E6'; // Laranja claro
                break;
            case self::CATEGORIAS['ATENCAO']:
                $cor = 'FFFFCC'; // Amarelo claro
                break;
        }

        if ($cor) {
            try {
                $sheet->getStyle("A{$linha}:N{$linha}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $cor]
                    ]
                ]);
            } catch (\Exception $e) {
                $this->info("Aviso: Não foi possível aplicar cor à linha {$linha}: {$e->getMessage()}");
            }
        }
    }

    private function aplicarEstilosFinais($sheet, $ultimaLinha): void
    {
        try {
            // Bordas para toda a tabela
            $sheet->getStyle("A6:N{$ultimaLinha}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN]
                ]
            ]);
        } catch (\Exception $e) {
            $this->info("Aviso: Não foi possível aplicar bordas: {$e->getMessage()}");
        }

        try {
            // Congelar painéis
            $sheet->freezePane('A7');
        } catch (\Exception $e) {
            $this->info("Aviso: Não foi possível congelar painéis: {$e->getMessage()}");
        }
    }

    /**
     * Lê arquivo em chunks para otimizar uso de memória
     */
    private function lerArquivoEmChunks(string $caminhoArquivo): string
    {
        $conteudo = '';
        $handle = fopen($caminhoArquivo, 'rb');

        if ($handle === false) {
            throw new \Exception("Não foi possível abrir o arquivo: {$caminhoArquivo}");
        }

        try {
            while (!feof($handle)) {
                $chunk = fread($handle, 8192); // 8KB chunks
                if ($chunk === false) {
                    throw new \Exception("Erro ao ler arquivo");
                }
                $conteudo .= $chunk;
            }
        } finally {
            fclose($handle);
        }

        return $conteudo;
    }

    private function prepararDadosEmail($empresa, $treinamentosAgrupados, $arquivoS3): array
    {
        return [
            'dados' => [
                'empresa' => $empresa->razao_social,
                'empresa_id' => $empresa->id,
                'empresa_cnpj' => $empresa->cnpj,
                'empresa_apelido' => $empresa->apelido,
                'empresa_endereco_completo' => $empresa->endereco_completo,
                'data_geracao' => (new DataHora())->dataHoraInsert(),
                'categorias' => $treinamentosAgrupados,
                'total_vencidos' => count($treinamentosAgrupados[self::CATEGORIAS['VENCIDO']]),
                'total_proximos' => count($treinamentosAgrupados[self::CATEGORIAS['PROXIMO']]),
                'total_atencao' => count($treinamentosAgrupados[self::CATEGORIAS['ATENCAO']]),
                'arquivo_s3' => [
                    'url' => $arquivoS3['url'],
                    'nome_arquivo' => $arquivoS3['nome_arquivo'],
                    'texto_link' => 'Baixar Relatório Detalhado (Excel)',
                    'validade' => 'Link válido até ' . now()->addDays(7)->format('d/m/Y'),
                    'instrucoes' => 'Relatório completo em formato Excel (.xlsx) para análise detalhada. O arquivo contém formatação por cores: vermelho para vencidos, laranja para próximos a vencer e amarelo para atenção.',
                    'tamanho' => $this->formatBytes($arquivoS3['tamanho']),
                    'tipo_arquivo' => 'Excel (.xlsx)',
                    'beneficios' => [
                        'Formatação colorida por categoria',
                        'Cabeçalhos organizados e estilos',
                        'Filtros e ordenação automática',
                        'Compatível com Excel e LibreOffice'
                    ]
                ]
            ]
        ];
    }

    /**
     * Exibe estatísticas finais do processamento
     */
    private function exibirEstatisticasFinais($estatisticas, $empresa): void
    {
        $tempoTotal = microtime(true) - $estatisticas['tempo_inicio'];

        $this->info("\n=== ESTATÍSTICAS FINAIS ===");
        $this->info("Empresa: {$empresa->razao_social}");
        $this->info("Lotes processados: {$estatisticas['lotes_processados']}");
        $this->info("E-mails enviados: {$estatisticas['emails_enviados']}");
        $this->info("Erros: {$estatisticas['erros']}");
        $this->info("Tempo total: " . number_format($tempoTotal, 2) . " segundos");

        if ($estatisticas['emails_enviados'] > 0) {
            $mediaPorEmail = $tempoTotal / $estatisticas['emails_enviados'];
            $this->info("Média por e-mail: " . number_format($mediaPorEmail, 3) . " segundos");
        }

        // Status final
        if ($estatisticas['erros'] === 0) {
            $this->info("✓ Todos os lotes foram processados com sucesso!");
        } elseif ($estatisticas['emails_enviados'] > 0) {
            $this->info("⚠ Processamento concluído com alguns erros.");
        } else {
            $this->error("✗ Falha total no processamento.");
        }
    }

    private function exibirEstatisticasFinaisComando($empresasProcessadas, $tempoTotal): void
    {
        $this->info("\n=== ESTATÍSTICAS FINAIS ===");
        $this->info("Empresas processadas: {$empresasProcessadas}");
        $this->info("Tempo total: " . number_format($tempoTotal, 2) . " segundos");
        $this->info("Memória pico: " . $this->formatBytes(memory_get_peak_usage(true)));
        $this->info("Verificação de treinamentos concluída!");
    }

    private function limparArquivoS3($caminhoS3): void
    {
        try {
            Storage::disk('s3')->delete($caminhoS3);
            $this->info("Arquivo S3 removido: {$caminhoS3}");
        } catch (\Exception $e) {
            $this->error("Erro ao remover arquivo S3: {$e->getMessage()}");
        }
    }

    /**
     * Formatar bytes para exibição legível
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
