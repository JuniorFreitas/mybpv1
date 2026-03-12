<?php

namespace App\Jobs\Admissao\Importacao;

use App\Mail\Admissao\Importacao\ImportacaoConcluidaMail;
use App\Models\User;
use App\Services\Admissao\Importacao\LeitorPlanilhaAdmissao;
use App\Services\Admissao\Importacao\MapperLinhaPlanilhaParaPayload;
use App\Services\Admissao\Importacao\PersistidorAdmissaoImportada;
use App\Services\Admissao\Importacao\ResolvedorVagaAreaCentroCusto;
use App\Services\Admissao\Importacao\ValidadorLinhaPlanilhaAdmissao;
use Illuminate\Support\Facades\Mail;
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

    public function __construct(
        public string $pathArquivo,
        public int $empresaId,
        public ?int $userId = null,
        public int $chunkSize = 100,
        public ?string $uuidImportacao = null
    ) {
        if ($this->uuidImportacao === null) {
            $this->uuidImportacao = Str::uuid()->toString();
        }
        $this->onQueue(config('queue.queues.importacao', 'default'));
    }

    public function handle(): void
    {
        $path = $this->resolvePath($this->pathArquivo);
        if (!is_readable($path)) {
            throw new RuntimeException("Arquivo não encontrado ou não legível: {$this->pathArquivo}");
        }

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

        $relatorioPath = $this->salvarRelatorio($relatorio, $totalProcessadas, $totalSucesso, $totalErros);
        $this->notificarConclusao($relatorioPath, $totalProcessadas, $totalSucesso, $totalErros);
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

    private function notificarConclusao(?string $relatorioPath, int $totalProcessadas, int $totalSucesso, int $totalErros): void
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
            'relatorio' => $relatorioPath,
        ]);
        $mailable = new ImportacaoConcluidaMail(
            (string) $user->nome,
            $email,
            $totalProcessadas,
            $totalSucesso,
            $totalErros,
            $relatorioPath
        );
        Mail::to($email)->queue($mailable);
    }

    private function removerPlanilha(): void
    {
        $path = $this->resolvePath($this->pathArquivo);
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
