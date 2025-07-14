<?php

namespace App\Console\Commands;

use App\Models\Admissao;
use App\Models\Cliente;
use App\Models\Sistema;
use App\Models\TipoRecebeEmail;
use App\Models\Treinamento;
use App\Models\User;
use App\Models\Vencimento;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use MasterTag\DataHora;

class TreinamentoVencimento extends Command
{
    protected $signature = 'mybp:treinamento-vencimento {--id=} {--all} {--force} {--lote-size=100} {--delay=1}';
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

    // Configurações de lote
    private const LOTE_SIZE_DEFAULT = 100;
    private const DELAY_DEFAULT = 1; // segundos entre lotes

    public function handle(): int
    {
        $this->info('Iniciando verificação de treinamentos...');
        $this->info("Ambiente: Docker PHP 8.2 | Memória inicial: " . $this->formatBytes(memory_get_usage(true)));

        $empresas = $this->buscarEmpresas();
        $this->info("Total de empresas encontradas: {$empresas->count()}");

        $tempoInicio = microtime(true);
        $empresasProcessadas = 0;

        foreach ($empresas as $empresa) {
            $this->processarEmpresa($empresa);
            $empresasProcessadas++;

            // Limpeza de memória a cada empresa (importante no Docker)
            if ($empresasProcessadas % 5 === 0) {
                gc_collect_cycles();
                $this->info("Memória atual: " . $this->formatBytes(memory_get_usage(true)));
            }
        }

        $tempoTotal = microtime(true) - $tempoInicio;
        $this->info("\n=== ESTATÍSTICAS FINAIS ===");
        $this->info("Empresas processadas: {$empresasProcessadas}");
        $this->info("Tempo total: " . number_format($tempoTotal, 2) . " segundos");
        $this->info("Memória pico: " . $this->formatBytes(memory_get_peak_usage(true)));
        $this->info("Verificação de treinamentos concluída!");

        return 0;
    }

    private function buscarEmpresas(): \Illuminate\Database\Eloquent\Collection
    {
        return Cliente::withoutGlobalScopes()
            ->whereAtivo(true)
            ->select(['id', 'razao_social', 'cnpj', 'apelido',
                'logradouro',
                'bairro',
                'cep',
                'numero',
                'complemento',
                'municipio',
                'uf'
            ])
            ->with(['Filiais', 'ClienteConfig'])
            ->when($this->option('id'), function ($query) {
                return $query->whereId($this->option('id'));
            })
            ->get();
    }

