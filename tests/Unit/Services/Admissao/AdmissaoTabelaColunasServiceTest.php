<?php

namespace Tests\Unit\Services\Admissao;

use App\Services\Admissao\AdmissaoTabelaColunasService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AdmissaoTabelaColunasServiceTest extends TestCase
{
    private AdmissaoTabelaColunasService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Cache::flush();
        $this->service = new AdmissaoTabelaColunasService();
    }

    public function test_get_colunas_retorna_padrao_quando_nao_ha_cache(): void
    {
        $colunas = $this->service->getColunas(1, 10);

        $this->assertSame($this->service->colunasPadrao(), $colunas);
        $this->assertContains(['label' => 'CPF', 'checked' => false, 'id' => 'tbl_cpf'], $colunas);
    }

    public function test_get_colunas_grava_padrao_em_cache_na_primeira_chamada(): void
    {
        $this->service->getColunas(1, 10);

        $this->assertSame(
            $this->service->colunasPadrao(),
            Cache::tags(['tabelas_filtro_processo'])->get('tabelas_filtro_processo_1_10')
        );
    }

    public function test_atualizar_colunas_sobrescreve_e_isola_por_empresa_usuario(): void
    {
        $novasColunas = [
            ['label' => 'CPF', 'checked' => true, 'id' => 'tbl_cpf'],
        ];

        $retorno = $this->service->atualizarColunas(1, 10, $novasColunas);

        $this->assertSame($novasColunas, $retorno);
        $this->assertSame($novasColunas, $this->service->getColunas(1, 10));

        // Outra empresa/usuário não deve ser afetada, e deve receber o padrão.
        $this->assertSame($this->service->colunasPadrao(), $this->service->getColunas(2, 10));
    }
}
