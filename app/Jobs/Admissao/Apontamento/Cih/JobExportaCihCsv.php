<?php

namespace App\Jobs\Admissao\Apontamento\Cih;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Models\Exportacao;
use App\Models\User;
use App\Services\Cih\CihQueryBuilder;
use App\Services\Cih\CihExportFormatter;
use App\Services\Cih\CsvFileManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

class JobExportaCihCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600;

    public function __construct(
        private int        $userId,
        private string     $local,
        private string     $nomeArquivo,
        private array      $filtros,
        private string|int $modeloCihConfig  // Aceita string ou int
    )
    {
    }

    public function handle(): void
    {
        try {
            \Log::info('Iniciando exportação CIH CSV final');
            \Log::info('Filtros: ' . json_encode($this->filtros));
            \Log::info('Modelo config: ' . $this->modeloCihConfig);

            $user = $this->authenticateUser();
            $formatter = new CihExportFormatter($this->modeloCihConfig);
            $fileManager = new CsvFileManager();

            // Criar arquivo e escrever dados
            $headers = $formatter->getHeaders();
            \Log::info('Headers: ' . json_encode($headers));

            $tempFilePath = $fileManager->createTempFile($headers);

            $query = CihQueryBuilder::forExport($user, $this->filtros);
            \Log::info('Query SQL: ' . $query->toSql());
            \Log::info('Query bindings: ' . json_encode($query->getBindings()));

            $rowsWritten = $fileManager->writeDataInChunks($query, $formatter);

            $fileManager->closeFile();

            // Upload e limpeza
            $fileManager->uploadToS3($this->nomeArquivo);
            $fileManager->cleanup();

            // Notificações e registros
            $this->sendNotification();
            $this->createExportRecord();

            \Log::info("Exportação concluída. Linhas escritas: {$rowsWritten}");

        } catch (\Exception $e) {
            \Log::error('Erro na exportação CIH CSV: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    private function authenticateUser(): User
    {
        $user = User::find($this->userId);

        if (!$user) {
            throw new \Exception("Usuário não encontrado: {$this->userId}");
        }

        auth()->login($user);
        return $user;
    }

    private function sendNotification(): void
    {
        Event::dispatch(new NotificacaoEvent([
            'user_id' => $this->userId,
            'local' => $this->local,
        ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));
    }

    private function createExportRecord(): void
    {
        Exportacao::create([
            'user_id' => $this->userId,
            'arquivo' => $this->nomeArquivo,
            'local' => $this->local,
            'removido' => false,
        ]);
    }
}
