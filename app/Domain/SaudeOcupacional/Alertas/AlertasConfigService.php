<?php

namespace App\Domain\SaudeOcupacional\Alertas;

use App\Models\ClienteConfig;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Configuração dos alertas de exames periódicos por empresa (marcos em dias + resumo semanal).
 */
class AlertasConfigService
{
    public const JANELAS_PADRAO = [60, 30, 7];

    public const MAX_JANELAS = 4;

    /**
     * @return array{janelas: list<int>, resumo_semanal: bool}
     */
    public function obter(int $empresaId): array
    {
        if (!Schema::hasColumn('cliente_configs', 'saude_alerta_janelas')) {
            return ['janelas' => self::JANELAS_PADRAO, 'resumo_semanal' => true];
        }

        $config = ClienteConfig::withoutGlobalScopes()
            ->where('cliente_id', $empresaId)
            ->first(['saude_alerta_janelas', 'saude_resumo_semanal']);

        $janelas = $config?->saude_alerta_janelas;

        return [
            'janelas' => is_array($janelas) && $janelas !== [] ? array_map('intval', $janelas) : self::JANELAS_PADRAO,
            'resumo_semanal' => $config ? (bool) $config->saude_resumo_semanal : true,
        ];
    }

    /**
     * @param  mixed  $janelas
     * @return array{janelas: list<int>, resumo_semanal: bool}
     */
    public function salvar(int $empresaId, mixed $janelas, bool $resumoSemanal): array
    {
        $lista = collect(is_array($janelas) ? $janelas : [])
            ->map(fn ($d) => filter_var($d, FILTER_VALIDATE_INT))
            ->filter(fn ($d) => $d !== false && $d >= 1 && $d <= 180)
            ->unique()
            ->sortDesc()
            ->values()
            ->all();

        if ($lista === [] || count($lista) > self::MAX_JANELAS) {
            throw ValidationException::withMessages(['janelas' => 'Informe de 1 a ' . self::MAX_JANELAS . ' avisos, entre 1 e 180 dias antes do vencimento.']);
        }

        $config = ClienteConfig::withoutGlobalScopes()->where('cliente_id', $empresaId)->first();
        if (!$config) {
            throw ValidationException::withMessages(['janelas' => 'Configuração da empresa não encontrada.']);
        }

        $config->saude_alerta_janelas = $lista;
        $config->saude_resumo_semanal = $resumoSemanal;
        $config->save();

        return ['janelas' => $lista, 'resumo_semanal' => $resumoSemanal];
    }
}
