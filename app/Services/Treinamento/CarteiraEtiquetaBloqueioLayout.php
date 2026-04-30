<?php

namespace App\Services\Treinamento;

/**
 * Medidas da etiqueta de bloqueio no PDF A4 com 2 pares (frente+verso) por folha.
 * Manter alinhado a {@see resources/views/pdf/treinamento/carteira/layout_carteira.blade.php}:
 * @page margin vertical, body.carteira--pdf .carteira-a4-wrap--bloqueio padding vertical.
 */
final class CarteiraEtiquetaBloqueioLayout
{
    /** Altura útil da folha A4 em mm (área de conteúdo, sem margens de página). */
    public const A4_ALTURA_MM = 297.0;

    /** Soma margem superior + inferior de @page (ex.: 0mm + 0mm). */
    public const MARGEM_PAGINA_VERTICAL_MM = 0.0;

    /** Soma padding vertical do wrap de bloqueio no PDF (ex.: 2mm + 2mm em body.carteira--pdf). */
    public const WRAP_BLOQUEIO_PADDING_VERTICAL_MM = 4.0;

    /** Espaço entre a 1ª e a 2ª linha de pares na mesma folha. */
    public const ENTRE_DOIS_PARES_MM = 3.0;

    public const LINHAS_POR_FOLHA = 2;

    /**
     * Altura máxima de cada linha (um par frente+costa lado a lado), em mm,
     * para caber exatamente {@see LINHAS_POR_FOLHA} linhas na área útil.
     */
    public static function alturaLinhaParMm(): float
    {
        $alturaUtil = self::A4_ALTURA_MM
            - self::MARGEM_PAGINA_VERTICAL_MM
            - self::WRAP_BLOQUEIO_PADDING_VERTICAL_MM;

        $gaps = (self::LINHAS_POR_FOLHA - 1) * self::ENTRE_DOIS_PARES_MM;

        return ($alturaUtil - $gaps) / self::LINHAS_POR_FOLHA;
    }
}
