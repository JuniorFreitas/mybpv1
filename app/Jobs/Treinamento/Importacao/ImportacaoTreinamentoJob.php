<?php

namespace App\Jobs\Treinamento\Importacao;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Models\Arquivo;
use App\Models\Exportacao;
use App\Models\Treinamento\ImportacaoTreinamento;
use App\Models\User;
use App\Services\Treinamento\Importacao\DivisorRelatorioImportacaoTreinamento;
use App\Services\Treinamento\Importacao\LeitorPlanilhaImportacaoTreinamento;
use Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Str;

class ImportacaoTreinamentoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 3600;

    private const LOCK_KEY_PREFIX = 'importacao_treinamento_lock_';

    private const LOCAL_DOWNLOADS = 'Importação de Treinamentos';

    public function __construct(
        public string $pathArquivo,
        public int $empresaId,
        public ?int $userId = null,
        public ?string $uuidImportacao = null,
        public ?string $s3Path = null
    ) {
        if ($this->uuidImportacao === null) {
            $this->uuidImportacao = Str::uuid()->toString();
        }
        $this->onQueue(config('queue.queues.importacao', 'default'));
    }

    public function handle(
        LeitorPlanilhaImportacaoTreinamento $leitor,
        DivisorRelatorioImportacaoTreinamento $divisor
    ): void {
        $lockKey = self::LOCK_KEY_PREFIX . $this->empresaId;
        $lock = Cache::lock($lockKey, $this->timeout);
        if (!$lock->get()) {
            \Log::info('Importação de treinamentos: outra importação já está em execução para esta empresa. Job ignorado.', [
                'empresa_id' => $this->empresaId,
                'uuid' => $this->uuidImportacao,
            ]);
            return;
        }

        try {
            $path = $this->resolvePathForProcessamento();
            if (!is_readable($path)) {
                throw new RuntimeException("Arquivo não encontrado ou não legível: {$this->pathArquivo}");
            }

            $registros = $leitor->ler($path, $this->empresaId);
            if (count($registros) === 0) {
                throw new RuntimeException('Nenhuma linha válida encontrada na planilha.');
            }

            $jsonPath = $this->gravarJsonTemporario($registros);
            $relatorioPath = $this->caminhoRelatorioLocal();
            $this->executarComandoImportacao($jsonPath, $relatorioPath);
            $this->publicarRelatorioENotificar($relatorioPath, $divisor);
        } catch (\Throwable $e) {
            $this->marcarFalha($e->getMessage());
            throw $e;
        } finally {
            $lock->release();
        }
    }

    private function resolvePathForProcessamento(): string
    {
        if ($this->s3Path !== null && $this->s3Path !== '') {
            $conteudo = Storage::disk('disco-exportacao')->get($this->s3Path);
            $dir = storage_path('app/importacao_treinamentos/' . $this->empresaId);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $localPath = $dir . '/' . $this->uuidImportacao . '_from_s3.xlsx';
            file_put_contents($localPath, $conteudo);

            return $localPath;
        }

        return $this->resolvePath($this->pathArquivo);
    }

    private function resolvePath(string $path): string
    {
        if (str_starts_with($path, '/') || preg_match('#^[A-Za-z]:\\\\#', $path)) {
            return $path;
        }

        return storage_path('app/' . ltrim($path, '/'));
    }

    /**
     * @param list<array<string, string>> $registros
     */
    private function gravarJsonTemporario(array $registros): string
    {
        $dir = storage_path('app/importacao_treinamentos/' . $this->empresaId);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $jsonPath = $dir . '/' . $this->uuidImportacao . '.json';
        $json = json_encode($registros, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException('Falha ao serializar JSON da importação de treinamentos.');
        }

        if (file_put_contents($jsonPath, $json) === false) {
            throw new RuntimeException('Falha ao gravar JSON temporário da importação.');
        }

        return $jsonPath;
    }

    private function caminhoRelatorioLocal(): string
    {
        $dir = storage_path('app/importacao_treinamentos/relatorios/' . $this->empresaId);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir . '/' . $this->uuidImportacao . '_resultado.csv';
    }

    private function executarComandoImportacao(string $jsonPath, string $relatorioPath): void
    {
        $params = [
            '--empresa_id' => $this->empresaId,
            '--path' => $jsonPath,
            '--gerar-relatorio' => true,
            '--relatorio-path' => $relatorioPath,
        ];

        if ($this->userId !== null && $this->userId > 0) {
            $params['--user_id'] = $this->userId;
        }

        $email = $this->resolverEmailDestino();
        if ($email !== null) {
            $params['--enviar-email'] = $email;
        }

        $exitCode = Artisan::call('mybp:importa-treinamento', $params);
        if ($exitCode !== 0) {
            \Log::warning('Importação de treinamentos finalizou com código diferente de zero', [
                'empresa_id' => $this->empresaId,
                'uuid' => $this->uuidImportacao,
                'exit_code' => $exitCode,
                'output' => Artisan::output(),
            ]);
        }
    }

    private function publicarRelatorioENotificar(
        string $relatorioPath,
        DivisorRelatorioImportacaoTreinamento $divisor
    ): void {
        if (!is_readable($relatorioPath) || !is_file($relatorioPath)) {
            \Log::warning('Importação de treinamentos: relatório CSV não encontrado após o comando', [
                'empresa_id' => $this->empresaId,
                'uuid' => $this->uuidImportacao,
                'path' => $relatorioPath,
            ]);
            $this->marcarFalha('Relatório CSV não encontrado após o processamento.');

            return;
        }

        $dirSplit = storage_path('app/importacao_treinamentos/relatorios/' . $this->empresaId);
        $dividido = $divisor->dividir($relatorioPath, $dirSplit, (string) $this->uuidImportacao);

        $arquivoSucesso = $this->enviarCsvHistorico($dividido['sucesso'] ?? null, 'sucesso');
        $arquivoErro = $this->enviarCsvHistorico($dividido['erro'] ?? null, 'erro');

        $nomeArquivoDownloads = $this->enviarRelatorioParaDownloads($relatorioPath);
        if ($this->userId !== null && $nomeArquivoDownloads !== null) {
            Exportacao::create([
                'user_id' => $this->userId,
                'arquivo' => $nomeArquivoDownloads,
                'local' => self::LOCAL_DOWNLOADS,
                'removido' => false,
            ]);
        }

        ImportacaoTreinamento::query()
            ->where('uuid', $this->uuidImportacao)
            ->update([
                'status' => ImportacaoTreinamento::STATUS_CONCLUIDA,
                'total' => $dividido['total'],
                'sucesso' => $dividido['sucesso_count'],
                'erros' => $dividido['erros_count'],
                'arquivo_sucesso' => $arquivoSucesso,
                'arquivo_erro' => $arquivoErro,
                'mensagem_erro' => null,
            ]);

        $this->notificarConclusao(
            $dividido['total'],
            $dividido['sucesso_count'],
            $dividido['erros_count']
        );

        $this->limparArquivoLocal($relatorioPath);
        $this->limparArquivoLocal($dividido['sucesso'] ?? null);
        $this->limparArquivoLocal($dividido['erro'] ?? null);
    }

    private function enviarCsvHistorico(?string $pathLocal, string $tipo): ?string
    {
        if ($pathLocal === null || !is_readable($pathLocal)) {
            return null;
        }

        $caminhoS3 = sprintf(
            'importacao_treinamentos/relatorios/%d/%s_%s.csv',
            $this->empresaId,
            $this->uuidImportacao,
            $tipo
        );

        try {
            $conteudo = file_get_contents($pathLocal);
            if ($conteudo === false) {
                return null;
            }
            Storage::disk(Arquivo::DISCO_EXPORTACAO)->put($caminhoS3, $conteudo);

            return $caminhoS3;
        } catch (\Throwable $e) {
            \Log::warning('Importação de treinamentos: falha ao enviar CSV de histórico', [
                'tipo' => $tipo,
                'path' => $pathLocal,
                'erro' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function enviarRelatorioParaDownloads(string $pathLocal): ?string
    {
        $userId = $this->userId ?? 0;
        $nomeArquivo = sprintf(
            'relatorio_importacao_treinamento_%d_%d_%s.csv',
            $userId,
            $this->empresaId,
            date('YmdHis')
        );

        try {
            $conteudo = file_get_contents($pathLocal);
            if ($conteudo === false) {
                return null;
            }
            Storage::disk(Arquivo::DISCO_EXPORTACAO)->put($nomeArquivo, $conteudo);

            return $nomeArquivo;
        } catch (\Throwable $e) {
            \Log::warning('Importação de treinamentos: falha ao enviar relatório para downloads', [
                'path' => $pathLocal,
                'erro' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function marcarFalha(string $mensagem): void
    {
        ImportacaoTreinamento::query()
            ->where('uuid', $this->uuidImportacao)
            ->where('status', ImportacaoTreinamento::STATUS_PROCESSANDO)
            ->update([
                'status' => ImportacaoTreinamento::STATUS_FALHA,
                'mensagem_erro' => mb_substr($mensagem, 0, 500),
            ]);
    }

    private function limparArquivoLocal(?string $path): void
    {
        if ($path !== null && file_exists($path) && is_file($path)) {
            @unlink($path);
        }
    }

    private function notificarConclusao(int $totalProcessadas, int $totalSucesso, int $totalErros): void
    {
        if ($this->userId === null) {
            return;
        }

        Event::dispatch(new NotificacaoEvent([
            'user_id' => $this->userId,
            'total_processadas' => $totalProcessadas,
            'total_sucesso' => $totalSucesso,
            'total_erros' => $totalErros,
        ], NotificacaoEvent::IMPORTACAO_TREINAMENTOS_CONCLUIDA, NotificacaoEvent::TIPO_PADRAO));
    }

    private function resolverEmailDestino(): ?string
    {
        if ($this->userId === null) {
            return null;
        }

        $user = User::query()->select(['id', 'login'])->find($this->userId);
        if (!$user) {
            return null;
        }

        $email = $user->login ?? null;

        return is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }
}
