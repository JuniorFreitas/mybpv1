<?php

namespace App\Services\AssinaturaDigital;

use App\Jobs\AssinaturaDigital\JobEnviarAlertaCotaAssinatura;
use App\Models\AssinaturaCotaAlertaEnvio;
use App\Models\ClienteConfig;
use App\Models\DocumentoParaAssinatura;
use App\Models\Papel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AssinaturaCotaService
{
    public function obterResumoMensal(int $empresaId, ?string $referencia = null): array
    {
        $agora = Carbon::now();
        $competencia = $agora;

        if (!empty($referencia) && preg_match('/^\d{4}-\d{2}$/', $referencia)) {
            try {
                $competencia = Carbon::createFromFormat('Y-m', $referencia)->startOfMonth();
            } catch (\Throwable $e) {
                $competencia = $agora;
            }
        }

        $inicioMes = $competencia->copy()->startOfMonth();
        $fimMes = $competencia->copy()->endOfMonth();
        $config = ClienteConfig::whereClienteId($empresaId)->first();
        $limite = null;
        if ($config && $this->temColunaClienteConfig('limite_assinaturas_mensal')) {
            $limite = $config->limite_assinaturas_mensal;
        }

        $baseQuery = DocumentoParaAssinatura::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->whereBetween('created_at', [$inicioMes, $fimMes]);

        $usadas = (int) (clone $baseQuery)->count();
        $restantes = $limite === null ? null : max(((int) $limite) - $usadas, 0);
        $percentualUso = ($limite === null || (int) $limite <= 0) ? null : round(($usadas / (int) $limite) * 100, 2);

        $extratoPorTipo = (clone $baseQuery)
            ->select('tipo_documento', DB::raw('COUNT(*) as total'))
            ->groupBy('tipo_documento')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($linha) {
                return [
                    'tipo_documento' => $linha->tipo_documento,
                    'label' => DocumentoParaAssinatura::labelTipoDocumento((string) $linha->tipo_documento),
                    'total' => (int) $linha->total,
                ];
            })
            ->values()
            ->toArray();

        $extratoDiario = (clone $baseQuery)
            ->select(DB::raw('DATE(created_at) as dia'), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('dia', 'asc')
            ->get()
            ->map(function ($linha) {
                return [
                    'dia' => $linha->dia,
                    'total' => (int) $linha->total,
                ];
            })
            ->values()
            ->toArray();

        return [
            'competencia' => $inicioMes->format('Y-m'),
            'periodo_inicio' => $inicioMes->format('Y-m-d 00:00:00'),
            'periodo_fim' => $fimMes->format('Y-m-d 23:59:59'),
            'limite_mensal' => $limite === null ? null : (int) $limite,
            'usadas' => $usadas,
            'restantes' => $restantes,
            'percentual_uso' => $percentualUso,
            'extrato_por_tipo' => $extratoPorTipo,
            'extrato_diario' => $extratoDiario,
        ];
    }

    public function validarDisponibilidadeOrFail(int $empresaId): void
    {
        $this->validarFuncionalidadeHabilitadaOrFail($empresaId);

        $resumo = $this->obterResumoMensal($empresaId);
        if ($resumo['limite_mensal'] === null) {
            return;
        }
        if ((int) $resumo['usadas'] >= (int) $resumo['limite_mensal']) {
            throw new \RuntimeException('Cota mensal de assinatura digital atingida para esta empresa.');
        }
    }

    public function validarFuncionalidadeHabilitadaOrFail(int $empresaId): void
    {
        if (!$this->temColunaClienteConfig('assinatura_digital_habilitada')) {
            throw new \RuntimeException('Assinatura digital não está habilitada para esta empresa.');
        }

        $habilitada = (bool) ClienteConfig::whereClienteId($empresaId)
            ->value('assinatura_digital_habilitada');

        if (!$habilitada) {
            throw new \RuntimeException('Assinatura digital não está habilitada para esta empresa.');
        }
    }

    public function verificarAlertas(int $empresaId): void
    {
        $resumo = $this->obterResumoMensal($empresaId);
        $limite = $resumo['limite_mensal'];
        if ($limite === null || (int) $limite <= 0) {
            return;
        }

        $emails = $this->obterEmailsDestinatariosAlerta($empresaId);
        if (empty($emails)) {
            return;
        }

        $percentualAtual = (float) ($resumo['percentual_uso'] ?? 0);
        $competencia = (string) $resumo['competencia'];

        foreach ([80, 90, 100] as $percentual) {
            if ($percentualAtual < $percentual) {
                continue;
            }

            $envio = AssinaturaCotaAlertaEnvio::firstOrCreate(
                [
                    'empresa_id' => $empresaId,
                    'competencia' => $competencia,
                    'percentual' => $percentual,
                ],
                [
                    'usadas' => (int) $resumo['usadas'],
                    'limite' => (int) $limite,
                ]
            );

            if ($envio->wasRecentlyCreated) {
                JobEnviarAlertaCotaAssinatura::dispatch($empresaId, $percentual, $resumo, $emails);
            }
        }
    }

    public function obterEmailsDestinatariosAlerta(int $empresaId): array
    {
        $config = ClienteConfig::whereClienteId($empresaId)->first();
        if (!$config) {
            return [];
        }

        $userIds = collect($this->temColunaClienteConfig('assinatura_alerta_user_ids') ? ($config->assinatura_alerta_user_ids ?: []) : [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();
        $grupoIds = collect($this->temColunaClienteConfig('assinatura_alerta_grupo_ids') ? ($config->assinatura_alerta_grupo_ids ?: []) : [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $emailsUsuarios = collect();
        if ($userIds->isNotEmpty()) {
            $emailsUsuarios = User::where('empresa_id', $empresaId)
                ->whereIn('id', $userIds->all())
                ->where('ativo', true)
                ->get(['login'])
                ->map(function ($u) {
                    return $u->login;
                });
        }

        $emailsGrupos = collect();
        if ($grupoIds->isNotEmpty()) {
            $emailsGrupos = User::where('empresa_id', $empresaId)
                ->whereIn('grupo_id', $grupoIds->all())
                ->where('ativo', true)
                ->get(['login'])
                ->map(function ($u) {
                    return $u->login;
                });
        }

        return $emailsUsuarios
            ->merge($emailsGrupos)
            ->filter(fn ($email) => !empty($email))
            ->unique()
            ->values()
            ->all();
    }

    public function listarUsuariosEGrupos(int $empresaId): array
    {
        $usuarios = User::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get(['id', 'nome', 'login'])
            ->map(function ($u) {
                return [
                    'id' => (int) $u->id,
                    'nome' => $u->nome,
                    'email' => $u->login,
                ];
            })
            ->values()
            ->toArray();

        $grupos = Papel::where('empresa_id', $empresaId)
            ->orderBy('nome')
            ->get(['id', 'nome'])
            ->map(function ($g) {
                return [
                    'id' => (int) $g->id,
                    'nome' => $g->nome,
                ];
            })
            ->values()
            ->toArray();

        return [
            'usuarios' => $usuarios,
            'grupos' => $grupos,
        ];
    }

    public function salvarConfig(int $empresaId, array $dados): ClienteConfig
    {
        $config = ClienteConfig::firstOrNew(['cliente_id' => $empresaId]);
        if ($this->temColunaClienteConfig('limite_assinaturas_mensal')) {
            $limite = $dados['limite_assinaturas_mensal'] ?? null;
            $config->limite_assinaturas_mensal = ($limite === '' || $limite === null) ? null : max((int) $limite, 0);
        }
        if ($this->temColunaClienteConfig('assinatura_alerta_user_ids')) {
            $config->assinatura_alerta_user_ids = collect($dados['assinatura_alerta_user_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->values()->all();
        }
        if ($this->temColunaClienteConfig('assinatura_alerta_grupo_ids')) {
            $config->assinatura_alerta_grupo_ids = collect($dados['assinatura_alerta_grupo_ids'] ?? [])->map(fn ($id) => (int) $id)->filter()->values()->all();
        }
        $config->save();

        return $config->fresh();
    }

    private function temColunaClienteConfig(string $coluna): bool
    {
        return Schema::hasColumn('cliente_configs', $coluna);
    }
}
