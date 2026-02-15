<?php

namespace App\Jobs\Movimentacao\DemissaoPrevista;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Models\AprovacaoExtraConfig;
use App\Models\Exportacao;
use App\Models\User;
use App\Services\DemissaoPrevista\DemissaoPrevistaCsvFileManager;
use App\Services\DemissaoPrevista\DemissaoPrevistaExportFormatter;
use App\Services\DemissaoPrevista\DemissaoPrevistaExportQueryBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;

class JobDemissaoPrevistaExportaExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600;

    /** @var int */
    protected $userId;
    /** @var string */
    protected $local;
    /** @var string */
    protected $nomeArquivo;
    /** @var array */
    protected $filtros;

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
            \Log::info('Iniciando exportação Demissão Prevista CSV');

            $user = $this->authenticateUser();
            $configExtra = AprovacaoExtraConfig::getConfigAtiva($user->empresa_id, 'demissao');
            $nomeAprovacaoExtra = $configExtra ? $configExtra->nome_aprovacao : null;
            $formatter = new DemissaoPrevistaExportFormatter($nomeAprovacaoExtra);
            $fileManager = new DemissaoPrevistaCsvFileManager();

            $headers = $formatter->getHeaders();
            $fileManager->createTempFile($headers);

            $query = DemissaoPrevistaExportQueryBuilder::forExport($user, $this->filtros);
            $fileManager->writeDataInChunks($query, $formatter);

            $fileManager->closeFile();
            $fileManager->uploadToS3($this->nomeArquivo);
            $fileManager->cleanup();

            $this->sendNotification();
            $this->createExportRecord();

            \Log::info('Exportação Demissão Prevista concluída.');
        } catch (\Exception $e) {
            \Log::error('Erro na exportação Demissão Prevista CSV: ' . $e->getMessage());
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
