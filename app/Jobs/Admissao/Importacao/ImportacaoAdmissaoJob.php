<?php

namespace App\Jobs\Admissao\Importacao;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Mail\Admissao\Importacao\ImportacaoConcluidaMail;
use App\Models\User;
use App\Services\Admissao\Importacao\LeitorPlanilhaAdmissao;
use App\Services\Admissao\Importacao\MapperLinhaPlanilhaParaPayload;
use App\Services\Admissao\Importacao\PersistidorAdmissaoImportada;
use App\Services\Admissao\Importacao\ResolvedorVagaAreaCentroCusto;
use App\Services\Admissao\Importacao\ValidadorLinhaPlanilhaAdmissao;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use RuntimeException;
use Str;

class ImportacaoAdmissaoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 3600;

    private const LOCK_KEY_PREFIX = 'importacao_admissao_lock_';

    public function __construct(
        public string $pathArquivo,
        public int $empresaId,
        public ?int $userId = null,
        public int $chunkSize = 30,
        public ?string $uuidImportacao = null,
        public ?string $s3Path = null
    ) {
        if ($this->uuidImportacao === null) {
            $this->uuidImportacao = Str::uuid()->toString();
        }
        $this->onQueue(config('queue.queues.importacao', 'default'));
    }

    public function handle(): void
    {
        $lockKey = self::LOCK_KEY_PREFIX . $this->empresaId;
        $lock = Cache::lock($lockKey, $this->timeout);
        if (!$lock->get()) {
            \Log::info('Importação de admissões: outra importação já está em execução para esta empresa (ECS/replicas). Job ignorado.', [
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

            $this->processarImportacao($path);
        } finally {
            $lock->release();
        }
    }

    private function resolvePathForProcessamento(): string
    {
        if ($this->s3Path !== null && $this->s3Path !== '') {
            $conteudo = Storage::disk('disco-exportacao')->get($this->s3Path);
            $dir = storage_path('app/importacao_admissoes/' . $this->empresaId);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $localPath = $dir . '/' . $this->uuidImportacao . '_from_s3.xlsx';
            file_put_contents($localPath, $conteudo);
            return $localPath;
        }
        return $this->resolvePath($this->pathArquivo);
    }

    private function processarImportacao(string $path): void
    {
        $leitor = new LeitorPlanilhaAdmissao();
        $validador = new ValidadorLinhaPlanilhaAdmissao();
        $resolvedor = new ResolvedorVagaAreaCentroCusto();
        $mapper = new MapperLinhaPlanilhaParaPayload();
        $persistidor = new PersistidorAdmissaoImportada();

        $relatorio = [];
        $totalProcessadas = 0;
        $totalSucesso = 0;
        $totalErros = 0;
        $numeroLinhaGlobal = 2;

        $iterator = $leitor->ler($path, $this->chunkSize);
        foreach ($iterator as $chunk) {
            foreach ($chunk as $linha) {
                if (trim((string) ($linha['cpf'] ?? '')) === '') {
                    $numeroLinhaGlobal++;
                    continue;
                }
                $errosLinha = $validador->validar($linha, $numeroLinhaGlobal, $this->empresaId);
                if (!empty($errosLinha)) {
                    foreach ($errosLinha as $campo => $dados) {
                        $relatorio[] = $this->linhaRelatorio(
                            $numeroLinhaGlobal,
                            $this->mascararCpfRelatorio($linha['cpf'] ?? ''),
                            'erro_validacao',
                            $campo,
                            $dados['mensagem'] ?? '',
                            $dados['como_corrigir'] ?? ''
                        );
                    }
                    $totalErros++;
                } else {
                    $rVaga = $resolvedor->resolverVaga($this->empresaId, $linha['cod_vaga'] ?? '');
                    $rArea = $resolvedor->resolverArea($this->empresaId, $linha['cod_area'] ?? '');
                    $rCc = $resolvedor->resolverCentroCusto($this->empresaId, $linha['centro_custo'] ?? '');
                    if ($rVaga['erro'] !== null) {
                        $relatorio[] = $this->linhaRelatorio($numeroLinhaGlobal, $this->mascararCpfRelatorio($linha['cpf'] ?? ''), 'erro_resolucao', 'cod_vaga', $rVaga['erro'], 'Use o código numérico ou cadastre a vaga.');
                        $totalErros++;
                    } elseif ($rCc['erro'] !== null) {
                        $relatorio[] = $this->linhaRelatorio($numeroLinhaGlobal, $this->mascararCpfRelatorio($linha['cpf'] ?? ''), 'erro_resolucao', 'centro_custo', $rCc['erro'], 'Use o código ou cadastre o centro de custo.');
                        $totalErros++;
                    } else {
                        $areaId = $rArea['id'];
                        if ($areaId === null && trim((string) ($linha['cod_area'] ?? '')) !== '') {
                            $relatorio[] = $this->linhaRelatorio($numeroLinhaGlobal, $this->mascararCpfRelatorio($linha['cpf'] ?? ''), 'erro_resolucao', 'cod_area', $rArea['erro'] ?? 'Área não encontrada.', 'Use o código ou nome cadastrado.');
                            $totalErros++;
                        } else {
                            $payload = $mapper->map($linha, (int) $rVaga['id'], $areaId, (int) $rCc['id']);
                            $resultado = $persistidor->persistir($payload, $this->empresaId, $this->userId);
                            if (!$resultado['sucesso']) {
                                $relatorio[] = $this->linhaRelatorio($numeroLinhaGlobal, $this->mascararCpfRelatorio($linha['cpf'] ?? ''), 'erro_persistencia', '', $resultado['erro'] ?? 'Erro ao persistir.', 'Verifique os dados e tente novamente.');
                                $totalErros++;
                            } else {
                                $totalSucesso++;
                            }
                        }
                    }
                }
                $totalProcessadas++;
                $numeroLinhaGlobal++;
            }
        }

        $relatorioPath = $totalErros > 0
            ? $this->salvarRelatorio($relatorio, $totalProcessadas, $totalSucesso, $totalErros)
            : null;
        $relatorioConteudo = $relatorioPath && is_readable($relatorioPath)
            ? file_get_contents($relatorioPath)
            : null;
        if ($relatorioPath !== null) {
            $this->enviarRelatorioParaS3($relatorioPath);
        }
        $this->notificarConclusao($relatorioConteudo, $totalProcessadas, $totalSucesso, $totalErros);
        if ($relatorioPath !== null && file_exists($relatorioPath) && is_file($relatorioPath)) {
            @unlink($relatorioPath);
        }
        $this->enviarPlanilhaParaS3($path);
        // Remove o arquivo usado no processamento (temp ou local) após envio ao S3
        $this->removerPlanilha($path);
        // Remove também o arquivo original do upload quando existir nesta instância (ex.: mesma réplica ECS)
        $this->removerPlanilha();
    }

    private function resolvePath(string $arquivo): string
    {
        if (str_starts_with($arquivo, '/') || preg_match('#^[A-Za-z]:\\\\#', $arquivo)) {
            return $arquivo;
        }
        return storage_path('app/' . ltrim($arquivo, '/'));
    }

    private function mascararCpfRelatorio($cpf): string
    {
        $digitos = preg_replace('/[^0-9]/', '', (string) $cpf);
        if (strlen($digitos) < 4) {
            return '***.***.***-**';
        }
        return '***.***.***-' . substr($digitos, -4);
    }

    /**
     * @param array<int, array{linha_planilha: int, cpf_planilha: string, status: string, campo: string, mensagem: string, como_corrigir: string}> $relatorio
     */
    private function salvarRelatorio(array $relatorio, int $totalProcessadas, int $totalSucesso, int $totalErros): ?string
    {
        $dir = storage_path('app/importacao_admissoes/relatorios/' . $this->empresaId);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $path = $dir . '/' . $this->uuidImportacao . '_resultado.csv';
        $fp = fopen($path, 'w');
        if ($fp === false) {
            return null;
        }
        fputcsv($fp, ['linha_planilha', 'cpf_planilha', 'status', 'campo', 'mensagem', 'como_corrigir'], ';');
        foreach ($relatorio as $linha) {
            fputcsv($fp, [
                $linha['linha_planilha'],
                $linha['cpf_planilha'],
                $linha['status'],
                $linha['campo'],
                $linha['mensagem'],
                $linha['como_corrigir'],
            ], ';');
        }
        fclose($fp);
        return $path;
    }

    private function notificarConclusao(?string $relatorioConteudoCsv, int $totalProcessadas, int $totalSucesso, int $totalErros): void
    {
        if ($this->userId === null) {
            return;
        }
        $user = User::withoutGlobalScopes()->find($this->userId);
        if ($user === null) {
            return;
        }
        $email = $user->login ?? $user->email ?? null;
        if ($email === null || $email === '') {
            \Log::warning('Importação de admissões: usuário sem e-mail para notificação', ['user_id' => $this->userId]);
            return;
        }
        \Log::info('Importação de admissões concluída', [
            'empresa_id' => $this->empresaId,
            'user_id' => $this->userId,
            'total_processadas' => $totalProcessadas,
            'sucesso' => $totalSucesso,
            'erros' => $totalErros,
        ]);
        $mailable = new ImportacaoConcluidaMail(
            (string) $user->nome,
            $email,
            $totalProcessadas,
            $totalSucesso,
            $totalErros,
            $relatorioConteudoCsv
        );
        Mail::to($email)->queue($mailable);

        Event::dispatch(new NotificacaoEvent([
            'user_id' => $this->userId,
            'total_processadas' => $totalProcessadas,
            'total_sucesso' => $totalSucesso,
            'total_erros' => $totalErros,
        ], NotificacaoEvent::IMPORTACAO_ADMISSOES_CONCLUIDA, NotificacaoEvent::TIPO_PADRAO));
    }

    /**
     * Envia o relatório CSV (erros da importação) para o S3 (disco-exportacao).
     * Caminho no S3: importacao_admissoes/relatorios/{empresa_id}/{uuid}_resultado.csv
     */
    private function enviarRelatorioParaS3(string $pathLocal): void
    {
        if (!is_readable($pathLocal) || !is_file($pathLocal)) {
            return;
        }
        $nomeArquivo = $this->uuidImportacao . '_resultado.csv';
        $caminhoS3 = 'importacao_admissoes/relatorios/' . $this->empresaId . '/' . $nomeArquivo;
        try {
            $conteudo = file_get_contents($pathLocal);
            Storage::disk('disco-exportacao')->put($caminhoS3, $conteudo);
            \Log::info('Importação de admissões: relatório enviado para S3', [
                'caminho_s3' => $caminhoS3,
                'empresa_id' => $this->empresaId,
                'uuid' => $this->uuidImportacao,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Importação de admissões: falha ao enviar relatório para S3', [
                'caminho_local' => $pathLocal,
                'caminho_s3' => $caminhoS3,
                'erro' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Envia a planilha importada para o S3 (disco-exportacao).
     * Caminho no S3: importacao_admissoes/{empresa_id}/{uuid}_{nome_arquivo}
     */
    private function enviarPlanilhaParaS3(string $pathLocal): void
    {
        if (!is_readable($pathLocal) || !is_file($pathLocal)) {
            return;
        }
        $nomeArquivo = basename($pathLocal);
        $ext = pathinfo($nomeArquivo, PATHINFO_EXTENSION);
        if (strtolower($ext) !== 'xlsx' && strtolower($ext) !== 'xls') {
            $nomeArquivo = $this->uuidImportacao . '.xlsx';
        } else {
            $nomeArquivo = $this->uuidImportacao . '_' . $nomeArquivo;
        }
        $caminhoS3 = 'importacao_admissoes/' . $this->empresaId . '/' . $nomeArquivo;
        try {
            $conteudo = file_get_contents($pathLocal);
            Storage::disk('disco-exportacao')->put($caminhoS3, $conteudo);
            \Log::info('Importação de admissões: planilha enviada para S3', [
                'caminho_s3' => $caminhoS3,
                'empresa_id' => $this->empresaId,
                'uuid' => $this->uuidImportacao,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Importação de admissões: falha ao enviar planilha para S3', [
                'caminho_local' => $pathLocal,
                'caminho_s3' => $caminhoS3,
                'erro' => $e->getMessage(),
            ]);
        }
    }

    private function removerPlanilha(?string $pathUsado = null): void
    {
        $path = $pathUsado ?? $this->resolvePath($this->pathArquivo);
        if (file_exists($path) && is_file($path)) {
            @unlink($path);
        }
    }

    private function linhaRelatorio(int $linhaPlanilha, string $cpfPlanilha, string $status, string $campo, string $mensagem, string $comoCorrigir): array
    {
        return [
            'linha_planilha' => $linhaPlanilha,
            'cpf_planilha' => $cpfPlanilha,
            'status' => $status,
            'campo' => $campo,
            'mensagem' => $mensagem,
            'como_corrigir' => $comoCorrigir,
        ];
    }
}
