<?php

namespace App\Jobs;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Models\Avaliacao;
use App\Models\Exportacao;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use MasterTag\DataHora;

class JobExportaAvaliacoesCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600; // 10 minutos
    public $queue;

    protected $usuario;
    protected $local;
    protected $nomeArquivo;
    protected $filtros;
    protected $lockKey;
    protected $lockTimeout = 1200; // 20 minutos

    const CHUNK_SIZE = 200; // Chunk baixo para evitar sobrecarga de memória

    /**
     * Create a new job instance.
     */
    public function __construct($usuario, $local, $nomeArquivo, $filtros)
    {
        $this->usuario = $usuario;
        $this->local = $local;
        $this->nomeArquivo = $nomeArquivo;
        $this->filtros = $filtros;
        
        // Gerar chave de lock única baseada no arquivo e usuário
        $this->lockKey = 'avaliacoes_export_lock_' . md5($nomeArquivo . '_' . $usuario . '_' . json_encode($filtros));
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Tentar adquirir lock distribuído
        if (!$this->acquireLock()) {
            \Log::info("Job Avaliações já está sendo processado por outra instância. Lock key: {$this->lockKey}");
            return;
        }

        try {
            \Log::info('Iniciando exportação Avaliações CSV');
            \Log::info('Lock adquirido com sucesso. Lock key: ' . $this->lockKey);
            \Log::info('Filtros: ' . json_encode($this->filtros));
            
            $headers = $this->getHeaders();
            \Log::info('Cabeçalhos: ' . json_encode($headers));

            $localFilePath = $this->createLocalCsvFile($headers);

            $s3FilePath = $this->nomeArquivo;
            $this->uploadToS3($localFilePath, $s3FilePath);

            unlink($localFilePath);

            Event::dispatch(new NotificacaoEvent([
                'user_id' => $this->usuario,
                'local' => $this->local,
            ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));

            Exportacao::create([
                'user_id' => $this->usuario,
                'arquivo' => $this->nomeArquivo,
                'local' => $this->local,
                'removido' => false,
            ]);

            \Log::info('Exportação Avaliações CSV finalizada com sucesso');

        } catch (\Exception $e) {
            \Log::error('Erro na exportação Avaliações CSV: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        } finally {
            // Sempre liberar o lock
            $this->releaseLock();
        }
    }

    /**
     * Adquirir lock distribuído
     */
    private function acquireLock(): bool
    {
        return Cache::lock($this->lockKey, $this->lockTimeout)->get();
    }

    /**
     * Liberar lock distribuído
     */
    private function releaseLock(): void
    {
        try {
            Cache::lock($this->lockKey)->forceRelease();
            \Log::info("Lock liberado: {$this->lockKey}");
        } catch (\Exception $e) {
            \Log::warning("Falha ao liberar lock: {$this->lockKey} - " . $e->getMessage());
        }
    }

    /**
     * Obter cabeçalhos do CSV
     */
    private function getHeaders(): array
    {
        return [
            'ID Avaliação',
            'Título Avaliação',
            'Tipo de Avaliação',
            'Status Avaliação',
            'Ano Avaliação',
            'Data Início',
            'Data Fim',
            'Auto Avaliação',
            // 'Tipo PJ',
            'Ativo',
            'Data Criação Avaliação',
            
            // Dados do Feedback
            // 'ID Feedback',
            'Status Feedback',
            'Origem Feedback',
            'Avaliador Principal',
            'Avaliar Como',
            
            // Dados do Funcionário Avaliado
            // 'ID Funcionário',
            'Nome Funcionário',
            'E-mail Funcionário',
            'Matrícula',
            // 'CPF',
            'Cargo',
            // 'Área',
            'Centro de Custo',
            'É Filial',
            'Razão Social',
            'CNPJ',
            'Data Admissão',
            
            // Dados do Avaliador
            // 'ID Avaliador',
            'Nome Avaliador',
            'E-mail Avaliador',
            
            // Dados do Feedback
            // 'Comentário Avaliador',
            // 'Nota Final',
            // 'Data Início Feedback',
            'Data Fim Feedback',
            
            // Total de Questões/Respostas
            // 'Total Respostas',
            // 'Média Geral',
        ];
    }

    /**
     * Criar arquivo CSV local
     */
    private function createLocalCsvFile(array $headers): string
    {
        $localFilePath = sys_get_temp_dir() . '/' . $this->nomeArquivo;
        $file = fopen($localFilePath, 'w');
        
        // BOM para UTF-8
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Escrever cabeçalhos
        fputcsv($file, $headers, ';');

        // Processar dados em chunks
        $query = $this->buildQuery();
        
        $chunkCount = 0;
        $query->chunk(self::CHUNK_SIZE, function ($avaliacoes) use ($file, &$chunkCount) {
            foreach ($avaliacoes as $avaliacao) {
                // Se a avaliação tem feedbacks, exportar cada feedback em uma linha
                if ($avaliacao->AvaliacaoFeedbacks && $avaliacao->AvaliacaoFeedbacks->count() > 0) {
                    foreach ($avaliacao->AvaliacaoFeedbacks as $feedback) {
                        $row = $this->formatRow($avaliacao, $feedback);
                        fputcsv($file, $row, ';');
                        unset($row);
                        unset($feedback);
                    }
                } else {
                    // Se não tem feedbacks, exportar apenas os dados da avaliação
                    $row = $this->formatRow($avaliacao, null);
                    fputcsv($file, $row, ';');
                    unset($row);
                }
                unset($avaliacao);
            }
            $chunkCount++;
            // Coleta de lixo a cada 10 chunks
            if ($chunkCount % 10 === 0 && function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        });

        fclose($file);
        
        return $localFilePath;
    }

    /**
     * Construir query com filtros
     */
    private function buildQuery()
    {
        try {
            // Autenticar o usuário para respeitar as regras de tenant
            $user = \App\Models\User::find($this->usuario);
            if (!$user) {
                \Log::error('Usuário não encontrado: ' . $this->usuario);
                throw new \Exception("Usuário não encontrado: {$this->usuario}");
            }

            auth()->login($user);
            \Log::info('Usuário autenticado para exportação: ' . $user->id . ' - Empresa: ' . $user->empresa_id);

            // Usar o método filtro do AvaliacaoController para garantir as regras de tenant
            $avaliacaoController = new \App\Http\Controllers\AvaliacaoController();
            $request = new \Illuminate\Http\Request($this->filtros);
            
            // Aplicar filtros usando o método existente (que respeita TenantTrait)
            $query = $avaliacaoController->filtro($request);

            // Adicionar relacionamentos necessários para exportação completa
            $query->with([
                'AvaliacaoTipo',
                'AvaliacaoFeedbacks' => function ($q) {
                    $q->with([
                        'Funcionario',
                        'Avaliador:id,nome,login',
                        'Respostas',
                        'TipoAvaliador'
                    ]);
                }
            ]);

            return $query;
        } catch (\Exception $e) {
            \Log::error("Erro ao construir query no JobExportaAvaliacoesCsv: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Formatar linha do CSV
     */
    private function formatRow($avaliacao, $feedback = null): array
    {
        $dadosFuncionario = $this->getDadosFuncionario($feedback);
        $totalRespostas = $feedback && $feedback->Respostas ? $feedback->Respostas->count() : 0;
        $somaNotas = $feedback && $feedback->Respostas ? $feedback->Respostas->sum('nota') : 0;
        $mediaGeral = $totalRespostas > 0 ? round($somaNotas / $totalRespostas, 2) : 0;
        
        return [
            // Dados da Avaliação
            $avaliacao->id,
            $avaliacao->titulo,
            $avaliacao->AvaliacaoTipo ? $avaliacao->AvaliacaoTipo->nome : 'N/A',
            $this->formatStatus($avaliacao->status),
            $avaliacao->ano_avaliacao,
            $avaliacao->data_inicio_prazo ? (new DataHora($avaliacao->data_inicio_prazo))->dataCompleta() : 'N/A',
            $avaliacao->data_fim_prazo ? (new DataHora($avaliacao->data_fim_prazo))->dataCompleta() : 'N/A',
            // Auto Avaliação: Sim apenas se feedback for do próprio funcionário
            ($feedback && $feedback->origem_feedback === 'Funcionario' && $feedback->Funcionario && $feedback->Avaliador && $feedback->Funcionario->id === $feedback->Avaliador->id) ? 'Sim' : 'Não',
            // $this->formatTipoPj($avaliacao->tipo_pj),
            $avaliacao->ativo ? 'Sim' : 'Não',
            $avaliacao->created_at ? (new DataHora($avaliacao->created_at))->dataHoraCompleta() : 'N/A',
            
            // Dados do Feedback
            // $feedback ? $feedback->id : 'N/A',
            $feedback ? $this->formatStatusFeedback($feedback->status) : 'N/A',
            $feedback ? $this->formatOrigemFeedback($feedback->origem_feedback) : 'N/A',
            $feedback ? ($feedback->principal ? 'Sim' : 'Não') : 'N/A',
            $feedback && $feedback->TipoAvaliador ? ($feedback->TipoAvaliador->label ?? 'N/A') : 'N/A',
            
            // Dados do Funcionário Avaliado
            // $dadosFuncionario['id'],
            $dadosFuncionario['nome'],
            $dadosFuncionario['email'] ?? $dadosFuncionario['login'],
            $dadosFuncionario['matricula'],
            // $dadosFuncionario['cpf'],
            $dadosFuncionario['cargo'],
            // $dadosFuncionario['area'],
            $dadosFuncionario['centro_custo'],
            $dadosFuncionario['is_filial'] ?? 'N/A',
            $dadosFuncionario['razao_social'] ?? 'N/A',
            $dadosFuncionario['cnpj_empresa'] ?? 'N/A',
            $dadosFuncionario['data_admissao'] ? (new DataHora($dadosFuncionario['data_admissao']))->dataCompleta() : 'N/A',
            
            // Dados do Avaliador
            // $feedback && $feedback->Avaliador ? $feedback->Avaliador->id : 'N/A',
            $feedback && $feedback->Avaliador ? $feedback->Avaliador->nome : 'N/A',
            $feedback && $feedback->Avaliador ? ($feedback->Avaliador->email ?? $feedback->Avaliador->login) : 'N/A',
            
            // Dados do Feedback
            // $feedback ? $this->limparTexto($feedback->comentario ?? '') : 'N/A',
            // $feedback ? (($feedback->nota_final_total !== null && $feedback->nota_final_total !== '') ? $feedback->nota_final_total : ($mediaGeral ?: 'N/A')) : 'N/A',
            // $feedback && $feedback->inicio_feedback ? date('d/m/Y H:i:s', strtotime($feedback->inicio_feedback)) : 'N/A',
            $feedback && $feedback->fim_feedback ? date('d/m/Y H:i:s', strtotime($feedback->fim_feedback)) : 'N/A',
            
            // Total de Questões/Respostas
            // $totalRespostas,
            // $mediaGeral,
        ];
    }

    /**
     * Formatar status da avaliação
     */
    private function formatStatus($status): string
    {
        $statusMap = [
            'Aguardando Inicio' => 'Aguardando Início',
            'Aberta' => 'Aberta',
            'Encerrada' => 'Encerrada',
        ];

        return $statusMap[$status] ?? $status;
    }

    /**
     * Formatar tipo PJ
     */
    private function formatTipoPj($tipo): string
    {
        $tipoMap = [
            'CLT' => 'CLT',
            'PJ' => 'PJ',
        ];

        return $tipoMap[$tipo] ?? $tipo;
    }

    /**
     * Formatar status do feedback
     */
    private function formatStatusFeedback($status): string
    {
        $statusMap = [
            'Pendente' => 'Pendente',
            'Avaliada' => 'Avaliada',
            'Finalizada' => 'Finalizada',
        ];

        return $statusMap[$status] ?? ($status ?? 'N/A');
    }

    /**
     * Formatar origem do feedback
     */
    private function formatOrigemFeedback($origem): string
    {
        $origemMap = [
            'Funcionario' => 'Funcionário',
            'Avaliador' => 'Avaliador',
        ];

        return $origemMap[$origem] ?? $origem;
    }

    /**
     * Limpar texto removendo quebras de linha e caracteres especiais
     */
    private function limparTexto($texto): string
    {
        if (empty($texto)) {
            return '';
        }
        
        // Remove quebras de linha e caracteres especiais
        $texto = str_replace(["\r\n", "\n", "\r", "\t"], ' ', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);
        $texto = trim($texto);
        
        return $texto;
    }

    /**
     * Obter dados completos do funcionário
     */
    private function getDadosFuncionario($feedback): array
    {
        $dadosPadrao = [
            'id' => 'N/A',
            'nome' => 'N/A',
            'login' => 'N/A',
            'matricula' => 'N/A',
            'cpf' => 'N/A',
            'cargo' => 'N/A',
            'area' => 'N/A',
            'centro_custo' => 'N/A',
            'data_admissao' => 'N/A',
        ];

        // Validar se feedback existe e tem empresa_id
        if (!$feedback || !isset($feedback->empresa_id) || !$feedback->Funcionario) {
            return $dadosPadrao;
        }

        try {
            $funcionario = $feedback->Funcionario;
            
            $dados = [
                'id' => $funcionario->id,
                'nome' => $funcionario->nome ?? 'N/A',
                'login' => $funcionario->login ?? 'N/A',
                'cpf' => 'N/A',
                'matricula' => 'N/A',
                'cargo' => 'N/A',
                'area' => 'N/A',
                'centro_custo' => 'N/A',
                'data_admissao' => 'N/A',
            ];

            // Buscar dados adicionais do colaborador se existir FeedbackCurriculo
                $feedbackCurriculo = \App\Models\FeedbackCurriculo::with([
                    'Curriculo',
                    'Admissao.CentroCusto',
                    'Admissao.AreaEtiqueta',
                    'Admissao.CentroCustoFilial.Filial'
                ])
                    ->whereCurriculoId($funcionario->id)
                ->whereEmpresaId($feedback->empresa_id)
                ->first();

            if ($feedbackCurriculo) {
                // Buscar CPF do Curriculo
                if ($feedbackCurriculo->Curriculo) {
                    $dados['cpf'] = $feedbackCurriculo->Curriculo->cpf ?? 'N/A';
                }
                
                // Buscar dados da Admissão
                if ($feedbackCurriculo->Admissao) {
                    $admissao = $feedbackCurriculo->Admissao;
                    
                    $dados['matricula'] = $admissao->matricula ?? 'N/A';
                        $dados['cargo'] = $admissao->cargo ?? 'N/A';
                    $dados['data_admissao'] = $admissao->data_admissao 
                        ? (new DataHora($admissao->data_admissao))->dataCompleta()
                        : 'N/A';
                    
                    if ($admissao->CentroCusto) {
                        // Campo correto no modelo CentroCusto é 'label'
                        $dados['centro_custo'] = $admissao->CentroCusto->label ?? 'N/A';
                    }
                    
                    if ($admissao->AreaEtiqueta) {
                        $dados['area'] = $admissao->AreaEtiqueta->area_etiqueta ?? 'N/A';
                    }

                    // Informações de filial
                    $dados['is_filial'] = $admissao->filial ? 'Filial' : 'Matriz';
                    if ($admissao->filial && $admissao->CentroCustoFilial && $admissao->CentroCustoFilial->Filial) {
                        $filial = $admissao->CentroCustoFilial->Filial;
                        // No modelo ClienteFilial, os dados ficam no campo JSON 'dados'
                        $dados['razao_social'] = $filial->dados->razao_social ?? 'N/A';
                        $dados['cnpj_empresa'] = $filial->dados->cnpj ?? 'N/A';
                    } else {
                        // Quando não for filial, usar dados do cliente principal (empresa matriz)
                        $clienteMatriz = \App\Models\Cliente::find(auth()->user()->empresa_id);
                        $dados['razao_social'] = $clienteMatriz->razao_social ?? 'N/A';
                        $dados['cnpj_empresa'] = $clienteMatriz->cnpj ?? 'N/A';
                    }
                }
            }

            return $dados;
        } catch (\Exception $e) {
            \Log::warning("Erro ao buscar dados do funcionário: " . $e->getMessage());
            return $dadosPadrao;
        }
    }

    /**
     * Upload para S3
     */
    private function uploadToS3(string $localFilePath, string $s3FilePath): void
    {
        $fileContent = file_get_contents($localFilePath);
        Storage::disk('disco-exportacao')->put($s3FilePath, $fileContent, 'public');
        \Log::info("Arquivo enviado para disco-exportacao: {$s3FilePath}");
    }

    /**
     * Limpar em caso de falha
     */
    public function failed(\Throwable $exception)
    {
        \Log::error('Job JobExportaAvaliacoesCsv falhou: ' . $exception->getMessage());
        $this->releaseLock();
    }
}
