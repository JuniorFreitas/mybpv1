<?php

namespace App\Services\Admissao;

use Illuminate\Support\Facades\Cache;

/**
 * Gerencia a preferência de colunas visíveis na tabela/filtro de Admissão (por empresa + usuário).
 * Extraído de AdmissaoController::getColunasTabela/atualizarColunasTabela (TD-002 fatia 2).
 */
class AdmissaoTabelaColunasService
{
    private const CACHE_TAG = 'tabelas_filtro_processo';

    /** TTL 90 dias — preferência de UI; evita cache infinito (AGENTS.md §8). */
    private const CACHE_TTL_SECONDS = 60 * 60 * 24 * 90;

    /**
     * @return array<int, array{label: string, checked: bool, id: string}>
     */
    public function colunasPadrao(): array
    {
        return [
            ['label' => 'PCD', 'checked' => false, 'id' => 'pcd'],
            ['label' => 'CPF', 'checked' => false, 'id' => 'tbl_cpf'],
            ['label' => 'ENC. DOCUMENTO', 'checked' => true, 'id' => 'enc_documento'],
            ['label' => 'ENC. EXAME', 'checked' => true, 'id' => 'enc_exame'],
            ['label' => 'ENC. TREINAMENTO', 'checked' => true, 'id' => 'enc_treinamento'],
            ['label' => 'RESP. ENCAMINHAMENTO', 'checked' => true, 'id' => 'resp_encaminhamento'],
            ['label' => 'CRACHÁ', 'checked' => true, 'id' => 'cracha'],
            ['label' => 'FOTO 3x4', 'checked' => true, 'id' => 'foto_3x4'],
            ['label' => 'DATA ADMISSÃO', 'checked' => true, 'id' => 'tbl_dt_admissao'],
            ['label' => 'CONTATO', 'checked' => true, 'id' => 'tbl_contato'],
        ];
    }

    /**
     * Retorna as colunas salvas em cache para a empresa/usuário, ou as colunas padrão
     * (gravando-as em cache na primeira chamada).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getColunas(int $empresaId, int $usuarioId): array
    {
        $cacheKey = $this->cacheKey($empresaId, $usuarioId);

        $colunasTabela = Cache::tags([self::CACHE_TAG])->get($cacheKey);

        if (!$colunasTabela) {
            $colunasTabela = $this->colunasPadrao();
            Cache::tags([self::CACHE_TAG])->put($cacheKey, $colunasTabela, self::CACHE_TTL_SECONDS);
        }

        return $colunasTabela;
    }

    /**
     * Atualiza as colunas salvas em cache para a empresa/usuário.
     *
     * @param  array<int, array<string, mixed>>  $colunasTabela
     * @return array<int, array<string, mixed>>
     */
    public function atualizarColunas(int $empresaId, int $usuarioId, array $colunasTabela): array
    {
        $cacheKey = $this->cacheKey($empresaId, $usuarioId);

        Cache::tags([self::CACHE_TAG])->put($cacheKey, $colunasTabela, self::CACHE_TTL_SECONDS);

        return $colunasTabela;
    }

    private function cacheKey(int $empresaId, int $usuarioId): string
    {
        return "tabelas_filtro_processo_{$empresaId}_{$usuarioId}";
    }
}
