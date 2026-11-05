<?php

namespace App\Domain\SaudeOcupacional\Alertas;

use App\Domain\SaudeOcupacional\Conformidade\ConformidadeService;
use App\Domain\SaudeOcupacional\ModuloSaudeOcupacional;
use App\Jobs\SaudeOcupacional\JobMailSaudeOcupacional;
use App\Models\TipoRecebeEmail;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Alertas de exames periódicos por e-mail ao RH (quem marcou "Vencimento ASO"):
 * - marcos: ASO que chega a N dias do vencimento (config) e o que venceu ontem — cada marco é avisado uma vez;
 * - resumo semanal (segunda-feira): números do Painel e link.
 * Conteúdo: nome, cargo e datas. Nada de dado de saúde.
 */
class AlertasPeriodicosService
{
    public const TTL_MARCO_DIAS = 400;

    public const MARCO_VENCEU = -1;

    public function __construct(
        private readonly ConformidadeService $conformidade,
        private readonly AlertasConfigService $config
    ) {
    }

    /**
     * @return array{marcos: int, destinatarios: int, resumo: bool}
     */
    public function processar(int $empresaId, ?CarbonImmutable $hoje = null): array
    {
        $hoje ??= CarbonImmutable::today();
        $resultado = ['marcos' => 0, 'destinatarios' => 0, 'resumo' => false];

        if (!ModuloSaudeOcupacional::habilitado($empresaId)) {
            return $resultado;
        }

        $destinatarios = $this->destinatarios($empresaId);
        if ($destinatarios === []) {
            return $resultado;
        }
        $resultado['destinatarios'] = count($destinatarios);

        $config = $this->config->obter($empresaId);
        $itens = $this->marcosDoDia($empresaId, $hoje, $config['janelas']);
        $resultado['marcos'] = count($itens);

        if ($itens !== []) {
            foreach ($destinatarios as $usuario) {
                JobMailSaudeOcupacional::dispatch(JobMailSaudeOcupacional::TIPO_MARCOS, (int) $usuario->id, [
                    'itens' => $itens,
                    'data_br' => $hoje->format('d/m/Y'),
                    'link' => route('g.saude_ocupacional.conformidade.index'),
                ]);
            }
        }

        if ($config['resumo_semanal'] && $hoje->isMonday()) {
            $totais = $this->conformidade->totais($empresaId, $hoje);
            foreach ($destinatarios as $usuario) {
                JobMailSaudeOcupacional::dispatch(JobMailSaudeOcupacional::TIPO_RESUMO, (int) $usuario->id, [
                    'totais' => $totais,
                    'janela' => $this->conformidade->janela($empresaId)['dias'],
                    'data_br' => $hoje->format('d/m/Y'),
                    'link' => route('g.saude_ocupacional.painel'),
                ]);
            }
            $resultado['resumo'] = true;
        }

        return $resultado;
    }

    /**
     * ASOs que hoje estão exatamente a N dias (marcos) ou venceram ontem, ainda não avisados.
     *
     * @param  list<int>  $janelas
     * @return list<array{colaborador: string, cargo: string|null, vencimento_br: string|null, dias: int, marco: string}>
     */
    public function marcosDoDia(int $empresaId, CarbonImmutable $hoje, array $janelas): array
    {
        $marcos = array_merge($janelas, [self::MARCO_VENCEU]);
        $itens = [];

        foreach ([ConformidadeService::STATUS_VENCENDO, ConformidadeService::STATUS_VENCIDO] as $status) {
            $this->conformidade->percorrer($empresaId, ['status' => $status], $hoje, function (array $lote) use (&$itens, $marcos, $empresaId) {
                foreach ($lote as $l) {
                    if ($l['dias'] === null || !in_array($l['dias'], $marcos, true)) {
                        continue;
                    }

                    // Cada marco de cada vencimento é avisado uma vez (novo ASO = novo vencimento = novos avisos).
                    $chave = sprintf('saude_ocupacional:alerta:%d:%d:%s:%d', $empresaId, $l['feedback_id'], $l['vencimento_br'], $l['dias']);
                    if (!Cache::add($chave, 1, now()->addDays(self::TTL_MARCO_DIAS))) {
                        continue;
                    }

                    $itens[] = [
                        'colaborador' => $l['colaborador'],
                        'cargo' => $l['cargo'],
                        'vencimento_br' => $l['vencimento_br'],
                        'dias' => $l['dias'],
                        'marco' => $l['dias'] === self::MARCO_VENCEU ? 'Venceu ontem' : 'Vence em ' . $l['dias'] . ' dias',
                    ];
                }
            });
        }

        usort($itens, fn ($a, $b) => $a['dias'] <=> $b['dias']);

        return $itens;
    }

    /**
     * @return list<User>
     */
    private function destinatarios(int $empresaId): array
    {
        try {
            return User::withoutGlobalScopes()
                ->paraNotificacaoEmail(TipoRecebeEmail::VENCIMENTO_ASO, $empresaId)
                ->select(['users.id', 'users.nome', 'users.login', 'users.empresa_id'])
                ->get()
                ->all();
        } catch (\Throwable $e) {
            Log::warning('Alertas Saúde Ocupacional: falha ao listar destinatários', ['empresa_id' => $empresaId, 'erro' => $e->getMessage()]);

            return [];
        }
    }
}
