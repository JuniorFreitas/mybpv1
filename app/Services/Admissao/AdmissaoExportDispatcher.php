<?php

namespace App\Services\Admissao;

use App\Jobs\JobExportaAdmissoes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Orquestra o disparo da exportação de admissões (fila + cache anti-duplicidade).
 * Extraído de AdmissaoController::export (TD-002 fatia 1).
 */
class AdmissaoExportDispatcher
{
    /**
     * @param  array<string, mixed>  $requestData
     * @return array{status: int, body: array<string, mixed>}
     */
    public function dispatch(int $userId, array $requestData): array
    {
        $cacheKey = 'export_admissoes_' . $userId . '_' . md5(json_encode($requestData));

        $cacheData = Cache::get($cacheKey);
        if ($cacheData) {
            $status = $cacheData['status'] ?? 'processing';
            $attempts = $cacheData['attempt'] ?? 1;
            $maxTries = $cacheData['max_tries'] ?? 3;

            $message = match ($status) {
                'processing' => "Exportação em andamento (tentativa {$attempts}/{$maxTries}). Aguarde a conclusão.",
                'retrying' => "Exportação tentando novamente (tentativa {$attempts}/{$maxTries}). Aguarde.",
                'completed' => 'Exportação já foi concluído. Verifique suas notificações.',
                'failed' => "Última Exportação falhou após {$maxTries} tentativas. Você pode tentar novamente.",
                default => 'Já existe uma Exportação em andamento. Aguarde a conclusão.',
            };

            return [
                'status' => 200,
                'body' => [
                    'msg' => $message,
                    'status' => $status,
                    'initiated_at' => $cacheData['initiated_at'] ?? null,
                    'attempts' => $attempts,
                    'max_tries' => $maxTries,
                    'last_error' => $cacheData['last_error'] ?? null,
                ],
            ];
        }

        try {
            // CSV streaming (PERF-MEM / padrão CIH) — evita PhpSpreadsheet no container 700MB
            $nameArquivo = 'admissoes-exportado' . rand(1000, 9999) . '_' . date('YmdHis') . '.csv';
            $expiresAt = now()->addMinutes(15);

            Cache::put($cacheKey, [
                'filename' => $nameArquivo,
                'initiated_at' => now(),
                'expires_at' => $expiresAt,
                'user_id' => $userId,
                'status' => 'queued',
                'attempt' => 0,
                'max_tries' => 3,
                'progress' => 0,
            ], $expiresAt);

            JobExportaAdmissoes::dispatch(
                $userId,
                $requestData,
                $nameArquivo,
                $cacheKey
            );

            return [
                'status' => 200,
                'body' => [
                    'msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.',
                    'export_id' => $cacheKey,
                    'estimated_time' => '5-15 minutos',
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('Erro no dispatcher de export admissões: ' . $e->getMessage() . ' ' . $e->getFile() . ' on line ' . $e->getLine());
            Cache::forget($cacheKey);

            return [
                'status' => 500,
                'body' => ['error' => 'Erro interno'],
            ];
        }
    }
}
