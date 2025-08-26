<?php

namespace App\Jobs;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Models\Arquivo;
use App\Models\Exportacao;
use App\Models\User;
use App\Services\Entrevistas\ParecerRotaFilter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class JobExportaParecerRota implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $requestData;
    protected $fileName;
    protected mixed $cacheKey;

    public int $timeout = 900; // Tempo máximo de execução do job (15 minutos)
    public int $tries = 3; // Número de tentativas antes de falhar
    public int $backoff = 10; // Tempo de espera entre as tentativas (em segundos)

    /**
     * Quantidade de registros por chunk
     */
    const CHUNK_QNT = 100;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId, array $requestData, string $fileName, string $cacheKey = null)
    {
        $this->userId = $userId;
        $this->requestData = $requestData;
        $this->fileName = $fileName;
        $this->cacheKey = $cacheKey;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Atualizar cache com tentativa atual
            $this->updateCacheStatus('processing', [
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries
            ]);

            \Auth::loginUsingId($this->userId);

            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', 0);

            if (function_exists('gc_enable')) {
                gc_enable();
            }

            // Valida se o usuário existe antes de prosseguir
            $user = User::find($this->userId);
            if (!$user) {
                throw new \Exception("Usuário {$this->userId} não encontrado");
            }

            \Log::info("Iniciando exportação de Parecer Rota para usuário: {$this->userId} (Tentativa {$this->attempts()}/{$this->tries})");

            // Constrói a query usando o filtro
            $query = $this->buildQuery()->orderBy('curriculos.nome', 'ASC');

            // Processa os dados
            $this->processDataInChunks($query);

            // Coleta de lixo final
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }

            \Log::info("Exportação de Parecer Rota concluída com sucesso para usuário: {$this->userId}");

            // Sucesso: Remove cache apenas aqui
            $this->completeExport();

        } catch (\Exception $e) {
            \Log::error('Erro ao exportar parecer rota: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('User ID: ' . $this->userId);
            \Log::error('Request data: ' . json_encode($this->requestData));
            \Log::error("Tentativa {$this->attempts()}/{$this->tries} falhou");

            // Apenas atualiza o status da tentativa
            $this->updateCacheStatus('retrying', [
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries,
                'last_error' => $e->getMessage()
            ]);

            // Re-lança a exceção para que o Laravel possa gerenciar o retry/failure
            throw $e;
        }
    }

    /**
     * Constrói a query usando o ParecerRotaFilter
     */
    private function buildQuery()
    {
        $query = ParecerRotaFilter::forUser($this->userId)
            ->apply($this->requestData);

        // Aplica filtro de selecionados se existir
        if (isset($this->requestData['selecionados']) && !empty($this->requestData['selecionados'])) {
            $query->whereIds($this->requestData['selecionados']);
        }

        return $query->getQuery();
    }

    /**
     * Processa os dados em chunks e escreve no Excel
     */

    private function processDataInChunks($query): void
    {
        $head = [
            "Cod",
            "Nome",
            "Destro/Canhoto",
            "Nascimento",
            "Idade",
            "Endereço",
            "Contato",
            "E-mail",
            "Vaga",
            "Parecer RH Nota",
            "Tem Rota que atende",
            "Qual",
            "Bairro Rota",
            "Ponto de referência Rota",
            "Informado sobre ponto de referência",
            "Qual",
            "Bairro Residência",
            "Ponto de referência Residência",
            "Autorizado Vale Transporte",
            "Turno A",
            "Turno B",
            "Turno C",
            "Outros",
            "Parecer Final Rota Atende",
            "Parecer Final Rota Tipo de Contratação",
            "Entrevista Técnica Nota",
            "Teste Prático Nota",
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Define o cabeçalho
        $sheet->fromArray($head, null, 'A1');

        // Formata o cabeçalho
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '366092']
            ]
        ];

        // Corrigindo o range para cobrir todas as colunas (A até AD = 30 colunas)
        $sheet->getStyle('A1:AD1')->applyFromArray($headerStyle);

        $currentRow = 2;
        $chunkCount = 0;
        $totalProcessed = 0;

        \Log::info("Iniciando processamento em chunks para usuário: {$this->userId}");

        $query->chunk(self::CHUNK_QNT, function ($results) use ($sheet, &$currentRow, &$chunkCount, &$totalProcessed) {
            $rows = [];

            foreach ($results as $item) {
                try {
                    // Calcula idade se nascimento existir
                    $idade = null;
                    if ($item->Curriculo && $item->Curriculo->nascimento) {
                        try {
                            // Tenta diferentes formatos de data
                            $nascimentoStr = $item->Curriculo->nascimento;

                            if (strpos($nascimentoStr, '/') !== false) {
                                // Formato DD/MM/YYYY
                                $nascimento = \Carbon\Carbon::createFromFormat('d/m/Y', $nascimentoStr);
                            } else {
                                // Formato padrão YYYY-MM-DD ou outros formatos que o Carbon consegue interpretar
                                $nascimento = \Carbon\Carbon::parse($nascimentoStr);
                            }

                            $idade = $nascimento->age;
                        } catch (\Exception $e) {
                            \Log::warning("Erro ao calcular idade para item {$item->id}: " . $e->getMessage());
                            $idade = null;
                        }
                    }

                    // Monta endereço completo
                    $endereco = '';
                    if ($item->Curriculo) {
                        $enderecoPartes = array_filter([
                            $item->Curriculo->logradouro,
                            $item->Curriculo->complemento,
                            $item->Curriculo->bairro,
                            $item->Curriculo->municipio,
                            $item->Curriculo->uf,
                            $item->Curriculo->cep
                        ]);
                        $endereco = implode(', ', $enderecoPartes);
                    }

                    // Mapear escolaridade (pode precisar de um array de mapeamento se for código)
                    $escolaridade = $item->Curriculo ? $item->Curriculo->formacao : null;

                    $rows[] = [
                        // 1. Id
                        $item->Curriculo ? $item->Curriculo->id : null,
                        // 2. Nome
                        $item->Curriculo ? $item->Curriculo->nome : null,
                        // 3. Destro/Canhoto
                        $item->parecerRh ? $item->parecerRh->destro : null,
                        // 4. Nascimento
                        $item->Curriculo ? $item->Curriculo->nascimento : null,
                        // 5. Idade
                        $idade,
                        // 6. Endereço
                        $endereco,
                        // 7. Contato (assumindo telefone_id, pode precisar de join adicional)
                        $item->TelPrincipal ? $item->TelPrincipal->numero : null,
                        // 8. E-mail
                        $item->Curriculo ? $item->Curriculo->email : null,
                        // 10. Vaga
                        $item->VagaAberta ? $item->VagaAberta->titulo : null,
                        // 12. Parecer RH Nota
                        $item->parecerRh ? $item->parecerRh->nota : null,
                        // 13. Tem Rota que atende
                        $item->parecerRota ? ($item->parecerRota->tem_rota ? 'Sim' : 'Não') : null,
                        // 14. Qual
                        $item->parecerRota ? $item->parecerRota->qual : null,
                        // 15. Bairro Rota
                        $item->parecerRota ? $item->parecerRota->bairro_rota : null,
                        // 16. Ponto de referência Rota
                        $item->parecerRota ? $item->parecerRota->ponto_referencia_rota : null,
                        // 17. Informado sobre ponto de referência (campo pega_onibus)
                        $item->parecerRota ? ($item->parecerRota->pega_onibus ? 'Sim' : 'Não') : null,
                        // 18. Qual (segundo campo "Qual" - pode ser outro campo do parecer)
                        $item->parecerRota ? $item->parecerRota->pega_onibus_qual_ponto : null,
                        // 19. Bairro Residência
                        $item->parecerRota ? $item->parecerRota->bairro_residencia : null,
                        // 20. Ponto de referência Residência
                        $item->parecerRota ? $item->parecerRota->ponto_referencia_residencia : null,
                        // 21. Autorizado Vale Transporte
                        $item->parecerRota ? ($item->parecerRota->vale_transporte ? 'Sim' : 'Não') : null,
                        // 22. Turno A
                        $item->parecerRota ? ($item->parecerRota->rota_disponivel_turno_a ? 'Sim' : 'Não') : null,
                        // 23. Turno B
                        $item->parecerRota ? ($item->parecerRota->rota_disponivel_turno_b ? 'Sim' : 'Não') : null,
                        // 24. Turno C
                        $item->parecerRota ? ($item->parecerRota->rota_disponivel_turno_c ? 'Sim' : 'Não') : null,
                        // 25. Outros
                        $item->parecerRota ? $item->parecerRota->rota_disponivel_outros : null,
                        // 26. Parecer Final Rota Rota Atende
                        $item->parecerRota ? ($item->parecerRota->rota_atende ? 'Sim' : 'Não') : null,
                        // 27. Parecer Final Rota Tipo de Contratação
                        $item->parecerRota ? $item->parecerRota->rota_tipo : null,
                        // 29. Entrevista Técnica Nota
                        $item->parecerTecnica ? $item->parecerTecnica->nota : null,
                        // 30. Teste Prático Nota
                        $item->parecerTeste ? $item->parecerTeste->nota_teste : null,
                    ];

                    $totalProcessed++;

                } catch (\Exception $e) {
                    \Log::warning("Erro ao processar item {$item->id}: " . $e->getMessage());
                    continue;
                }
            }

            if (!empty($rows)) {
                $sheet->fromArray($rows, null, 'A' . $currentRow);
                $currentRow += count($rows);
            }

            unset($rows);
            $chunkCount++;

            // Atualiza progresso no cache
            if ($chunkCount % 5 === 0) {
                $this->updateCacheStatus('processing', [
                    'progress' => min(90, ($chunkCount * 10)),
                    'chunks_processed' => $chunkCount,
                    'records_processed' => $totalProcessed
                ]);
            }

            // Coleta de lixo a cada 10 chunks
            if ($chunkCount % 10 === 0) {
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
                \Log::info("Processados {$chunkCount} chunks, {$totalProcessed} registros. Memória atual: " . memory_get_usage(true) / 1024 / 1024 . " MB");
            }
        });

        \Log::info("Processamento concluído. Total: {$totalProcessed} registros em {$chunkCount} chunks");

        // Auto-ajusta largura das colunas (corrigindo para todas as 30 colunas)
        foreach (range('A', 'AD') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Salva arquivo
        $this->saveFile($spreadsheet);
    }

    /**
     * Atualiza o status no cache sem remover
     */
    private function updateCacheStatus($status, $additionalData = []): void
    {
        if (!$this->cacheKey || !Cache::has($this->cacheKey)) {
            return;
        }

        $cacheData = Cache::get($this->cacheKey);
        $cacheData['status'] = $status;
        $cacheData['updated_at'] = now();

        // Merge dados adicionais
        foreach ($additionalData as $key => $value) {
            $cacheData[$key] = $value;
        }

        $expiresAt = $cacheData['expires_at'] ?? now()->addMinutes(15);
        Cache::put($this->cacheKey, $cacheData, $expiresAt);

        if ($status === 'retrying') {
            \Log::warning("PARECER_ROTA_EXPORT_RETRY", [
                'user_id' => $this->userId,
                'attempt' => $additionalData['attempt'] ?? 0,
                'max_tries' => $additionalData['max_tries'] ?? 3,
                'last_error' => $additionalData['last_error'] ?? 'Unknown'
            ]);
        }
    }

    /**
     * Completa a exportação com sucesso
     */
    private function completeExport(): void
    {
        Cache::forget($this->cacheKey);

        \Log::info("PARECER_ROTA_EXPORT_SUCCESS", [
            'user_id' => $this->userId,
            'filename' => $this->fileName,
            'attempts' => $this->attempts(),
            'cache_key' => $this->cacheKey
        ]);
    }

    /**
     * Handle a job failure
     */
    public function failed(\Throwable $exception): void
    {
        Cache::forget($this->cacheKey);

        \Log::error("PARECER_ROTA_EXPORT_FAILED_FINAL", [
            'user_id' => $this->userId,
            'filename' => $this->fileName,
            'total_attempts' => $this->tries,
            'error_message' => $exception->getMessage(),
            'error_file' => $exception->getFile(),
            'error_line' => $exception->getLine(),
            'cache_key' => $this->cacheKey,
            'stack_trace' => $exception->getTraceAsString()
        ]);

        try {
            // Notificação de falha (usando valores padrão se constantes não existirem)
            Event::dispatch(new NotificacaoEvent([
                'user_id' => $this->userId,
                'local' => 'Parecer Rota',
                'error_message' => "Exportação falhou após {$this->tries} tentativas: " . $exception->getMessage(),
                'filename' => $this->fileName
            ], 'exportacao_falha', 'erro'));

            \Log::info("PARECER_ROTA_EXPORT_NOTIFICATION_SENT", [
                'user_id' => $this->userId,
                'type' => 'failure'
            ]);

        } catch (\Exception $e) {
            \Log::error("PARECER_ROTA_EXPORT_NOTIFICATION_FAILED", [
                'user_id' => $this->userId,
                'notification_error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Salva o arquivo Excel no S3
     */
    private function saveFile($spreadsheet): void
    {
        try {
            $writer = new Xlsx($spreadsheet);

            // Cria arquivo temporário local
            $tempFile = storage_path('app/temp/' . $this->fileName);

            if (!file_exists(dirname($tempFile))) {
                mkdir(dirname($tempFile), 0755, true);
            }

            // Salva temporariamente no servidor
            $writer->save($tempFile);

            // Upload para S3 usando o disco de exportação
            $s3Path = Storage::disk(Arquivo::DISCO_EXPORTACAO)
                ->putFile('', new \Illuminate\Http\File($tempFile), 'private');

            if (!$s3Path) {
                throw new \Exception('Falha ao fazer upload para S3');
            }

            // Remove arquivo temporário do servidor
            unlink($tempFile);

            $local = "Parecer - Rota";

            // Registra na tabela de exportações
            Exportacao::create([
                'user_id' => $this->userId,
                'arquivo' => basename($s3Path),
                'local' => $local,
                'removido' => false,
            ]);

            // Libera memória
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }

            // Dispara evento de notificação (usando valores padrão)
            Event::dispatch(new NotificacaoEvent([
                'user_id' => $this->userId,
                'local' => $local,
            ], 'exportacao_excel', 'padrao'));

            \Log::info("Exportação de Parecer Rota concluída e salva no S3. Arquivo: {$s3Path}. Memória final: " . memory_get_usage(true) / 1024 / 1024 . " MB");

        } catch (\Exception $e) {
            // Remove arquivo temporário se existir
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }

            \Log::error("Erro ao salvar arquivo: " . $e->getMessage());
            throw $e;
        }
    }
}
