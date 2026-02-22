<?php

namespace App\Jobs;

use App\Events\Notificacoes\NotificacaoEvent;
use App\Models\Exportacao;
use App\Models\User;
use App\Services\AvaliacaoNoventaService;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

/**
 * Exportação Excel do relatório Avaliação de Experiência (padrão CIH).
 * Gera o arquivo, grava em disco-exportacao, cria registro Exportacao e notifica o usuário.
 */
class JobExportaAvaliacaoExperienciaExcel implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600;

    protected int $userId;

    /** @var array Filtros da tela (status, nome, centroCusto, gestor, avaliacoes, cargo, funcao). Vazio = total. */
    protected array $filtros;

    protected string $local = 'Avaliação de Experiência';

    public function __construct(int $userId, array $filtros = [])
    {
        $this->userId = $userId;
        $this->filtros = $filtros;
    }

    public function handle(AvaliacaoNoventaService $service): void
    {
        $user = User::find($this->userId);
        if (!$user) {
            Log::warning('JobExportaAvaliacaoExperienciaExcel: usuário não encontrado', ['user_id' => $this->userId]);
            return;
        }

        // Contexto de autenticação para Scopes (ex.: ScopeEmpresa) que usam auth()->user()->empresa_id
        Auth::loginUsingId($this->userId);

        $empresaId = $user->empresa_id;
        $vencimentos = $service->getVencimentosOrdenadosParaExportacao($empresaId, $this->filtros);

        if (empty($vencimentos)) {
            Log::info('JobExportaAvaliacaoExperienciaExcel: nenhum vencimento para exportar', ['user_id' => $this->userId]);
            return;
        }

        try {
            Log::info('JobExportaAvaliacaoExperienciaExcel: iniciando geração do Excel', [
                'user_id' => $this->userId,
                'total' => count($vencimentos),
            ]);

            $resultado = $service->gerarExcelS3($vencimentos, $empresaId);
            if (!$resultado || empty($resultado['nome_arquivo'])) {
                throw new \Exception('Falha ao gerar arquivo Excel');
            }

            Exportacao::create([
                'user_id' => $this->userId,
                'arquivo' => $resultado['nome_arquivo'],
                'local' => $this->local,
                'removido' => false,
            ]);

            Event::dispatch(new NotificacaoEvent([
                'user_id' => $this->userId,
                'local' => $this->local,
            ], NotificacaoEvent::EXPORTACAO_EXCEL, NotificacaoEvent::TIPO_PADRAO));

            Log::info('JobExportaAvaliacaoExperienciaExcel: exportação concluída', [
                'user_id' => $this->userId,
                'arquivo' => $resultado['nome_arquivo'],
            ]);
        } catch (\Throwable $e) {
            Log::error('JobExportaAvaliacaoExperienciaExcel: erro', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
