<?php

namespace Tests\Unit\Services\Cih;

use App\Models\Cih;
use App\Services\Cih\CihExportFormatter;
use App\Services\Cih\CihLotacaoResolver;
use Tests\TestCase;

class CihExportFormatterLotacaoTest extends TestCase
{
    public function test_headers_com_filial_incluem_lotacao_apos_centro_de_custo(): void
    {
        $formatter = new CihExportFormatter(Cih::CONFIG_CENTRO_DE_CUSTO, true);

        $headers = $formatter->getHeaders();
        $idxCc = array_search('Centro de Custo', $headers, true);
        $idxLotacao = array_search('Lotação', $headers, true);

        $this->assertNotFalse($idxCc);
        $this->assertNotFalse($idxLotacao);
        $this->assertSame($idxCc + 1, $idxLotacao);
    }

    public function test_headers_sem_filial_nao_incluem_lotacao(): void
    {
        $formatter = new CihExportFormatter(Cih::CONFIG_CENTRO_DE_CUSTO, false);

        $this->assertNotContains('Lotação', $formatter->getHeaders());
    }

    public function test_headers_modelo_area_com_filial_incluem_lotacao(): void
    {
        $formatter = new CihExportFormatter('area', true);

        $headers = $formatter->getHeaders();
        $this->assertContains('Lotação', $headers);
        $this->assertContains('Área', $headers);
        $this->assertSame(
            array_search('Centro de Custo', $headers, true) + 1,
            array_search('Lotação', $headers, true)
        );
    }

    public function test_lotacao_resolver_format_retorna_vazio_sem_centro(): void
    {
        $resolver = new CihLotacaoResolver(1);

        $this->assertSame('', $resolver->format(null));
        $this->assertSame('', $resolver->format(0));
    }
}
