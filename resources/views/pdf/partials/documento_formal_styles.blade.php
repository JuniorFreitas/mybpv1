/*
 * Padrão visual formal (demissão / carta oferta).
 * IMPORTANTE (DomPDF): NÃO usar "* { margin:0 }" — isso anula @page margin.
 */
html, body, .folha, .folha * {
    font-family: DejaVu Sans, Arial, sans-serif !important;
}
@page {
    size: A4 portrait;
    margin: 5mm 10mm 16mm 10mm;
}
html, body {
    font-size: 11pt;
    color: #000;
    background: #fff;
}
.folha { width: 100%; }
.cabecalho {
    text-align: center;
    margin: 0 0 18pt;
    padding: 0 0 8pt;
    border-bottom: 1pt solid #000;
}
.cabecalho__logo { margin: 0 auto 4pt; }
.cabecalho__logo img {
    display: block;
    margin: 0 auto;
    max-height: 16mm;
    max-width: 48mm;
}
.cabecalho__logo--compact img { max-height: 12mm; }
.cabecalho__razao {
    font-size: 10.5pt;
    font-weight: bold;
    text-transform: uppercase;
    line-height: 1.35;
    margin: 0 0 2pt;
}
.cabecalho__meta {
    font-size: 8.5pt;
    line-height: 1.35;
    margin: 0;
}
.titulo {
    text-align: center;
    font-size: 12pt;
    font-weight: bold;
    text-transform: uppercase;
    line-height: 1.5;
    margin: 0 0 22pt;
}
.destinatario {
    font-size: 11pt;
    line-height: 1.6;
    margin: 0 0 18pt;
    text-align: left;
}
.paragrafo {
    font-size: 11pt;
    line-height: 1.7;
    text-align: justify;
    text-indent: 12.5mm;
    margin: 0 0 14pt;
    word-spacing: normal;
    letter-spacing: normal;
}
.paragrafo--sem-recuo {
    text-indent: 0;
}
.fechamento {
    margin-top: 36pt;
}
.local-data {
    font-size: 11pt;
    line-height: 1.5;
    text-align: left;
    margin: 0 0 36pt;
}
.assinatura {
    text-align: center;
    font-size: 11pt;
    line-height: 1.5;
    margin: 0 0 16pt;
}
.assinatura__linha {
    width: 9cm;
    margin: 0 auto 6pt;
    border: none;
    border-top: 1pt solid #000;
}
.conteudo-carta p {
    font-size: 11pt;
    line-height: 1.7;
    margin: 0 0 14pt;
}
.conteudo-carta hr {
    width: 9cm;
    margin: 22pt auto 6pt auto;
    border: none;
    border-top: 1pt solid #000;
}

/*
 * Fechamento de contrato (assinaturas + testemunhas).
 * DomPDF: table + page-break-inside:avoid — não usar float/inline-block.
 * --nova-pagina: abre página própria e distribui o bloco (igual Termo).
 */
.doc-secao-pagina {
    page-break-before: always;
    page-break-inside: avoid;
    break-inside: avoid;
}
.doc-secao-pagina--termo {
    padding-top: 24pt;
}
.doc-secao-pagina--termo .titulo {
    margin: 0 0 28pt;
}
.doc-secao-pagina--termo .doc-termo__texto {
    font-size: 9.5pt;
    line-height: 1.55;
    text-align: justify;
    text-indent: 0;
    margin: 0 0 36pt;
}
.doc-secao-pagina--termo .doc-fechamento {
    margin-top: 8pt;
}
.doc-secao-pagina--termo .doc-fechamento__local {
    margin: 0 0 40pt;
}
.doc-secao-pagina--termo .doc-fechamento__assinantes {
    margin: 0 0 52pt;
}
.doc-secao-pagina--termo .doc-fechamento__espaco {
    height: 44pt;
}
.doc-secao-pagina--termo .doc-fechamento__espaco--testemunha {
    height: 30pt;
}
.doc-secao-pagina--termo .doc-fechamento__testemunhas-titulo {
    margin: 8pt 0 12pt;
}
.doc-fechamento {
    margin-top: 18pt;
    page-break-inside: avoid;
    break-inside: avoid;
}
.doc-fechamento--nova-pagina {
    page-break-before: always;
    page-break-inside: avoid;
    break-inside: avoid;
    margin-top: 0;
    padding-top: 28pt;
}
.doc-fechamento--nova-pagina .doc-fechamento__local {
    margin: 0 0 36pt;
}
.doc-fechamento--nova-pagina .doc-fechamento__assinantes {
    margin: 0 0 48pt;
}
.doc-fechamento--nova-pagina .doc-fechamento__espaco {
    height: 42pt;
}
.doc-fechamento--nova-pagina .doc-fechamento__espaco--testemunha {
    height: 28pt;
}
.doc-fechamento--nova-pagina .doc-fechamento__testemunhas-titulo {
    margin: 24pt 0 10pt;
}
.doc-fechamento__local {
    font-size: 9.5pt;
    line-height: 1.35;
    text-align: left;
    margin: 0 0 14pt;
}
.doc-fechamento__assinantes,
.doc-fechamento__testemunhas {
    width: 100%;
    border-collapse: collapse;
    margin: 0 0 10pt;
    page-break-inside: avoid;
    break-inside: avoid;
}
.doc-fechamento__assinante,
.doc-fechamento__testemunha {
    text-align: center;
    font-size: 9pt;
    line-height: 1.3;
    padding: 0;
}
.doc-fechamento__espaco {
    height: 22pt;
}
.doc-fechamento__espaco--testemunha {
    height: 16pt;
}
.doc-fechamento__linha {
    width: 95%;
    margin: 0 auto 4pt;
    border: none;
    border-top: 1pt solid #000;
    height: 0;
}
.doc-fechamento__assinantes--unico {
    margin-top: 36pt;
}
.doc-fechamento__assinantes--unico .doc-fechamento__espaco {
    height: 48pt;
}
.doc-fechamento__assinantes--unico .doc-fechamento__linha {
    width: 9cm;
    margin: 0 auto 6pt;
}
.doc-fechamento__papel {
    font-size: 8pt;
    font-weight: bold;
    text-transform: uppercase;
    margin: 0 0 1pt;
}
.doc-fechamento__nome {
    font-size: 8.5pt;
    line-height: 1.25;
    word-wrap: break-word;
}
.doc-fechamento__subtitulo {
    font-size: 8pt;
    line-height: 1.25;
    margin-top: 1pt;
    color: #222;
}
.doc-fechamento__testemunhas-titulo {
    font-size: 9.5pt;
    font-weight: bold;
    text-align: left;
    margin: 12pt 0 6pt;
    page-break-after: avoid;
}
.doc-fechamento__testemunha-meta {
    font-size: 8pt;
    line-height: 1.35;
    text-align: left;
    padding: 0 4pt;
    color: #222;
}
