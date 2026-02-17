<?php

namespace App\Jobs\Movimentacao\TransferenciaPrevista;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Models\Exportacao;
use App\Models\User;
use App\Services\TransferenciaPrevista\TransferenciaPrevistaCsvFileManager;
use App\Services\TransferenciaPrevista\TransferenciaPrevistaExportFormatter;
use App\Services\TransferenciaPrevista\TransferenciaPrevistaExportQueryBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

class JobTransferenciaPrevistaExportaExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600;

    protected int $userId;
    protected string $local;
    protected string $nomeArquivo;
    protected array $filtros;

    public function __construct(int $userId, string $local, string $nomeArquivo, array $filtros = [])
    {
        $this->userId = $userId;
        $this->local = $local;
        $this->nomeArquivo = $nomeArquivo;
        $this->filtros = $filtros;
    }

    public function handle(): void
    {
        try {
            $user = $this->authenticateUser();
            $formatter = new TransferenciaPrevistaExportFormatter();
            $fileManager = new TransferenciaPrevistaCsvFileManager();
            $headers = $formatter->getHeaders();
            $fileManager->createTempFile($headers);
            $query = TransferenciaPrevistaExportQueryBuilder::forExport($user, $this->filtros);
            $fileManager->writeDataInChunks($query, $formatter);
            $fileManager->closeFile();
            $fileManager->uploadToS3($this->nomeArquivo);
            $fileManager->cleanup();
            $this->sendNotification();
            $this->createExportRecord();
        } catch (\Exception $e) {
            \Log::error('Erro na exportação Transferência Prevista CSV: ' . $e->getMessage());
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
