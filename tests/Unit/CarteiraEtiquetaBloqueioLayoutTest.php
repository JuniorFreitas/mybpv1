<?php

namespace Tests\Unit;

use App\Services\Treinamento\CarteiraEtiquetaBloqueioLayout;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CarteiraEtiquetaBloqueioLayoutTest extends TestCase
{
    #[Test]
    public function altura_linha_par_mm_bate_com_formula_a4_duas_linhas(): void
    {
        $alturaUtil = CarteiraEtiquetaBloqueioLayout::A4_ALTURA_MM
            - CarteiraEtiquetaBloqueioLayout::MARGEM_PAGINA_VERTICAL_MM
            - CarteiraEtiquetaBloqueioLayout::WRAP_BLOQUEIO_PADDING_VERTICAL_MM;

        $esperado = ($alturaUtil - CarteiraEtiquetaBloqueioLayout::ENTRE_DOIS_PARES_MM)
            / CarteiraEtiquetaBloqueioLayout::LINHAS_POR_FOLHA;

        $this->assertEqualsWithDelta($esperado, CarteiraEtiquetaBloqueioLayout::alturaLinhaParMm(), 0.0001);
        $this->assertEqualsWithDelta(135.0, CarteiraEtiquetaBloqueioLayout::alturaLinhaParMm(), 0.0001);
    }
}
