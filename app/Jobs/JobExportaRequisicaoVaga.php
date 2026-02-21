<?php

namespace App\Jobs;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Models\AprovacaoExtraConfig;
use App\Models\Exportacao;
use App\Models\User;
use App\Services\RequisicaoVaga\RequisicaoVagaExportFormatter;
use App\Services\RequisicaoVaga\RequisicaoVagaExportQueryBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

/**
 * Job de exportação Requisição de Vaga. Mesmo padrão do JobExportaCihCsvFinal:
 * lock, auth()->login($user) no worker, chunks, upload S3, notificação, Exportacao::create.
 */
class JobExportaRequisicaoVaga implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600;

    protected $usuario;
    protected $local;
    protected $nomeArquivo;
    protected $filtros;
    protected $lockKey;
    protected $lockTimeout = 1200;

    const CHUNK_SIZE = 1000;

    public function __construct($usuario, $local, $nomeArquivo, $filtros)
    {
        $this->usuario = $usuario;
        $this->local = $local;
        $this->nomeArquivo = $nomeArquivo;
        $this->filtros = $filtros;
        $this->lockKey = 'requisicao_vaga_export_lock_' . md5($nomeArquivo . '_' . $usuario . '_' . json_encode($filtros));
    }

    public function handle(): void
    {
        if (!$this->acquireLock()) {
            \Log::info("Job Requisição de Vaga já está sendo processado. Lock key: {$this->lockKey}");
            return;
        }

        try {
            \Log::info('Iniciando exportação Requisição de Vaga CSV');
            \Log::info('Filtros: ' . json_encode($this->filtros));
            $user = User::find($this->usuario);
            if (!$user) {
                throw new \Exception("Usuário não encontrado: {$this->usuario}");
            }
            auth()->login($user);
            \Log::info('Autenticado para export (mesmo padrão CIH)');

            $nomeAprovacaoExtra = null;
            $config = AprovacaoExtraConfig::getConfigAtiva($user->empresa_id, AprovacaoExtraConfig::TIPO_REQUISICAO_VAGA);
            if ($config && !empty($config->nome_aprovacao)) {
                $nomeAprovacaoExtra = $config->nome_aprovacao;
            }
            $formatter = new RequisicaoVagaExportFormatter($nomeAprovacaoExtra, $user->empresa_id);
            $headers = $formatter->getHeaders();

            $localFilePath = $this->createLocalCsvFile($user, $headers, $formatter);
            $fileContent = file_get_contents($localFilePath);
            Storage::disk('disco-exportacao')->put($this->nomeArquivo, $fileContent);
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

            \Log::info('Exportação Requisição de Vaga CSV finalizada com sucesso');
        } catch (\Exception $e) {
            \Log::error('Erro na exportação Requisição de Vaga CSV: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        } finally {
            $this->releaseLock();
        }
    }

    private function createLocalCsvFile(User $user, array $headers, RequisicaoVagaExportFormatter $formatter): string
    {
        $localFilePath = tempnam(sys_get_temp_dir(), 'requisicao_vaga_export_') . '.csv';
        $file = fopen($localFilePath, 'w');
        if (!$file) {
            throw new \Exception("Não foi possível criar arquivo temporário: {$localFilePath}");
        }
        fwrite($file, "\xEF\xBB\xBF");
        fputcsv($file, $headers, ';', '"');

        $query = RequisicaoVagaExportQueryBuilder::forExport($user, $this->filtros);
        $totalRecords = $query->count();
        \Log::info("Total de registros para exportar: {$totalRecords}");

        $processedRecords = 0;
        $rowsWritten = 0;
        $query->chunk(self::CHUNK_SIZE, function ($movimentacoes) use ($file, $formatter, &$processedRecords, &$rowsWritten) {
            foreach ($movimentacoes as $row) {
                $rowData = $formatter->formatRow($row);
                fputcsv($file, $rowData, ';', '"');
                $rowsWritten++;
            }
            $processedRecords += $movimentacoes->count();
            \Log::info("Requisição de Vaga Export - Processados {$processedRecords} registros, linhas escritas: {$rowsWritten}");
        });

        fclose($file);
        \Log::info("Total de linhas escritas no CSV: {$rowsWritten}");
        return $localFilePath;
    }

    private function acquireLock(): bool
    {
        $lockValue = gethostname() . '_' . getmypid() . '_' . time();
        try {
            return Cache::store('redis')->add($this->lockKey, $lockValue, $this->lockTimeout);
        } catch (\Exception $e) {
            \Log::error("Erro ao adquirir lock: {$e->getMessage()}");
            return true;
        }
    }

    private function releaseLock(): void
    {
        try {
            Cache::store('redis')->forget($this->lockKey);
        } catch (\Exception $e) {
            \Log::error("Erro ao liberar lock: {$e->getMessage()}");
        }
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error("Job Requisição de Vaga falhou: {$exception->getMessage()}");
        $this->releaseLock();
    }
}