    private function processarEmpresa($empresa): void
    {
        $this->info("Processando empresa: {$empresa->razao_social}");

        $usuariosEmail = $this->buscarUsuariosEmail($empresa->id);
        $this->info("Usuários para receber e-mail: {$usuariosEmail->count()}");

        if ($usuariosEmail->isEmpty()) {
            $this->info('Nenhum usuário configurado para receber e-mail. Pulando...');
            return;
        }

        $vencimentos = $this->buscarVencimentos($empresa->id);
        $admitidos = $this->buscarAdmitidos($empresa->id);

        if ($admitidos->isEmpty()) {
            $this->info('Nenhum admitido encontrado para esta empresa. Pulando...');
            return;
        }

        $this->info("Total de admitidos: {$admitidos->count()}");

        $treinamentosAgrupados = $this->processarTreinamentos($empresa->id, $admitidos, $vencimentos);

        if ($this->naoHaTreinamentosAlerta($treinamentosAgrupados)) {
            $this->info('Nenhum treinamento vencido ou próximo a vencer encontrado. Pulando...');
            return;
        }

        $this->exibirResumo($treinamentosAgrupados);
        $this->enviarNotificacoesPorLotes($empresa, $usuariosEmail, $treinamentosAgrupados);

        $this->info("Processamento concluído para empresa: {$empresa->razao_social}");
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

    private function buscarAdmitidos(int $empresaId): \Illuminate\Database\Eloquent\Collection
    {
        return Admissao::withoutGlobalScopes()
            ->join('feedback_curriculos as fc', 'fc.id', '=', 'admissoes.feedback_id')
            ->join('curriculos as c', 'c.id', '=', 'fc.curriculo_id')
            ->join('centro_custos as cc', 'cc.id', '=', 'admissoes.centro_custo_id')
            ->where('admissoes.status', Admissao::STATUS_ADMISSAO_ADMITIDO)
            ->where('fc.empresa_id', $empresaId)
            ->select([
                'admissoes.id as admissao_id',
                'admissoes.funcao',
                'admissoes.cargo',
                'admissoes.data_admissao',
                'admissoes.centro_custo_id',
                'admissoes.centro_custo_filial_id',
                'admissoes.filial',
                'admissoes.numero_cracha',
                'admissoes.data_admissao',
                'admissoes.matricula',
                'cc.label as centro_custo_label',
                'c.id as curriculo_id',
                'c.nome',
                'c.cpf',
                'fc.empresa_id',
                'fc.id as feedback_id',
            ])
            ->get()
            ->keyBy('feedback_id');
    }

    private function processarTreinamentos($empresaId, $admitidos, $vencimentos)
    {
        $treinamentos = $this->buscarTreinamentos($admitidos->keys());
        $this->info("Total de treinamentos encontrados: {$treinamentos->count()}");

        $treinamentosProcessados = $this->classificarTreinamentos($treinamentos, $vencimentos);
        $treinamentosAlerta = $this->filtrarTreinamentosAlerta($treinamentosProcessados);

        $this->info("Treinamentos vencidos ou próximos a vencer: {$treinamentosAlerta->count()}");

        return $this->agruparTreinamentos($treinamentosAlerta, $admitidos);
    }

    private function buscarTreinamentos(\Illuminate\Support\Collection $feedbackIds): \Illuminate\Database\Eloquent\Collection
    {
        return Treinamento::withoutGlobalScopes()
            ->join('treinamento_vencimento as tv', 'tv.treinamento_id', '=', 'treinamentos.id')
            ->whereIn('treinamentos.feedback_id', $feedbackIds)
            ->select([
                'treinamentos.id',
                'treinamentos.feedback_id',
                'tv.treinamento_id',
                'tv.vencimento_id',
                'tv.data_treinamento',
                'tv.data_vencimento',
                'tv.numero_fat',
            ])
            ->get();
    }

    private function classificarTreinamentos($treinamentos, $vencimentos)
    {
        $hoje = new DataHora();
        $dataAtual = $hoje->dataInsert();

        return $treinamentos->map(function ($item) use ($dataAtual, $vencimentos) {
            $item->dias_vencer = $this->calcularDiasParaVencer($item->data_vencimento, $dataAtual);
            $item->vencimento_nome = $this->obterNomeVencimento($item->vencimento_id, $vencimentos);
            $this->definirCategoriaEStatus($item);

            return $item;
        });
    }

    private function calcularDiasParaVencer(?string $dataVencimento, string $dataAtual): int|float
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

    private function filtrarTreinamentosAlerta($treinamentosProcessados)
    {
        return $treinamentosProcessados->filter(function ($item) {
            return $item->dias_vencer <= self::DIAS_ALERTA;
        });
    }

    private function agruparTreinamentos($treinamentosAlerta, $admitidos)
    {
        $agrupados = [
            self::CATEGORIAS['VENCIDO'] => [],
            self::CATEGORIAS['PROXIMO'] => [],
            self::CATEGORIAS['ATENCAO'] => []
        ];

        foreach ($treinamentosAlerta as $treinamento) {
            if ($treinamento->categoria === self::CATEGORIAS['REGULAR']) {
                continue;
            }

            $feedbackId = $treinamento->feedback_id;
            $categoria = $treinamento->categoria;

            if (!isset($agrupados[$categoria][$feedbackId])) {
                $agrupados[$categoria][$feedbackId] = $this->criarGrupoFuncionario($admitidos[$feedbackId]);
            }

            $agrupados[$categoria][$feedbackId]['treinamentos'][] = $this->extrairDadosTreinamento($treinamento);
        }

        return $this->ordenarTreinamentosPorPrioridade($agrupados);
    }

    private function criarGrupoFuncionario($admitido): array
    {
        return [
            'funcionario' => [
                'nome' => $admitido->nome,
                'cargo' => $admitido->cargo,
                'data_admissao' => $admitido->data_admissao,
                'admissao_id' => $admitido->admissao_id,
                'funcao' => $admitido->funcao,
                'centro_custo_id' => $admitido->centro_custo_id,
                'centro_custo_label' => $admitido->centro_custo_label,
                'centro_custo_filial' => Sistema::getFilial($admitido->empresa_id, $admitido->centro_custo_filial_id) ?: null,
                'filial' => $admitido->filial,
                'numero_cracha' => $admitido->numero_cracha,
                'matricula' => $admitido->matricula,
                'curriculo_id' => $admitido->curriculo_id,
                'cpf' => $admitido->cpf,
                'empresa_id' => $admitido->empresa_id,
                'feedback_id' => $admitido->feedback_id,
                'cnpj_lotacao' => Sistema::getEmpresaFilialMatriz($admitido->centro_custo_filial_id, $admitido->empresa_id) ?? null,
            ],
            'treinamentos' => []
        ];
    }

    private function extrairDadosTreinamento($treinamento): array
    {
        return [
            'id' => $treinamento->id,
            'vencimento_id' => $treinamento->vencimento_id,
            'vencimento_nome' => $treinamento->vencimento_nome,
            'data_treinamento' => $treinamento->data_treinamento,
            'data_vencimento' => $treinamento->data_vencimento,
            'dias_vencer' => $treinamento->dias_vencer,
            'status_texto' => $treinamento->status_texto,
            'prioridade' => $treinamento->prioridade
        ];
    }

    private function ordenarTreinamentosPorPrioridade($agrupados)
    {
        foreach ($agrupados as &$grupo) {
            foreach ($grupo as &$funcionario) {
                usort($funcionario['treinamentos'], function ($a, $b) {
                    return $b['prioridade'] <=> $a['prioridade'];
                });
            }
        }

        return $agrupados;
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

        // Upload único do arquivo para S3
        $arquivoS3 = $this->criarEUploadArquivoS3($empresa, $treinamentosAgrupados);

        if (!$arquivoS3) {
            $this->error('Falha ao criar arquivo S3. Abortando envio de e-mails.');
            return;
        }

        $dadosEmail = $this->prepararDadosEmail($empresa, $treinamentosAgrupados, $arquivoS3);

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

            // Enviar email
            Mail::send(
                ['html' => 'email.treinamento.vencendo_s3'],
                $dadosEmail,
                function ($m) use ($usuarioPrincipal, $usuariosCopia, $empresa, $numeroLote) {
                    $m->from('naoresponda@mybp.com.br', 'Sistema MyBP');

                    // Assunto com identificação do lote para debugging
                    $assunto = "[MyBP] Relatório de Vencimentos de Treinamentos - {$empresa->razao_social}";
                    $m->subject($assunto);

                    $m->to($usuarioPrincipal['email'], $usuarioPrincipal['nome']);

                    // Headers para melhor rastreamento
                    try {
                        if (method_exists($m, 'getSwiftMessage') && $m->getSwiftMessage()) {
                            $headers = $m->getSwiftMessage()->getHeaders();
                            if ($headers) {
                                $headers->addTextHeader('X-Mailer', 'MyBP Sistema v1.0');
                                $headers->addTextHeader('X-Batch-Number', (string)$numeroLote);
                                $headers->addTextHeader('X-Priority', '3'); // Normal priority
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
     * Exibe estatísticas finais do processamento
     */
    private function exibirEstatisticasFinais($estatisticas, $empresa)
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

    private function criarEUploadArquivoS3($empresa, $treinamentosAgrupados): ?array
    {
        $caminhoS3 = null;

        try {
            $csvContent = $this->gerarConteudoCSVUTF8($treinamentosAgrupados);
            $nomeArquivo = "treinamentos_{$empresa->id}_" . date('YmdHis') . '.csv';
            $caminhoS3 = "relatorios/treinamentos/{$empresa->id}/" . date('Y/m/d') . '/' . Str::random(10) . '_' . $nomeArquivo;

            $this->info("Fazendo upload do arquivo para S3...");

            $uploadSuccess = Storage::disk('s3')->put($caminhoS3, $csvContent, [
                'visibility' => 'private',
                'ContentType' => 'text/csv; charset=utf-8',
                'ContentDisposition' => 'attachment; filename="' . $nomeArquivo . '"',
                'ContentEncoding' => 'utf-8',
                'Metadata' => [
                    'charset' => 'utf-8',
                    'separator' => 'semicolon'
                ]
            ]);

            if (!$uploadSuccess) {
                throw new \Exception("Falha ao fazer upload do arquivo para S3");
            }

            $urlTemporaria = Storage::disk('s3')->temporaryUrl($caminhoS3, now()->addDays(7));

            $this->info("✓ Arquivo enviado para S3: {$caminhoS3}");
            $this->info("✓ URL temporária gerada (válida por 7 dias)");

            return [
                'url' => $urlTemporaria,
                'nome_arquivo' => $nomeArquivo,
                'caminho_s3' => $caminhoS3
            ];

        } catch (\Exception $e) {
            $this->error("Erro ao criar arquivo S3: {$e->getMessage()}");

            if ($caminhoS3) {
                $this->limparArquivoS3($caminhoS3);
            }

            return null;
        }
    }

    private function gerarConteudoCSVUTF8(array $treinamentosAgrupados): string
    {
        // Configurar locale para garantir encoding correto
        mb_internal_encoding('UTF-8');

        // Construir CSV como string para ter controle total da codificação
        $csv = '';

        // Adicionar BOM UTF-8 para garantir que o Excel reconheça a codificação
        $csv .= "\xEF\xBB\xBF";

        // Adicionar comando SEP para forçar o Excel a usar ponto e vírgula como separador
        $csv .= "sep=;\n";

        // Cabeçalho
        $cabecalho = [
            'Funcionário', 'Cargo', 'Função', 'Data de Admissão', 'Centro de custo',
            'Número do Crachá', 'Treinamento', 'Data Treinamento',
            'Data Vencimento', 'Dias para Vencer', 'Status'
        ];
        $csv .= $this->arrayParaLinhaCSV($cabecalho);

        $totalLinhas = 0;

        // Dados - processamento otimizado para Docker
        foreach ($treinamentosAgrupados as $funcionarios) {
            foreach ($funcionarios as $funcionario) {
                $funcionarioData = $funcionario['funcionario'];

                foreach ($funcionario['treinamentos'] as $treinamento) {
                    // Pré-processar dados uma única vez
                    $dataAdmissao = $funcionarioData['data_admissao'] ?? '';
                    $datatreinamento = $treinamento['data_treinamento'] ? date('d/m/Y', strtotime($treinamento['data_treinamento'])) : '';
                    $dataVencimento = $treinamento['data_vencimento'] ? date('d/m/Y', strtotime($treinamento['data_vencimento'])) : '';

                    $dados = [
                        $this->garantirUTF8($funcionarioData['nome']),
                        $this->garantirUTF8($funcionarioData['cargo']),
                        $this->garantirUTF8($funcionarioData['funcao']),
                        $dataAdmissao,
                        $this->garantirUTF8($funcionarioData['centro_custo_label']),
                        $funcionarioData['numero_cracha'] ?? '',
                        $this->garantirUTF8($treinamento['vencimento_nome']),
                        $datatreinamento,
                        $dataVencimento,
                        $treinamento['dias_vencer'],
                        $this->garantirUTF8($treinamento['status_texto'])
                    ];

                    $csv .= $this->arrayParaLinhaCSV($dados);
                    $totalLinhas++;

                    // Limpeza de memória a cada 1000 linhas (otimização Docker)
                    if ($totalLinhas % 1000 === 0) {
                        $this->info("Processadas {$totalLinhas} linhas do CSV...");
                    }
                }
            }
        }

        $this->info("CSV gerado com {$totalLinhas} linhas de dados");

        // Garantir que todo o conteúdo está em UTF-8
        return mb_convert_encoding($csv, 'UTF-8', 'UTF-8');
    }

    /**
     * Garante que o texto está em UTF-8 e limpo para CSV
     */
    private function garantirUTF8(?string $texto): string
    {
        if (is_null($texto) || $texto === '') {
            return '';
        }

        // Detectar encoding atual e converter para UTF-8 se necessário
        $encoding = mb_detect_encoding($texto, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($encoding !== 'UTF-8') {
            $texto = mb_convert_encoding($texto, 'UTF-8', $encoding);
        }

        // Remove quebras de linha e caracteres especiais que podem quebrar o CSV
        $texto = str_replace(["\r", "\n", "\t"], ' ', $texto);

        // Remove espaços extras
        $texto = preg_replace('/\s+/', ' ', $texto);

        // Garantir que está em UTF-8 válido
        $texto = mb_convert_encoding($texto, 'UTF-8', 'UTF-8');

        return trim($texto);
    }

    /**
     * Converte array para linha CSV com encoding UTF-8 garantido
     */
    private function arrayParaLinhaCSV(array $dados): string
    {
        $linha = '';
        $primeiroItem = true;

        foreach ($dados as $campo) {
            if (!$primeiroItem) {
                $linha .= ';';
            }

            // Converter para string e garantir UTF-8
            $campo = (string)$campo;
            $campo = $this->garantirUTF8($campo);

            // Escapar aspas duplas
            $campo = str_replace('"', '""', $campo);

            // Adicionar aspas se contém separador ou quebra de linha
            if (strpos($campo, ';') !== false || strpos($campo, '"') !== false || strpos($campo, "\n") !== false) {
                $campo = '"' . $campo . '"';
            }

            $linha .= $campo;
            $primeiroItem = false;
        }

        return $linha . "\n";
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
                    'texto_link' => 'Baixar Relatório Detalhado (CSV)',
                    'validade' => 'Link válido até ' . now()->addDays(7)->format('d/m/Y'),
                    'instrucoes' => 'Relatório completo em formato CSV UTF-8 para análise detalhada'
                ]
            ]
        ];
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
