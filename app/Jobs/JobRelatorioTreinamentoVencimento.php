<?php

namespace App\Jobs;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Models\Arquivo;
use App\Models\CentroCusto;
use App\Models\Exportacao;
use App\Models\User;
use App\Services\Relatorios\TreinamentoVencimentoRelatorioService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use MasterTag\DataHora;
use Illuminate\Http\File;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class JobRelatorioTreinamentoVencimento implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $requestData;
    protected $fileName;
    protected $cacheKey;

    public int $timeout = 900; // 15 minutos
    public int $tries = 3; // 3 tentativas
    public int $backoff = 10; // 10 segundos entre tentativas

    /**
     * Quantidade de registros por chunk para otimização de memória
     */
    const CHUNK_QNT = 100;

    /**
     * Create a new job instance.
     */
    public function __construct(int $userId, array $requestData, string $fileName, string $cacheKey)
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
            // Configurações de performance
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', 0);

            if (function_exists('gc_enable')) {
                gc_enable();
            }

            // Fazer login do usuário para os scopes globais funcionarem
            Auth::loginUsingId($this->userId);

            // Valida usuário
            $user = User::find($this->userId);
            if (!$user) {
                throw new \Exception("Usuário {$this->userId} não encontrado");
            }

            \Log::info("Iniciando exportação de vencimento de treinamentos para usuário: {$this->userId} (Tentativa {$this->attempts()}/{$this->tries})");

            // Atualizar status para processando
            $this->updateCacheStatus('processing', [
                'attempt' => $this->attempts(),
                'progress' => 0
            ]);

            // Processar os dados usando a mesma lógica do TreinamentoController
            $this->processVencimentoData($user);

            // Coleta de lixo final
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }

            \Log::info("Exportação de vencimento concluída com sucesso para usuário: {$this->userId}");

            // Marcar como concluído
            $this->completeExport();

        } catch (\Exception $e) {
            \Log::error('Erro ao exportar vencimento de treinamentos: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('User ID: ' . $this->userId);
            \Log::error('Request data: ' . json_encode($this->requestData));
            \Log::error("Tentativa {$this->attempts()}/{$this->tries} falhou");

            // Atualizar status para retry
            $this->updateCacheStatus('retrying', [
                'attempt' => $this->attempts(),
                'max_tries' => $this->tries,
                'last_error' => $e->getMessage()
            ]);

            // Re-lança exceção para que Laravel gerencie retry/failure
            throw $e;
        }
    }

    /**
     * Processa os dados de vencimento com a mesma regra da tela (serviço único).
     */
    private function processVencimentoData(User $user): void
    {
        $service = app(TreinamentoVencimentoRelatorioService::class);
        $periodo = $service->resolverPeriodo($this->requestData['periodo'] ?? null);

        $filtros = $this->requestData;
        $filtros['periodo'] = $periodo['periodo_input'];
        $filtros['vencimento'] = $periodo['inicio_str'] . ' até ' . $periodo['fim_str'];
        $filtros['campoDemitido'] = false;
        $filtros['campoVencimento'] = 'true';

        \Log::info("Processando vencimento - Período: {$periodo['inicio_str']} até {$periodo['fim_str']}");

        try {
            $baseQuery = $service->montarQuery($user, $filtros, $periodo['inicio'], $periodo['fim']);
            \Log::info('Query preparada - iniciando processamento (regra única Excel/tela)');
        } catch (\Exception $e) {
            \Log::error('Erro ao montar query de vencimento: ' . $e->getMessage());
            throw $e;
        }

        $cc = (new CentroCusto())->listaCentroCustoPorCnpj($user->empresa_id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Configurar propriedades do documento
        $spreadsheet->getProperties()
            ->setCreator("MyBPIN Sistema")
            ->setTitle("Relatório de Vencimento de Treinamentos")
            ->setSubject("Treinamentos Vencidos e Próximos ao Vencimento")
            ->setDescription("Relatório de treinamentos vencidos e próximos a vencer")
            ->setKeywords('treinamentos vencimentos mybp')
            ->setCategory('Relatórios');

        // Adicionar cabeçalho da empresa igual ao comando TreinamentoVencimento
        $this->adicionarCabecalhoEmpresa($sheet, $user);

        // Cabeçalho das colunas igual ao frontend
        $linhaCabecalho = 6;
        $cabecalhos = [
            'A' => 'Nome',
            'B' => 'Cargo',
            'C' => 'CNPJ da Empresa',
            'D' => 'Empresa',
            'E' => 'Centro de Custo',
            'F' => 'Padrão de Treinamento',
            'G' => 'Tipo',
            'H' => 'Treinamento',
            'I' => 'Descrição',
            'J' => 'Data Treinamento',
            'K' => 'Data Vencimento',
            'L' => 'Dias para Vencer',
            'M' => 'Status'
        ];

        foreach ($cabecalhos as $coluna => $titulo) {
            $sheet->setCellValue($coluna . $linhaCabecalho, $titulo);
        }

        // Estilizar cabeçalho igual ao comando TreinamentoVencimento
        $this->aplicarEstiloCabecalho($sheet, $linhaCabecalho);

        $currentRow = $linhaCabecalho + 1; // Começar após o cabeçalho
        $totalProcessed = 0;

        // Processar dados em chunks - otimização de memória
        $baseQuery->chunk(self::CHUNK_QNT, function ($dados) use ($sheet, &$currentRow, &$totalProcessed, $cc, $service) {
            $rows = [];

            foreach ($dados as $feedback) {
                try {
                    foreach ($service->mapearLinhasExcel($feedback, $cc) as $linha) {
                        $treinamento = $linha['treinamento'];
                        $baseData = $linha['base'];

                        $rows[] = [
                            'data' => [
                                $baseData['nome'],
                                $baseData['cargo'],
                                $baseData['emp_cnpj'],
                                $baseData['emp_nome_fantasia'],
                                $baseData['emp_centro_custo'],
                                $baseData['segmento'],
                                $baseData['tipo'],
                                $treinamento['label'],
                                $treinamento['descricao'],
                                $treinamento['data_treinamento'] ? (new DataHora($treinamento['data_treinamento']))->dataCompleta() : '',
                                $treinamento['data_vencimento'] ? (new DataHora($treinamento['data_vencimento']))->dataCompleta() : '',
                                $treinamento['dias_vencer'],
                                $linha['status'],
                            ],
                            'categoria' => $linha['categoria'],
                        ];
                    }

                    $totalProcessed++;
                } catch (\Exception $e) {
                    \Log::error("Erro ao processar feedback {$feedback->id}: " . $e->getMessage());
                    continue;
                }
            }

            // Adicionar linhas ao spreadsheet com pintura e bordas
            if (!empty($rows)) {
                foreach ($rows as $rowInfo) {
                    $rowData = $rowInfo['data'];
                    $categoria = $rowInfo['categoria'];
                    
                    // Adicionar dados à linha
                    $col = 'A';
                    foreach ($rowData as $value) {
                        $sheet->setCellValue($col . $currentRow, $value);
                        $col++;
                    }
                    
                    // Aplicar cor baseada na categoria
                    $this->aplicarCorPorCategoria($sheet, $currentRow, $categoria);
                    
                    // Aplicar bordas na linha
                    $this->aplicarBordasLinha($sheet, $currentRow);
                    
                    $currentRow++;
                }
            }
            
            // Atualizar progresso
            $progress = min(95, ($totalProcessed / 100) * 95); // 95% no máximo durante processamento
            $this->updateCacheStatus('processing', [
                'progress' => $progress,
                'processed_records' => $totalProcessed
            ]);

            \Log::info("Chunk processado - Total processados: {$totalProcessed}");
            
            // Liberar memória do chunk
            unset($rows, $dados);
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        });

        \Log::info("Processamento concluído - Total de registros: {$totalProcessed}");
        
        // Atualizar progresso para finalização
        $this->updateCacheStatus('processing', [
            'progress' => 98,
            'processed_records' => $totalProcessed,
            'status_message' => 'Salvando arquivo...'
        ]);

        // Salvar arquivo final
        $this->saveSpreadsheet($spreadsheet, $totalProcessed);
    }

    /**
     * Salva o spreadsheet e envia notificação
     */
    private function saveSpreadsheet(Spreadsheet $spreadsheet, int $totalRecords): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'export_vencimento_') . '.xlsx';
        
        try {
            // Garante que o diretório existe
            if (!file_exists(dirname($tempFile))) {
                mkdir(dirname($tempFile), 0755, true);
            }

            // Salva temporariamente no servidor
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);

            // Construir nome do arquivo como no JobExportaCihCsvFinal
            $nomeArquivo = "treinamento_vencimento_" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
            
            // Upload para S3 usando nome específico - igual ao JobExportaCihCsvFinal
            $fileContent = file_get_contents($tempFile);
            Storage::disk(Arquivo::DISCO_EXPORTACAO)->put($nomeArquivo, $fileContent);

            // Remove arquivo temporário do servidor
            unlink($tempFile);

            $local = "Relatório Vencimento Treinamentos";
            
            // Criar registro de exportação - igual ao JobExportaCihCsvFinal
            Exportacao::create([
                'user_id' => $this->userId,
                'arquivo' => $nomeArquivo, // Nome específico do arquivo
                'local' => $local, // Nome amigável
                'removido' => false,
            ]);
            
            // Progresso 100%
            $this->updateCacheStatus('processing', [
                'progress' => 100,
                'processed_records' => $totalRecords,
                'status_message' => 'Arquivo salvo com sucesso!'
            ]);
            
            // Dispara evento de notificação - igual ao JobExportaCihCsvFinal
            Event::dispatch(new NotificacaoEvent([
                'user_id' => $this->userId,
                'local' => $local,
            ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));
            
            \Log::info("Exportação de vencimento concluída: {$nomeArquivo} - {$totalRecords} registros");
            
        } catch (\Exception $e) {
            \Log::error("Erro ao salvar arquivo de vencimento: " . $e->getMessage());
            throw $e;
        } finally {
            // Liberar memória do spreadsheet
            unset($spreadsheet);
        }
    }

    /**
     * Atualiza o status no cache
     */
    private function updateCacheStatus(string $status, array $additionalData = []): void
    {
        if (!$this->cacheKey || !Cache::has($this->cacheKey)) {
            return;
        }

        $cacheData = Cache::get($this->cacheKey);
        $cacheData['status'] = $status;
        $cacheData['updated_at'] = now();
        
        foreach ($additionalData as $key => $value) {
            $cacheData[$key] = $value;
        }

        Cache::put($this->cacheKey, $cacheData, now()->addMinutes(15));
    }

    /**
     * Marca exportação como concluída
     */
    private function completeExport(): void
    {
        if ($this->cacheKey && Cache::has($this->cacheKey)) {
            $cacheData = Cache::get($this->cacheKey);
            $cacheData['status'] = 'completed';
            $cacheData['completed_at'] = now();
            $cacheData['progress'] = 100;
            
            // Manter no cache por mais 5 minutos para status
            Cache::put($this->cacheKey, $cacheData, now()->addMinutes(5));
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        \Log::error("Job de exportação de vencimento falhou definitivamente para usuário {$this->userId}: " . $exception->getMessage());
        
        // Marcar como falhado no cache
        if ($this->cacheKey && Cache::has($this->cacheKey)) {
            $cacheData = Cache::get($this->cacheKey);
            $cacheData['status'] = 'failed';
            $cacheData['failed_at'] = now();
            $cacheData['last_error'] = $exception->getMessage();
            
            Cache::put($this->cacheKey, $cacheData, now()->addMinutes(30));
        }
    }

    /**
     * Adiciona cabeçalho da empresa na planilha - baseado no comando TreinamentoVencimento
     */
    private function adicionarCabecalhoEmpresa($sheet, User $user): void
    {
        $empresa = $user->empresa ?? null;
        $empresaNome = $empresa->razao_social ?? 'Empresa não identificada';
        $empresaCnpj = $empresa->cnpj ?? 'CNPJ não informado';
        
        // Título principal
        $sheet->setCellValue('A1', 'RELATÓRIO DE VENCIMENTOS DE TREINAMENTOS');
        $sheet->mergeCells('A1:M1');

        // Informações da empresa
        $sheet->setCellValue('A2', "Empresa: {$empresaNome}");
        $sheet->mergeCells('A2:M2');

        $sheet->setCellValue('A3', "CNPJ: {$empresaCnpj}");
        $sheet->mergeCells('A3:M3');

        $sheet->setCellValue('A4', "Data de Geração: " . date('d/m/Y H:i:s'));
        $sheet->mergeCells('A4:M4');

        // Linha em branco
        $sheet->setCellValue('A5', '');

        // Estilizar cabeçalho da empresa
        try {
            $sheet->getStyle('A1:M1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
            ]);

            $sheet->getStyle('A2:M4')->applyFromArray([
                'font' => ['bold' => true, 'size' => 12]
            ]);
        } catch (\Exception $e) {
            \Log::warning("Não foi possível aplicar estilo ao cabeçalho da empresa: {$e->getMessage()}");
            // Aplicar estilo básico como fallback
            $sheet->getStyle('A1:M1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A2:M4')->getFont()->setBold(true)->setSize(12);
        }
    }

    /**
     * Aplica estilo ao cabeçalho das colunas - baseado no comando TreinamentoVencimento
     */
    private function aplicarEstiloCabecalho($sheet, int $linha): void
    {
        try {
            $sheet->getStyle("A{$linha}:M{$linha}")->applyFromArray([
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
            \Log::warning("Não foi possível aplicar estilo ao cabeçalho: {$e->getMessage()}");
            // Aplicar estilo básico como fallback
            $sheet->getStyle("A{$linha}:M{$linha}")->getFont()->setBold(true);
        }
    }

    /**
     * Aplica cor baseada na categoria - baseado no comando TreinamentoVencimento
     */
    private function aplicarCorPorCategoria($sheet, int $linha, string $categoria): void
    {
        $cor = '';
        switch ($categoria) {
            case 'VENCIDO':
                $cor = 'FFE6E6'; // Vermelho claro
                break;
            case 'PROXIMO':
                $cor = 'FFF2E6'; // Laranja claro
                break;
            case 'ATENCAO':
                $cor = 'FFFFCC'; // Amarelo claro
                break;
            // REGULAR não tem cor (fundo branco)
        }

        if ($cor) {
            try {
                $sheet->getStyle("A{$linha}:M{$linha}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $cor]
                    ]
                ]);
            } catch (\Exception $e) {
                \Log::warning("Não foi possível aplicar cor à linha {$linha}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Aplica bordas em uma linha específica da tabela
     */
    private function aplicarBordasLinha($sheet, int $linha): void
    {
        try {
            $sheet->getStyle("A{$linha}:M{$linha}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::warning("Não foi possível aplicar bordas à linha {$linha}: {$e->getMessage()}");
        }
    }
}
