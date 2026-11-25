<?php

namespace App\Services\Admissao;

use App\Jobs\Admissao\Importacao\ImportacaoAdmissaoJob;
use App\Models\Arquivo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Upload + enfileiramento da importação de admissões (TD-002 fatia 4).
 */
class AdmissaoImportUploadService
{
    /**
     * @return array{status: int, body: array<string, mixed>}
     */
    public function enfileirar(UploadedFile $file, int $empresaId, ?int $userId): array
    {
        $uuid = Str::uuid()->toString();
        $dir = 'importacao_admissoes/' . $empresaId;
        $path = $file->storeAs($dir, $uuid . '.xlsx', 'local');

        if ($path === false) {
            return [
                'status' => 500,
                'body' => ['msg' => 'Erro ao salvar o arquivo.', 'status' => 'error'],
            ];
        }

        $caminhoS3 = $dir . '/' . $uuid . '.xlsx';

        try {
            $conteudo = file_get_contents(storage_path('app/' . $path));
            Storage::disk(Arquivo::DISCO_EXPORTACAO)->put($caminhoS3, $conteudo);
        } catch (\Throwable $e) {
            Log::warning('Importação admissões: falha ao enviar planilha para S3 no upload', [
                'path' => $path,
                'caminho_s3' => $caminhoS3,
                'erro' => $e->getMessage(),
            ]);

            return [
                'status' => 500,
                'body' => ['msg' => 'Erro ao enviar arquivo para o storage.', 'status' => 'error'],
            ];
        }

        ImportacaoAdmissaoJob::dispatch(
            $path,
            $empresaId,
            $userId,
            100,
            $uuid,
            $caminhoS3
        );

        return [
            'status' => 202,
            'body' => [
                'msg' => 'Importação enfileirada. Você será notificado quando o processamento terminar.',
                'status' => 'success',
                'uuid' => $uuid,
            ],
        ];
    }
}
