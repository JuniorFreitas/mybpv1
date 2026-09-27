<?php

namespace App\Jobs\Admissao\Processo;

use App\Jobs\JobMailVencimentoAso;
use App\Models\ClienteConfig;
use App\Models\TipoRecebeEmail;
use App\Models\User;
use App\Services\Relatorios\AsoVencimentoRelatorioService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use MasterTag\DataHora;

class VencimentoAsoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct()
    {
    }

    public function __invoke()
    {
        $this->handle();
    }

    public function handle(): void
    {
        $service = app(AsoVencimentoRelatorioService::class);
        $configs = ClienteConfig::withoutGlobalScopes()
            ->whereNotNull('vencimento_aso')
            ->get();

        foreach ($configs as $clienteConfig) {
            $empresaId = (int) $clienteConfig->cliente_id;
            if (!$empresaId || !isset(ClienteConfig::LISTA_VENCIMENTOS[$clienteConfig->vencimento_aso])) {
                continue;
            }

            try {
                $this->processarEmpresa($service, $empresaId);
            } catch (\Throwable $e) {
                Log::error('Erro ao processar Vencimento ASO', [
                    'empresa_id' => $empresaId,
                    'erro' => $e->getMessage(),
                ]);
            } finally {
                Auth::logout();
            }
        }
    }

    private function processarEmpresa(AsoVencimentoRelatorioService $service, int $empresaId): void
    {
        $usuarioTemp = User::usuarioContextoEmpresa($empresaId);

        if (!$usuarioTemp) {
            Log::info("Vencimento ASO: sem usuário para login temporário - empresa {$empresaId}");
            return;
        }

        Auth::login($usuarioTemp);

        $usuarios = User::withoutGlobalScopes()
            ->paraNotificacaoEmail(TipoRecebeEmail::VENCIMENTO_ASO, $empresaId)
            ->select(['users.id', 'users.nome', 'users.login', 'users.empresa_id'])
            ->get();

        if ($usuarios->isEmpty()) {
            return;
        }

        $itens = $service->listarParaAlertaEmail($usuarioTemp);
        if ($itens->isEmpty()) {
            return;
        }

        $vencimentos = $itens->map(function (array $row) {
            return [
                'colaborador' => $row['colaborador'],
                'data_aso' => $this->formatarData($row['data_aso'] ?? null),
                'data_vencimento' => $this->formatarData($row['data_vencimento'] ?? null),
                'dias_vencer' => $row['dias_vencer'],
                'status' => $row['status'] ?? (($row['dias_vencer'] ?? 0) < 0 ? 'VENCIDO' : 'A VENCER'),
            ];
        })->all();

        foreach ($usuarios as $usuario) {
            JobMailVencimentoAso::dispatch([
                'usuario' => $usuario,
                'vencimentos' => $vencimentos,
                'empresa_id' => $empresaId,
            ])->delay(now()->addSeconds(5));

            Log::info("E-mail de Vencimento ASO enfileirado - {$usuario->nome} - {$empresaId}");
        }
    }

    private function formatarData($data): string
    {
        if (!$data) {
            return '—';
        }
        if (is_string($data) && str_contains($data, '/')) {
            return $data;
        }

        try {
            return (new DataHora($data))->dataCompleta();
        } catch (\Throwable $e) {
            return (string) $data;
        }
    }
}
