<?php

namespace App\Jobs;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Http\Controllers\AdmissaoController;
use App\Models\Arquivo;
use App\Models\Exportacao;
use App\Models\FeedbackCurriculo;
use App\Models\User;
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

class JobExportaAdmissoes implements ShouldQueue
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

            \Log::info("Iniciando exportação de admissões para usuário: {$this->userId} (Tentativa {$this->attempts()}/{$this->tries})");

            // Usa o filtro do AdmissaoController
            $request = new \Illuminate\Http\Request($this->requestData);
            $query = (new AdmissaoController())->filtro($request);

            // Aplica filtro de selecionados se existir
            if (isset($this->requestData['selecionados']) && !empty($this->requestData['selecionados'])) {
                $query->whereIn('id', $this->requestData['selecionados']);
            }

            // Processa os dados
            $this->processDataInChunks($query);

            // Coleta de lixo final
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }

            \Log::info("Exportação de admissões concluída com sucesso para usuário: {$this->userId}");

            // ✅ SUCESSO: Remove cache apenas aqui
            $this->completeExport();

        } catch (\Exception $e) {
            \Log::error('Erro ao exportar admissões: ' . $e->getMessage());
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
            Event::dispatch(new NotificacaoEvent([
                'user_id' => $this->userId,
                'local' => 'Relatório de Admissões',
                'error_message' => "Exportação falhou após {$this->tries} tentativas: " . $exception->getMessage(),
                'filename' => $this->fileName
            ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));

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
            "Nome", "Naturalidade", "Estado Civil", "Sexo", "CPF", "Pai", "Mãe", "PCD",
            "Destro/Canhoto", "CNH", "Nascimento", "Idade", "Formação", "Calça", "Bota",
            "Camisa Meia", "Camisa Proteção", "Empresa", "Vaga", "Ex Funcionário", "Contato",
            "E-mail", "Disponibilidade para turnos 6X2", "Indicado por quem", "Indicado para qual área",
            "Endereço", "Tem Rota que atende", "Qual", "Bairro Rota", "Ponto de referência Rota",
            "Informado sobre ponto de referência", "Qual", "Bairro Residência", "Ponto de referência Residência",
            "Teste aplicado", "Resultado Teste Prático", "Rigger", "Plataforma Móvel", "Ponte Rolante",
            "Encaminhado para Documentos", "Data Encaminhado para Documentos", "Encaminhado para Exame",
            "Data Encaminhado para Exame", "Encaminhado para treinamento", "Data Encaminhado para Treinamento",
            "Área", "Centro de Custo", "CNPJ Filial", "Centro de custo filial", "Função", "Cargo",
            "Salário R$", "Documento", "Documento Portaria", "Tipo de admissão", "Treinamento",
            "Tipo de Treinamento", "Data Treinamento", "Número Crachá", "Data do ASO", "PIS",
            "CTPS", "CTPS Série", "CTPS Data Emissão", "CTPS UF", "Título de Eleitor",
            "Título de Eleitor Sessão", "Título de Eleitor Zona", "Certificado de Reservista",
            "Certificado de Reservista Categoria", "Dependentes", "Conta PIX", "Tipo de Chave PIX",
            "Chave PIX", "Banco", "Agência", "Conta", "Status Carteira de Treinamento e Etiqueta",
            "Status", "Data da Admissão", "Foto", "Quem Admitiu", "Quem Alterou"
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

        $sheet->getStyle('A1:BF1')->applyFromArray($headerStyle);

        $currentRow = 2;
        $chunkCount = 0;
        $totalProcessed = 0;

        \Log::info("Iniciando processamento em chunks para usuário: {$this->userId}");

        $query->chunk(self::CHUNK_QNT, function ($results) use ($sheet, &$currentRow, &$chunkCount, &$totalProcessed) {
            $rows = [];

            foreach ($results as $item) {
                try {
                    $rowData = $this->buildRowData($item);
                    $rows[] = $rowData;
                    $totalProcessed++;

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

        // Auto-ajusta largura das colunas
        foreach (range('A', 'BF') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Salva arquivo
        $this->saveFile($spreadsheet);
    }

    /**
     * Constrói os dados de uma linha para o Excel
     * @param $item
     * @return array
     */
    private function buildRowData($item): array
    {
        $dependentes = "";

        if (count($item->Curriculo->Dependentes ?? []) > 0) {
            $item->Curriculo?->Dependentes?->each(function ($dependente) use (&$dependentes) {
                $cpf = $dependente->cpf ?: "Não informado";
                $nascimento = $dependente->nascimento ?: 'Não informado';
                $dependentes .= "Tipo: ";
                $dependentes .= $dependente->tipo == 'outro' ? $dependente->outro_tipo : \App\Models\UsuarioDependente::TIPOS_DEPENDENTES[$dependente->tipo] ?? $dependente->tipo;
                $dependentes .= "\nNome: " . $dependente->nome;
                $dependentes .= "\nCPF: " . $cpf;
                $dependentes .= "\nData de Nascimento: " . $nascimento;
                $dependentes .= "\n\n";
            });
        }

        return [
            $item->Curriculo?->nome ?? '',
            $item->Curriculo?->naturalidade ?? 'NÃO INFORMADO',
            $item->Curriculo?->estado_civil ?? 'NÃO INFORMADO',
            $item->Curriculo?->sexo ?? 'NÃO INFORMADO',
            $item->Curriculo?->cpf ?? '',
            $item->Curriculo?->filiacao_pai ?? "",
            $item->Curriculo?->filiacao_mae ?? "",
            $item->Curriculo?->pcd ? 'SIM' : 'NÃO',
            $item->parecerRh?->destro ?? 'NÃO INFORMADO',
            $item->parecerRh?->cnh_tipo ?? 'NÃO INFORMADO',
            $item->Curriculo?->nascimento ?? '',
            $item->Curriculo?->idade ?? '',
            $item->Curriculo?->formacao <= 7 ? $item->Curriculo?->Escolaridade?->tipo : $this->getEscolaridade($item),
            $item->parecerRh?->calca ?? 'NÃO INFORMADO',
            $item->parecerRh?->bota ?? 'NÃO INFORMADO',
            $item->parecerRh?->camisa_meia ?? 'NÃO INFORMADO',
            $item->parecerRh?->camisa_protecao ?? 'NÃO INFORMADO',
            $item->empresa->cnpj ? $item->empresa?->razao_social : $item->empresa?->nome,
            $item->VagaAberta?->VagaSelecionada->nome . ' - ' . $item->VagaAberta->Municipio->uf ?? '',
            $item->parecerRh?->ex_funcionario ? 'SIM' : 'NÃO',
            $item->TelPrincipal ? $item->TelPrincipal?->numero : 'NÃO INFORMADO',
            $item->Curriculo->email ?? '',
            $item->parecerRh ? $item->parecerRh?->turnos_seis_por_dois ? 'SIM' : 'NÃO' : 'NÃO INFORMADO',
            $item->parecerRh->indicado_por ?? "",
            $item->parecerTecnica?->indicado_area ?? "",
            $item->Curriculo?->endereco_completo ?? '',
            $item->parecerRota ? $item->parecerRota?->tem_rota ? 'SIM' : 'NÃO' : 'NÃO INFORMADO',
            $item->parecerRota ? $item->parecerRota?->qual ?: 'NÃO INFORMADO' : 'NÃO INFORMADO',
            $item->parecerRota ? $item->parecerRota?->bairro_rota ?: 'NÃO INFORMADO' : 'NÃO INFORMADO',
            $item->parecerRota ? $item->parecerRota?->ponto_referencia_rota ?: 'NÃO INFORMADO' : 'NÃO INFORMADO',
            $item->parecerRota ? $item->parecerRota?->pega_onibus ? 'SIM' : 'NÃO' : 'NÃO INFORMADO',
            $item->parecerRota ? $item->parecerRota?->pega_onibus_qual_ponto ?: 'NÃO INFORMADO' : 'NÃO INFORMADO',
            $item->parecerRota ? $item->parecerRota?->bairro_residencia ?: 'NÃO INFORMADO' : 'NÃO INFORMADO',
            $item->parecerRota ? $item->parecerRota?->ponto_referencia_residencia ?: 'NÃO INFORMADO' : 'NÃO INFORMADO',
            $item->parecerTeste->qual_teste ?? 'NÃO INFORMADO',
            $item->parecerTeste ? $item->parecerTeste?->nota_teste == 0 ? 'Não se Aplica' : $item->parecerTeste?->nota_teste : 'Aguardando',
            $item->parecerTecnica ? $item->parecerTecnica?->experiencia_cargas_rigger ? 'SIM' : 'NÃO' : 'NÃO INFORMADO',
            $item->parecerTecnica ? $item->parecerTecnica?->opera_plat_movel ? 'SIM' : 'NÃO' : 'NÃO INFORMADO',
            $item->parecerTecnica ? $item->parecerTecnica?->opera_plat_ponte ? 'SIM' : 'NÃO' : 'NÃO INFORMADO',
            $item->ResultadoIntegrado?->documentos_entregue ? 'SIM' : 'NÃO',
            $item->ResultadoIntegrado?->documentos_entregue ? (new \MasterTag\DataHora($item->ResultadoIntegrado?->documentos_entregue_data))->dataCompleta() : '',
            $item->ResultadoIntegrado?->encaminhado_exame ? 'SIM' : 'NÃO',
            $item->ResultadoIntegrado?->encaminhado_exame ? (new \MasterTag\DataHora($item->ResultadoIntegrado?->encaminhado_exame_data))->dataCompleta() : '',
            $item->ResultadoIntegrado?->encaminhado_treinamento ? 'SIM' : 'NÃO',
            $item->ResultadoIntegrado?->encaminhado_treinamento ? (new \MasterTag\DataHora($item->ResultadoIntegrado?->encaminhado_treinamento_data))->dataCompleta() : '',
            $item->Admissao->AreaEtiqueta->label ?? "NÃO INFORMADO",
            $item->Admissao->CentroCusto->label ?? "NÃO INFORMADO",
            $item->Admissao?->filial ? 'SIM' : 'NÃO',
            $item->Admissao->CentroCustoFilial?->Filial?->dados->razao_social ?? "",
            $item->Admissao->funcao ?? "NÃO INFORMADO",
            $item->Admissao->cargo ?? "NÃO INFORMADO",
            $item->Admissao->salario ?? "NÃO INFORMADO",
            $item->Admissao->documento ?? "NÃO INFORMADO",
            $item->Admissao->documento_portaria ?? "NÃO INFORMADO",
            $item->Admissao->tipo_admissao ?? "NÃO INFORMADO",
            $item->Admissao->treinamento ?? "NÃO INFORMADO",
            $item->Admissao->tipo_treinamento ?? "NÃO INFORMADO",
            $item->Admissao->data_treinamento ?? "NÃO INFORMADO",
            $item->Admissao->numero_cracha ?? "NÃO INFORMADO",
            $item->Admissao->UltimoAso->data_realizacao ?? "NÃO INFORMADO",
            $item->Admissao->pis ?? "NÃO INFORMADO",
            $item->Admissao?->DadosAdmissoes?->ctps_numero ?? "NÃO INFORMADO",
            $item->Admissao?->DadosAdmissoes?->ctps_serie ?? "NÃO INFORMADO",
            $item->Admissao?->DadosAdmissoes?->ctps_data_emissao ?? "NÃO INFORMADO",
            $item->Admissao?->DadosAdmissoes?->ctps_uf ?? "NÃO INFORMADO",
            $item->Admissao?->DadosAdmissoes?->titulo_eleitor_numero ?? "NÃO INFORMADO",
            $item->Admissao?->DadosAdmissoes?->titulo_eleitor_sessao ?? "NÃO INFORMADO",
            $item->Admissao?->DadosAdmissoes?->titulo_eleitor_zona ?? "NÃO INFORMADO",
            $item->Admissao?->DadosAdmissoes?->cert_reservista_num ?? "NÃO INFORMADO",
            $item->Admissao?->DadosAdmissoes?->cert_reservista_categoria ?? "NÃO INFORMADO",
            $dependentes,
            $item->BancoConta ? $item->BancoConta?->pix ? 'SIM' : 'NÃO' : "NÃO INFORMADO",
            $item->BancoConta?->tipochavepix ?? "",
            $item->BancoConta?->chavepix ?? "",
            $item->BancoConta?->banco ?? "NÃO INFORMADO",
            $item->BancoConta?->agencia ?? "NÃO INFORMADO",
            $item->BancoConta?->conta ?? "NÃO INFORMADO",
            $item->Admissao?->status_carteira_treinamento ?? "NÃO INFORMADO",
            $item->Admissao?->status ?? "NÃO INFORMADO",
            $item->Admissao?->data_admissao ?? "NÃO INFORMADO",
            $item->Curriculo ? $item->Curriculo?->FotoTres ? $item->Curriculo?->FotoTres?->count() > 0 ? 'SIM' : 'NÃO' : 'NÃO' : 'NÃO',
            $item->Admissao?->QuemAdmitiu?->nome ?? "",
            $item->Admissao?->QuemAlterou?->nome ?? "",
        ];
    }

    public function getEscolaridade($row)
    {
        try {
            if ($row->Curriculo?->Escolaridade && $row->Curriculo?->Escolaridade?->id >= 8) {
                return $row->Curriculo?->Escolaridade?->tipo . ' - ' . $row->Curriculo?->formacao_curso ?? 'NÃO INFORMADO';
            }

            if ($row->Curriculo?->Escolaridade && $row->Curriculo?->Escolaridade?->id <= 7) {
                return $row->Curriculo?->Escolaridade?->tipo ?? 'NÃO INFORMADO';
            }

            return 'NÃO INFORMADO';
        } catch (\Exception $e) {
            \Log::debug($e->getMessage() . ' - ' . $row->Curriculo->nome);
            return 'NÃO INFORMADO';
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

            // Upload para S3 usando o disco de exportação
            $s3Path = Storage::disk(Arquivo::DISCO_EXPORTACAO)
                ->putFile('', new \Illuminate\Http\File($tempFile), 'private');

            if (!$s3Path) {
                throw new \Exception('Falha ao fazer upload para S3');
            }

            // Remove arquivo temporário do servidor
            unlink($tempFile);

            $local = "Relatório de Admissões";

            // Registra na tabela de exportações
            Exportacao::create([
                'user_id' => $this->userId,
                'arquivo' => basename($s3Path), // Apenas o nome do arquivo
                'local' => $local, // Caminho completo no S3
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

            \Log::info("Exportação concluída e salva no S3. Arquivo: {$s3Path}. Memória final: " . memory_get_usage(true) / 1024 / 1024 . " MB");

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
