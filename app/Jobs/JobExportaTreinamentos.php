<?php

namespace App\Jobs;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Models\Arquivo;
use App\Models\Exportacao;
use App\Models\User;
use App\Services\Treinamento\FeedbackCurriculoFilter;
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

class JobExportaTreinamentos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $requestData;
    protected $fileName;
    protected mixed $cacheKey;

    public int $timeout = 900; // Tempo máximo de execução do job (15 minutos)

    /**
     * Número de tentativas e tempo de espera entre elas
     * Ajuste conforme necessário para o seu ambiente
     */
    public int $tries = 3; // Número de tentativas antes de falhar
    public int $backoff = 10; // Tempo de espera entre as tentativas (em segundos)

    /**
     * Quantidade de registros por chunk
     * Ajuste conforme necessário para o seu ambiente
     */
    const CHUNK_QNT = 100; // Quantidade de registros por chunk

    /**
     * Create a new job instance.
     *
     * @param int $userId
     * @param array $requestData
     * @param string $fileName
     * @param string|null $cacheKey
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
     * @return void
     * @throws \Exception
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

            \Log::info("Iniciando exportação para usuário: {$this->userId} (Tentativa {$this->attempts()}/{$this->tries})");

            // Usa o filtro sem login automático para evitar problemas de contexto
            $query = FeedbackCurriculoFilter::forUser($this->userId)
                ->apply($this->requestData);

            // Aplica filtro de selecionados se existir
            if (isset($this->requestData['selecionados']) && !empty($this->requestData['selecionados'])) {
                $query->whereIds($this->requestData['selecionados']);
            }

            // Processa os dados
            $this->processDataInChunks($query->getQuery());

            // Coleta de lixo final
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }

            \Log::info("Exportação concluída com sucesso para usuário: {$this->userId}");

            // ✅ SUCESSO: Remove cache apenas aqui
            $this->completeExport();

        } catch (\Exception $e) {
            \Log::error('Erro ao exportar treinamentos: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('User ID: ' . $this->userId);
            \Log::error('Request data: ' . json_encode($this->requestData));
            \Log::error("Tentativa {$this->attempts()}/{$this->tries} falhou");

            // ✅ CORREÇÃO: NÃO remove o cache aqui - deixa o Laravel tentar novamente
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

        // ✅ BACKGROUND: TTL menor (só para duração do job)
        $expiresAt = $cacheData['expires_at'] ?? now()->addMinutes(15);
        Cache::put($this->cacheKey, $cacheData, $expiresAt);

        // ✅ Log para monitoramento
        if ($status === 'retrying') {
            \Log::warning("EXPORT_RETRY", [
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
        // ✅ BACKGROUND: Remove cache imediatamente (não precisa manter)
        Cache::forget($this->cacheKey);

        // ✅ IMPORTANTE: Log estruturado para monitoramento
        \Log::info("EXPORT_SUCCESS", [
            'user_id' => $this->userId,
            'filename' => $this->fileName,
            'attempts' => $this->attempts(),
            'duration' => now()->diffInSeconds(Cache::get($this->cacheKey)['initiated_at'] ?? now()),
            'cache_key' => $this->cacheKey
        ]);
    }

    /**
     * Handle a job failure - APENAS AQUI o cache é removido definitivamente
     */
    public function failed(\Throwable $exception): void
    {
        // ✅ BACKGROUND: Remove cache imediatamente
        Cache::forget($this->cacheKey);

        // ✅ IMPORTANTE: Log estruturado para alertas/monitoramento
        \Log::error("EXPORT_FAILED_FINAL", [
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
            // ✅ CRÍTICO: Notificação de falha para background job
//            Event::dispatch(new NotificacaoEvent([
//                'user_id' => $this->userId,
//                'local' => 'Carteira Treinamentos',
//                'error_message' => "Exportação falhou após {$this->tries} tentativas: " . $exception->getMessage(),
//                'filename' => $this->fileName
//            ], NotificacaoEvent::EXPORTACAO_EXCEL_FALHA ?? 'exportacao_falha', NotificacaoEvent::TIPO_ERRO ?? 'erro'));

            \Log::info("EXPORT_NOTIFICATION_SENT", [
                'user_id' => $this->userId,
                'type' => 'failure'
            ]);

        } catch (\Exception $e) {
            \Log::error("EXPORT_NOTIFICATION_FAILED", [
                'user_id' => $this->userId,
                'notification_error' => $e->getMessage()
            ]);
        }
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    private function processDataInChunks($query): void
    {
        $head = [
            "Nome", "Cargo", "Status", "Data Admissão", "PCD", "Área",
            "Foto 3x4", "Treinamento", "Data do treinamento", "Data do vencimento",
            "Status Vencimento", "Dias para Vencer/Vencido", "Ultima Atualização"
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

        $sheet->getStyle('A1:M1')->applyFromArray($headerStyle);

        $currentRow = 2;
        $chunkCount = 0;
        $totalProcessed = 0;
        $treinamentos_selecionados = $this->requestData['treinamentos_selecionados'] ?? [];
        $rowsWithStatus = []; // Para aplicar formatação condicional depois

        \Log::info("Iniciando processamento em chunks para usuário: {$this->userId}");

        $query->chunk(self::CHUNK_QNT, function ($results) use ($sheet, &$currentRow, $treinamentos_selecionados, &$chunkCount, &$totalProcessed, &$rowsWithStatus) {
            $rows = [];

            foreach ($results as $item) {
                try {
                    $itemArray = $item->toArray();

                    // Filtra vencimentos se necessário
                    $vencimentos = $itemArray['treinamento']['vencimentos'] ?? [];
                    if (count($treinamentos_selecionados) > 0) {
                        $vencimentos = array_filter($vencimentos, function ($vencimento) use ($treinamentos_selecionados) {
                            return in_array($vencimento['label'], $treinamentos_selecionados);
                        });
                    }

                    if (empty($vencimentos)) {
                        $rowData = $this->buildRowData($itemArray, null);
                        $rows[] = $rowData;
                    } else {
                        foreach ($vencimentos as $vencimento) {
                            $rowData = $this->buildRowData($itemArray, $vencimento);
                            $rows[] = $rowData;

                            // Armazena informação do status para formatação
                            $statusVencimento = $rowData[10]; // Coluna K (Status Vencimento)
                            $rowsWithStatus[$currentRow + count($rows) - 1] = $statusVencimento;
                        }
                    }

                    $totalProcessed++;
                    unset($itemArray, $vencimentos);

                } catch (\Exception $e) {
                    \Log::warning("Erro ao processar item {$item->id}: " . $e->getMessage());
                    // Continua com o próximo item
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
                    'progress' => min(90, ($chunkCount * 10)), // Aproximação do progresso
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

        // Aplica formatação condicional
        $this->applyConditionalFormatting($sheet, $rowsWithStatus);

        // Auto-ajusta largura das colunas
        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Salva arquivo
        $this->saveFile($spreadsheet);
    }

    /**
     * Constrói os dados de uma linha para o Excel
     * @param $itemArray
     * @param $vencimento
     * @return array
     */
    private function buildRowData($itemArray, $vencimento = null): array
    {
        // Calcula status de vencimento e dias
        $statusVencimento = '';
        $diasVencimento = '';

        if ($vencimento && isset($vencimento['pivot']['data_vencimento'])) {
            $dataVencimento = $vencimento['pivot']['data_vencimento'];

            // Verifica se a data não está vazia ou nula
            if (!empty($dataVencimento) && $dataVencimento !== '0000-00-00' && $dataVencimento !== '0000-00-00 00:00:00') {
                try {
                    $hoje = \Carbon\Carbon::now()->startOfDay();
                    $dataVenc = $this->parseDataVencimento($dataVencimento);

                    if ($dataVenc) {
                        // diffInDays com false retorna negativo se a data já passou
                        $diasDiferenca = $hoje->diffInDays($dataVenc, false);

                        if ($diasDiferenca < 0) {
                            // Vencido
                            $statusVencimento = 'VENCIDO';
                            $diasAbsolutos = abs($diasDiferenca);
                            $diasVencimento = 'Vencido há ' . $diasAbsolutos . ($diasAbsolutos == 1 ? ' dia' : ' dias');
                        } elseif ($diasDiferenca == 0) {
                            // Vence hoje
                            $statusVencimento = 'VENCE HOJE';
                            $diasVencimento = 'Vence hoje';
                        } elseif ($diasDiferenca <= 30) {
                            // Próximo do vencimento (até 30 dias)
                            $statusVencimento = 'VENCENDO';
                            $diasVencimento = 'Vence em ' . $diasDiferenca . ($diasDiferenca == 1 ? ' dia' : ' dias');
                        } elseif ($diasDiferenca <= 60) {
                            // Atenção (até 60 dias)
                            $statusVencimento = 'PROXIMO A VENCER';
                            $diasVencimento = 'Vence em ' . $diasDiferenca . ($diasDiferenca == 1 ? ' dia' : ' dias');
                        } else {
                            // Em dia (mais de 60 dias)
                            $statusVencimento = 'EM DIA';
                            $diasVencimento = 'Em dia';
                        }
                    } else {
                        $statusVencimento = 'Data inválida';
                        $diasVencimento = 'Formato não reconhecido';
                    }

                } catch (\Exception $e) {
                    $statusVencimento = 'Erro na data';
                    $diasVencimento = 'Erro ao processar';
                    \Log::warning("Erro ao processar data '{$dataVencimento}': " . $e->getMessage());
                }
            } else {
                // Data vazia ou nula
                $statusVencimento = 'Sem data';
                $diasVencimento = 'Não informado';
            }
        }

        return [
            $itemArray['curriculo']['nome'] ?? '',
            $itemArray['admissao']['cargo'] ?? '',
            $itemArray['admissao']['status'] ?? '',
            $itemArray['admissao']['data_admissao'] ?? '',
            ($itemArray['curriculo']['pcd'] ?? false) ? 'Sim' : 'Não',
            $itemArray['admissao']['area_etiqueta']['label'] ?? 'Não informado',
            ($itemArray['curriculo']['foto_tres'] ?? false) ? 'Sim' : 'Não',
            $vencimento['label'] ?? '',
            $vencimento['pivot']['data_treinamento'] ?? '',
            $vencimento['pivot']['data_vencimento'] ?? '',
            $statusVencimento,
            $diasVencimento,
            $itemArray['treinamento']['updated_at'] ?? '',
        ];
    }

    /**
     * Faz parse da data de vencimento com múltiplos formatos
     */
    private function parseDataVencimento($dataString): ?\Carbon\Carbon
    {
        if (empty($dataString)) {
            return null;
        }

        // Remove espaços extras
        $dataString = trim($dataString);

        // Lista de formatos para tentar, priorizando os mais comuns no Brasil
        $formatos = [
            // Formatos brasileiros (dia/mês/ano)
            'd/m/Y H:i:s',     // 21/11/2023 14:30:00
            'd/m/Y',           // 21/11/2023
            'd-m-Y H:i:s',     // 21-11-2023 14:30:00
            'd-m-Y',           // 21-11-2023
            'd.m.Y H:i:s',     // 21.11.2023 14:30:00
            'd.m.Y',           // 21.11.2023

            // Formatos ISO (ano-mês-dia)
            'Y-m-d H:i:s',     // 2023-11-21 14:30:00
            'Y-m-d',           // 2023-11-21
            'Y/m/d H:i:s',     // 2023/11/21 14:30:00
            'Y/m/d',           // 2023/11/21

            // Formatos americanos (mês/dia/ano) - por último
            'm/d/Y H:i:s',     // 11/21/2023 14:30:00
            'm/d/Y',           // 11/21/2023
        ];

        foreach ($formatos as $formato) {
            try {
                $data = \Carbon\Carbon::createFromFormat($formato, $dataString);

                if ($data) {
                    // Verifica se a data é válida (não é 30/02/2023 por exemplo)
                    $errors = \Carbon\Carbon::getLastErrors();
                    if (!$errors || ($errors['warning_count'] == 0 && $errors['error_count'] == 0)) {
                        return $data->startOfDay();
                    }
                }
            } catch (\Exception $e) {
                // Continua tentando o próximo formato
                continue;
            }
        }

        // Tenta usar createFromFormat específico para formato brasileiro
        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dataString)) {
                // Formato dd/mm/yyyy - força interpretação brasileira
                $data = \Carbon\Carbon::createFromFormat('d/m/Y', $dataString);
                if ($data) {
                    return $data->startOfDay();
                }
            }
        } catch (\Exception $e) {
            // Ignora erro
        }

        // Última tentativa: usar Carbon::parse mas configurando locale brasileiro
        try {
            // Temporariamente configura para Brasil
            $originalLocale = \Carbon\Carbon::getLocale();
            \Carbon\Carbon::setLocale('pt_BR');

            $data = \Carbon\Carbon::parse($dataString);

            // Restaura locale original
            \Carbon\Carbon::setLocale($originalLocale);

            return $data->startOfDay();

        } catch (\Exception $e) {
            \Log::warning("Não foi possível fazer parse da data: '{$dataString}' - " . $e->getMessage());
            return null;
        }
    }

    /**
     * Aplica formatação condicional nas células de status e dias
     * @param $sheet
     * @param $rowsWithStatus
     * @return void
     */
    private function applyConditionalFormatting($sheet, $rowsWithStatus): void
    {
        foreach ($rowsWithStatus as $row => $status) {
            $cellRange = "K{$row}:L{$row}"; // Colunas K e L (Status e Dias)

            switch ($status) {
                case 'VENCIDO':
                    $sheet->getStyle($cellRange)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFE6E6'] // Vermelho claro
                        ],
                        'font' => ['color' => ['rgb' => 'CC0000']] // Texto vermelho escuro
                    ]);
                    break;

                case 'VENCE HOJE':
                    $sheet->getStyle($cellRange)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFF2CC'] // Amarelo claro
                        ],
                        'font' => ['color' => ['rgb' => 'B8860B']] // Texto amarelo escuro
                    ]);
                    break;

                case 'VENCENDO':
                    $sheet->getStyle($cellRange)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFE4B5'] // Laranja claro
                        ],
                        'font' => ['color' => ['rgb' => 'FF8C00']] // Texto laranja
                    ]);
                    break;

                case 'PROXIMO A VENCER':
                    $sheet->getStyle($cellRange)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'FFF8DC'] // Bege claro
                        ],
                        'font' => ['color' => ['rgb' => 'D2691E']] // Texto marrom
                    ]);
                    break;

                case 'EM DIA':
                    $sheet->getStyle($cellRange)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'E6FFE6'] // Verde claro
                        ],
                        'font' => ['color' => ['rgb' => '006600']] // Texto verde escuro
                    ]);
                    break;

                case 'Data inválida':
                case 'Erro na data':
                case 'Sem data':
                    $sheet->getStyle($cellRange)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F0F0F0'] // Cinza claro
                        ],
                        'font' => ['color' => ['rgb' => '666666']] // Texto cinza
                    ]);
                    break;
            }
        }
    }

    /**
     * Salva o arquivo Excel no S3
     * @param $spreadsheet
     * @return void
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
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

            // Construir nome do arquivo como no JobExportaCihCsvFinal
            $nomeArquivo = "treinamento_" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
            
            // Upload para S3 usando nome específico - igual ao JobExportaCihCsvFinal
            $fileContent = file_get_contents($tempFile);
            Storage::disk(Arquivo::DISCO_EXPORTACAO)->put($nomeArquivo, $fileContent);

            // Remove arquivo temporário do servidor
            unlink($tempFile);

            $local = "Carteira Treinamentos";

            // Registra na tabela de exportações - igual ao JobExportaCihCsvFinal
            Exportacao::create([
                'user_id' => $this->userId,
                'arquivo' => $nomeArquivo, // Nome específico do arquivo
                'local' => $local, // Nome amigável
                'removido' => false,
            ]);

            // Libera memória
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);

            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }

            // Dispara evento de notificação
            Event::dispatch(new NotificacaoEvent([
                'user_id' => $this->userId,
                'local' => $local,
            ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));

            \Log::info("Exportação concluída e salva no S3. Arquivo: {$nomeArquivo}. Memória final: " . memory_get_usage(true) / 1024 / 1024 . " MB");

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
